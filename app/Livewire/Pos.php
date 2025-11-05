<?php

namespace App\Livewire;

use Filament\Forms;
use App\Models\Product;
use App\Models\Setting;
use Livewire\Component;
use App\Models\Category;
use Filament\Forms\Form;
use App\Models\Transaction;
use Livewire\WithPagination;
use App\Models\PaymentMethod;
use App\Models\TransactionItem;
use App\Helpers\TransactionHelper;
use App\Services\DirectPrintService;
use Filament\Notifications\Notification;

class Pos extends Component
{
    use WithPagination;

    public int | string $perPage = 10;
    public $categories;
    public $selectedCategory;
    public $search = '';
    public $print_via_bluetooth = false;
    public $barcode = '';
    public $name = 'Umum';
    public $payment_method_id;
    public $payment_methods;
    public $order_items = [];
    public $total_price = 0;
    public $cash_received = '';
    public $change = 0;
    public $showConfirmationModal = false;
    public $showCheckoutModal = false;
    public $orderToPrint = null;
    public $is_cash = true;
    public $selected_payment_method = null;

    protected $listeners = [
        'scanResult' => 'handleScanResult',
    ];

    public function mount()
    {
        $settings = Setting::first();
        $this->print_via_bluetooth = $settings->print_via_bluetooth ?? $this->print_via_bluetooth = false;

        // Mengambil data kategori dan menambahkan data 'Semua' sebagai pilihan pertama
        $this->categories = collect([['id' => null, 'name' => 'Semua']])->merge(Category::all());

        // Jika session 'orderItems' ada, maka ambil data nya dan simpan ke dalam property $order_items
        // Session 'orderItems' digunakan untuk menyimpan data order sementara sebelum di checkout
        if (session()->has('orderItems')) {
            $this->order_items = session('orderItems');
            $this->calculateTotal();
        }

        $this->payment_methods = PaymentMethod::all();
    }

    public function render()
    {
        return view('livewire.pos', [
            'products' => Product::where('stock', '>', 0)->where('is_active', 1)
                ->when($this->selectedCategory !== null, function ($query) {
                    return $query->where('category_id', $this->selectedCategory);
                })
                ->where(function ($query) {
                    return $query->where('name', 'LIKE', '%' . $this->search . '%')
                        ->orWhere('sku', 'LIKE', '%' . $this->search . '%');
                })
                ->paginate($this->perPage)
        ]);
    }


    public function updatedPaymentMethodId($value)
    {
        if ($value) {
            $paymentMethod = PaymentMethod::find($value);
            $this->selected_payment_method = $paymentMethod;
            $this->is_cash = $paymentMethod->is_cash ?? false;
            
            if (!$this->is_cash) {
                $this->cash_received = $this->total_price;
                $this->change = 0;
            } else {
                $this->calculateChange();
            }
        }
    }

    public function updatedCashReceived($value)
    {
        if ($this->is_cash) {
            // Remove thousand separator dots before calculation
            $this->cash_received = $value;
            $this->calculateChange();
        }
    }

    public function calculateChange()
    {
        // Remove thousand separator dots and convert to number
        $cleanValue = str_replace('.', '', $this->cash_received);
        $cashReceived = floatval($cleanValue);
        $totalPrice = floatval($this->total_price);
        
        if ($cashReceived >= $totalPrice) {
            $this->change = $cashReceived - $totalPrice;
        } else {
            $this->change = 0;
        }
    }

    // Helper method to get numeric value from formatted input
    public function getCashReceivedNumeric()
    {
        return floatval(str_replace('.', '', $this->cash_received));
    }

    public function updatedBarcode($barcode)
    {
        $product = Product::where('barcode', $barcode)
            ->where('is_active', true)->first();

        if ($product) {
            $this->addToOrder($product->id);
        } else {
            Notification::make()
                ->title('Product not found ' . $barcode)
                ->danger()
                ->send();
        }

        // Reset barcode
        $this->barcode = '';
    }

