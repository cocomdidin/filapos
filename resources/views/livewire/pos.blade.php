<div class="min-h-screen p-2 lg:p-6" style="font-family: 'Poppins'">
    <div class="max-w-[1600px] mx-auto">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Main Products Section -->
            <div class="xl:col-span-2">
                <!-- Header Section -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-6">
                    <!-- Search Controls -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="relative">
                            <svg class="absolute left-4 top-9 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <input wire:model.live.debounce.300ms='search' type="text"
                                placeholder="Cari nama atau SKU produk..."
                                class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 dark:bg-gray-700 dark:border-gray-600 text-gray-900 dark:text-white focus:border-green-500 focus:ring-2 focus:ring-green-200 dark:focus:ring-green-800 transition-all duration-200">
                        </div>

                        <div class="flex gap-3">
                            <div class="relative flex-1">
                                <svg class="absolute left-4 top-9 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                                    </path>
                                </svg>
                                <input wire:model.live='barcode' type="text" placeholder="Scan barcode..." autofocus
                                    id="barcode"
                                    class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 dark:bg-gray-700 dark:border-gray-600 text-gray-900 dark:text-white focus:border-green-500 focus:ring-2 focus:ring-green-200 dark:focus:ring-green-800 transition-all duration-200">
                            </div>
                            <x-filament::button x-data="" x-on:click="$dispatch('toggle-scanner')"
                                class="px-4 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                                <img src="{{ asset('images/qrcode-scan.svg') }}" class="w-6 h-6" />
                            </x-filament::button>
                        </div>
                    </div>

                    {{-- MODAL SCAN CAMERA --}}
                    <livewire:scanner-modal-component>
                </div>

                <!-- Categories Section -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-6">
                    <div class="overflow-x-auto">
                        <div class="flex gap-3 pb-2 whitespace-nowrap">
                            @foreach ($categories as $item)
                                <button wire:click="setCategory({{ $item['id'] ?? null }})"
                                    class="category-btn px-6 py-3 rounded-xl font-medium transition-all duration-300 transform hover:scale-105
                                    {{ $selectedCategory === $item['id']
                                        ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg'
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-white' }}">
                                    {{ $item['name'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-4">
                        @foreach ($products as $item)
                            <div wire:click="addToOrder({{ $item->id }})"
                                class="group bg-white dark:bg-gray-700 rounded-xl shadow-md hover:shadow-2xl transform hover:scale-105 transition-all duration-300 cursor-pointer overflow-hidden">
                                <div class="relative">
                                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}"
                                        class="w-full h-32 object-cover">
                                </div>
                                <div class="p-4">
                                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white line-clamp-2 mb-2">
                                        {{ $item->name }}</h3>
                                        <div class="flex items-center justify-between w-full">
                                            <p class="text-lg font-bold text-green-600 dark:text-green-400">
                                                Rp {{ number_format($item->price, 0, ',', '.') }}
                                            </p>
                                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                                ({{ $item->stock }})
                                            </p>
                                        </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <x-filament::pagination :paginator="$products" :page-options="[5, 10, 20, 50, 100]"
                            current-page-option-property="perPage" />
                    </div>
                </div>
            </div>

            <!-- Cart Section -->
            <div class="xl:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 sticky top-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Keranjang</h2>
                        <button wire:click="resetOrder"
                            class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                            Reset
                        </button>
                    </div>

                    @if (count($order_items) === 0)
                        <div class="flex flex-col items-center justify-center py-12">
                            <img src="{{ asset('images/cart-empty.png') }}" alt="Empty Cart"
                                class="w-24 h-24 mb-4 opacity-50">
                            <p class="text-gray-500 dark:text-gray-400 font-medium">Keranjang Kosong</p>
                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Pilih produk untuk memulai</p>
                        </div>
                    @else
                        <div
                            class="{{ count($order_items) >= 4 ? 'max-h-[400px] overflow-y-auto pr-2' : '' }} space-y-3">
                            @foreach ($order_items as $item)
                                <div
                                    class="bg-gray-50 dark:bg-gray-700 rounded-xl p-3 transition-all duration-200 hover:shadow-md">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ asset('storage/' . $item['image_url']) }}"
                                            alt="{{ $item['name'] }}"
                                            class="w-16 h-16 object-cover rounded-lg shadow-sm">
                                        <div class="flex-1 min-w-0">
                                            <h4
                                                class="text-sm font-semibold text-gray-800 dark:text-white line-clamp-1">
                                                {{ $item['name'] }}
                                            </h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Rp {{ number_format($item['price'], 0, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between mt-3">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            Subtotal: Rp
                                            {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                        </span>
                                        <div class="flex items-center gap-2">
                                            <button wire:click="decreaseQuantity({{ $item['product_id'] }})"
                                                class="w-8 h-8 rounded-lg bg-gray-200 dark:bg-gray-600 hover:bg-white dark:hover:bg-gray-500 text-gray-700 dark:text-white font-medium transition-colors duration-200">
                                                -
                                            </button>
                                            <span class="w-10 text-center font-semibold text-gray-800 dark:text-white">
                                                {{ $item['quantity'] }}
                                            </span>
                                            <button wire:click="increaseQuantity({{ $item['product_id'] }})"
                                                class="w-8 h-8 rounded-lg bg-green-500 hover:bg-green-600 text-white font-medium transition-colors duration-200">
                                                +
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Total Section -->
                        <div class="mt-6 pt-6 border-t-2 border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-lg font-medium text-gray-600 dark:text-gray-400">Total</span>
                                <span class="text-2xl font-bold text-gray-800 dark:text-white">
                                    Rp {{ number_format($this->calculateTotal(), 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endif

                    <!-- Checkout Button -->
                    <button type="button" wire:click="$set('showCheckoutModal', true)"
                        class="w-full mt-4 py-4 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 {{ count($order_items) === 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ count($order_items) === 0 ? 'disabled' : '' }}>
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            Checkout
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Checkout Modal -->
    @if ($showCheckoutModal)
        <div wire:ignore.self x-data="{
            init() {
                @this.calculateTotal();
                if (@this.payment_method_id) {
                    @this.updatedPaymentMethodId(@this.payment_method_id);
                }
            }
        }"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center z-50 p-4">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl transform scale-95 animate-modal-appear">
                <div class="p-6">
                    <form wire:submit="checkout">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="col-span-1">
                                <!-- Total Belanja Display -->
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-4 mb-6 text-white">
                                    <div class="text-sm opacity-90">Total Belanja</div>
                                    <div class="text-3xl font-bold">
                                        Rp {{ number_format($total_price, 0, ',', '.') }}
                                    </div>
                                </div>

                                <!-- Customer Name -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">
                                        Nama Customer
                                    </label>
                                    <input type="text" wire:model="name"
                                        class="w-full px-4 py-3 rounded-xl border border-white dark:border-gray-600 bg-gray-300 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Masukkan nama customer">
                                </div>

                                <!-- Payment Method -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">
                                        Metode Pembayaran
                                    </label>
                                    <select wire:model.live="payment_method_id"
                                        class="w-full px-4 py-3 rounded-xl border border-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        required>
                                        <option value="">Pilih metode pembayaran</option>
                                        @foreach ($payment_methods as $method)
                                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('payment_method_id')
                                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-span-1">
                                <!-- Change Display -->
                                <div class="mb-6">
                                    <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl p-4 mb-6 text-white">
                                        <div class="text-sm opacity-90">Kembalian</div>
                                        <div class="text-3xl font-bold transition-all duration-300">
                                            Rp {{ number_format($change, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Cash Received (Only for Cash Payment) -->
                                @if ($is_cash)
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">
                                            Nominal Bayar
                                        </label>
                                        <div class="relative">
                                            <span
                                                class="absolute left-4 top-9 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 font-medium">
                                                Rp
                                            </span>
                                            <input type="text" wire:model.live="cash_received"
                                                x-data="{
                                                    // Fungsi untuk mengubah format angka menjadi format mata uang
                                                    formatCurrency(value) {
                                                        // Menggunakan regex untuk menggantikan setiap 3 digit dengan titik
                                                        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                                                    }
                                                }"
                                                x-on:input="
                                        let value = $event.target.value.replace(/\./g, '');
                                        if (!isNaN(value)) {
                                            $event.target.value = formatCurrency(value);
                                        }
                                    "
                                                class="w-full pl-12 pr-4 py-3 rounded-xl border border-white dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 text-lg font-medium"
                                                placeholder="0" required autocomplete="off">
                                            @error('cash_received')
                                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Quick Cash Buttons -->
                                        <div class="grid grid-cols-3 gap-2 mt-3">
                                            @php
                                                $quickAmounts = [50000, 100000, 150000, 200000, 500000, 1000000];
                                            @endphp
                                            @foreach ($quickAmounts as $amount)
                                                <button type="button"
                                                    wire:click="$set('cash_received', '{{ number_format($amount, 0, '', '.') }}')"
                                                    class="py-2 px-3 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm font-medium text-gray-700 dark:text-white transition-colors duration-200">
                                                    {{ number_format($amount, 0, ',', '.') }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <!-- Non-Cash Payment Info -->
                                    <div class="mb-6">
                                        <div class=" rounded-xl p-4">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                    </path>
                                                </svg>
                                                <span class="text-sm text-amber-700 dark:text-amber-500">
                                                    Pembayaran non-tunai akan diproses sesuai nominal total belanja
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3">
                            <button type="button" wire:click="$set('showCheckoutModal', false)"
                                class="flex-1 py-3 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white font-medium rounded-xl transition-all duration-200 transform hover:scale-[0.98]">
                                Batal
                            </button>
                            <button type="submit" @if ($is_cash && ($change < 0 || empty($cash_received))) disabled @endif
                                class="flex-1 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                                <span>Bayar</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    <!-- Payment Confirmation Modal -->
    @if ($showConfirmationModal)
        <div wire:ignore.self
            class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center z-50 p-4">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md transform animate-modal-appear">
                <div class="p-6">
                    <!-- Success Icon Animation -->
                    <div class="mx-auto w-20 h-20 mb-4">
                        <svg class="w-full h-full text-green-500 animate-success-check" fill="none"
                            viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"
                                class="animate-circle-draw" />
                            <path d="M7 12l3 3 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="animate-check-draw" />
                        </svg>
                    </div>

                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-2 text-center">
                        Pembayaran Berhasil!
                    </h3>

                    <p class="text-gray-600 dark:text-white text-center mb-6">
                        Transaksi telah berhasil diproses
                    </p>

                    <!-- Transaction Summary -->
                    <div class="bg-gray-50 dark:bg-gray-600 rounded-xl p-4 mb-6">
                        <div class="space-y-2 text-sm">
                            @if ($orderToPrint)
                                @php
                                    $order = \App\Models\Transaction::find($orderToPrint);
                                @endphp
                                @if ($order)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">No. Transaksi</span>
                                        <span
                                            class="font-medium text-gray-800 dark:text-white">{{ $order->transaction_number }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Total Bayar</span>
                                        <span class="font-medium text-gray-800 dark:text-white">Rp
                                            {{ number_format($order->cash_received, 0, ',', '.') }}</span>
                                    </div>
                                    @if ($order->change > 0)
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Kembalian</span>
                                            <span class="font-medium text-green-600">Rp
                                                {{ number_format($order->change, 0, ',', '.') }}</span>
                                        </div>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Print Options -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-white mb-2">
                            Cetak Struk?
                        </h4>

                        <div class="grid grid-cols-2 gap-3">
                            @if (!$print_via_bluetooth)
                                <button wire:click="printLocalKabel"
                                    class="flex items-center justify-center space-x-2 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-medium rounded-xl transition-all duration-200 transform hover:scale-[0.98]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                        </path>
                                    </svg>
                                    <span>Cetak Struk</span>
                                </button>
                            @else
                                <button wire:click="printBluetooth"
                                    class="flex items-center justify-center space-x-2 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-medium rounded-xl transition-all duration-200 transform hover:scale-[0.98]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0">
                                        </path>
                                    </svg>
                                    <span>Cetak Struk</span>
                                </button>
                            @endif

                            <button wire:click="$set('showConfirmationModal', false)"
                                class="flex items-center justify-center space-x-2 py-3 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white font-medium rounded-xl transition-all duration-200 transform hover:scale-[0.98]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span>Lewati</span>
                            </button>
                        </div>
                    </div>

                    <!-- Auto-close countdown -->
                    <div class="mt-4 text-center text-xs text-gray-500 dark:text-gray-400" x-data="paymentSuccessTimer()">
                        Modal akan tertutup dalam <span x-text="seconds"></span> detik
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Animation Styles -->
        <style>
            @keyframes circle-draw {
                to {
                    stroke-dashoffset: 0;
                }
            }

            @keyframes check-draw {
                to {
                    stroke-dashoffset: 0;
                }
            }

            .animate-circle-draw {
                stroke-dasharray: 62.83;
                stroke-dashoffset: 62.83;
                animation: circle-draw 0.6s ease-out forwards;
            }

            .animate-check-draw {
                stroke-dasharray: 24;
                stroke-dashoffset: 24;
                animation: check-draw 0.3s ease-out 0.6s forwards;
            }

            .animate-success-check {
                animation: scale-up 0.3s ease-out 0.9s forwards;
                transform: scale(0);
            }

            @keyframes scale-up {
                to {
                    transform: scale(1);
                }
            }
        </style>
    @endif
</div>