    public function handleScanResult($decodedText)
    {
        $product = Product::where('barcode', $decodedText)
            ->where('is_active', true)->first();

        if ($product) {
            $this->addToOrder($product->id);
        } else {
            Notification::make()
                ->title('Product not found ' . $decodedText)
                ->danger()
                ->send();
        }

        // Reset barcode
        $this->barcode = '';
    }

    public function setCategory($categoryId = null)
    {
        $this->selectedCategory = $categoryId;
    }

    public function addToOrder($productId)
    {
        $product = Product::find($productId);

        if ($product) {
            // Cari apakah item sudah ada di dalam order
            $existingItemKey = array_search($productId, array_column($this->order_items, 'product_id'));

            // Jika item sudah ada, tambahkan 1 quantity
            if ($existingItemKey !== false) {
                if ($this->order_items[$existingItemKey]['quantity'] >= $product->stock) {
                    Notification::make()
                        ->title('Stok barang tidak mencukupi')
                        ->danger()
                        ->send();
                    return;
                } else {
                    $this->order_items[$existingItemKey]['quantity']++;
                }
            }
            // Jika item belum ada, tambahkan item baru ke dalam order
            else {
                $this->order_items[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'cost_price' => $product->cost_price,
                    'total_profit' => $product->price - $product->cost_price,
                    'image_url' => $product->image,
                    'quantity' => 1,
                ];
            }

            // Simpan perubahan order ke session
            session()->put('orderItems', $this->order_items);
            
            // Recalculate total
            $this->calculateTotal();
            
            // Recalculate change if cash payment
            if ($this->is_cash && !empty($this->cash_received)) {
                $this->calculateChange();
            }
        }
    }

    public function loadOrderItems($orderItems)
    {
        $this->order_items = $orderItems;
        session()->put('orderItems', $orderItems);
    }

    public function increaseQuantity($product_id)
    {
        $product = Product::find($product_id);

        if (!$product) {
            Notification::make()
                ->title('Produk tidak ditemukan')
                ->danger()
                ->send();
            return;
        }

        // Loop setiap item yang ada di cart
        foreach ($this->order_items as $key => $item) {
            // Jika item yang sedang di-loop sama dengan item yang ingin di tambah
            if ($item['product_id'] == $product_id) {
                // Jika quantity item ditambah 1 masih kurang dari atau sama dengan stok produk maka tambah 1 quantity
                if ($item['quantity'] + 1 <= $product->stock) {
                    $this->order_items[$key]['quantity']++;
                }
                // Jika quantity item yang ingin di tambah lebih besar dari stok produk maka tampilkan notifikasi
                else {
                    Notification::make()
                        ->title('Stok barang tidak mencukupi')
                        ->danger()
                        ->send();
                }
                // Berhenti loop karena item yang ingin di tambah sudah di temukan
                break;
            }
        }

        session()->put('orderItems', $this->order_items);
        
        // Recalculate total and change
        $this->calculateTotal();
        if ($this->is_cash && !empty($this->cash_received)) {
            $this->calculateChange();
        }
    }

    public function decreaseQuantity($product_id)
    {
        // Loop setiap item yang ada di cart
        foreach ($this->order_items as $key => $item) {
            // Jika item yang sedang di-loop sama dengan item yang ingin di kurangi
            if ($item['product_id'] == $product_id) {
                // Jika quantity item lebih dari 1 maka kurangi 1 quantity
                if ($this->order_items[$key]['quantity'] > 1) {
                    $this->order_items[$key]['quantity']--;
                }
                // Jika quantity item 1 maka hapus item dari cart
                else {
                    unset($this->order_items[$key]);
                    $this->order_items = array_values($this->order_items);
                }
                break;
            }
        }
        
        // Simpan perubahan cart ke session
        session()->put('orderItems', $this->order_items);
        
        // Recalculate total and change
        $this->calculateTotal();
        if ($this->is_cash && !empty($this->cash_received)) {
            $this->calculateChange();
        }
    }

    public function calculateTotal()
    {
        // Inisialisasi total harga
        $total = 0;

        // Loop setiap item yang ada di cart
        foreach ($this->order_items as $item) {
            // Tambahkan harga setiap item ke total
            $total += $item['quantity'] * $item['price'];
        }

        // Simpan total harga di property $total_price
        $this->total_price = $total;

        // Return total harga
        return $total;
    }

    public function resetOrder()
    {
        // Hapus semua session terkait
        session()->forget(['orderItems', 'name', 'payment_method_id']);

        // Reset variabel Livewire
        $this->order_items = [];
        $this->payment_method_id = null;
        $this->total_price = 0;
        $this->cash_received = '';
        $this->change = 0;
        $this->is_cash = true;
        $this->selected_payment_method = null;
    }

    public function formatNumber($value)
    {
        return number_format($value, 0, ',', '.');
    }

    public function checkout()
    {
        // Convert formatted cash_received to numeric for validation
        $cashReceivedNumeric = $this->getCashReceivedNumeric();
        
        // Custom validation messages
        $messages = [
            'payment_method_id.required' => 'Metode pembayaran harus dipilih',
            'cash_received.required' => 'Nominal bayar harus diisi',
            'cash_received_numeric.min' => 'Nominal bayar kurang dari total belanja'
        ];
        
        // Base validation
        $this->validate([
            'name' => 'string|max:255',
            'payment_method_id' => 'required'
        ], $messages);
        
        // Additional validation for cash payment
        if ($this->is_cash) {
            if (empty($this->cash_received)) {
                $this->addError('cash_received', 'Nominal bayar harus diisi');
                return;
            }
            
            if ($cashReceivedNumeric < $this->total_price) {
                $this->addError('cash_received', 'Nominal bayar kurang dari total belanja');
                return;
            }
        }

        $payment_method_id_temp = $this->payment_method_id;
        $cash_received_value = $this->is_cash ? $cashReceivedNumeric : $this->total_price;

        if (session('orderItems') === null || count(session('orderItems')) == 0) {
            Notification::make()
                ->title('Keranjang kosong')
                ->danger()
                ->send();
            
            $this->showCheckoutModal = false;
        } else {
            $order = Transaction::create([
                'payment_method_id' => $payment_method_id_temp,
                'transaction_number' => TransactionHelper::generateUniqueTrxId(),
                'name' => $this->name,
                'total' => $this->total_price,
                'cash_received' => $cash_received_value,
                'change' => $this->change,
            ]);
            
            foreach ($this->order_items as $item) {
                TransactionItem::create([
                    'transaction_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'cost_price' => $item['cost_price'],
                    'total_profit' => $item['total_profit'] * $item['quantity'],
                ]);
            }
            
            // Simpan ID order untuk cetak
            $this->orderToPrint = $order->id;

            // Tampilkan modal konfirmasi
            $this->showConfirmationModal = true;
            $this->showCheckoutModal = false;

            Notification::make()
                ->title('Order berhasil disimpan')
                ->success()
                ->send();

            $this->resetOrder();
        }
    }

    public function printLocalKabel()
    {
        $directPrint = app(DirectPrintService::class);

        $directPrint->print($this->orderToPrint);

        $this->showConfirmationModal = false;
        $this->orderToPrint = null;
    }

    public function printBluetooth()
    {
        $order = Transaction::with(['paymentMethod', 'transactionItems.product'])->findOrFail($this->orderToPrint);
        $items = $order->transactionItems;

        $this->dispatch(
            'doPrintReceipt',
            store: Setting::first(),
            order: $order,
            items: $items,
            date: $order->created_at->format('d-m-Y H:i:s')
        );

        $this->showConfirmationModal = false;
        $this->orderToPrint = null;
    }
}