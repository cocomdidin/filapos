<?php

namespace App\Imports;


use App\Models\Category;
use App\Models\Product;
use EightyNine\ExcelImport\Exceptions\ImportStoppedException;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class ProductImport implements ToCollection, WithHeadingRow
{
    private \Illuminate\Database\Eloquent\Collection $existingCategories;

    public function __construct()
    {
        // Load existing categories to minimize database queries
        $this->existingCategories = Category::all();
    }

    private function firstOrCreateCategory(string $categoryName): int
    {
        $category = $this->existingCategories->firstWhere('name', $categoryName);

        if (!$category) {
            $category = Category::create(['name' => $categoryName]);
            $this->existingCategories->push($category);
        }

        return $category->id;
    }

    public function collection(Collection $collection)
    {
        // Validation data
        $validated = $this->validate($collection);

        // Process data
        foreach ($validated as $data) {
            Product::create([
                'category_id' => $this->firstOrCreateCategory($data['kategori']),
                'sku' => Arr::get($data, 'sku'),
                'name' => $data['nama_produk'],
                'cost_price' => $data['harga_modal'],
                'price' => $data['harga_jual'],
                'stock' => Arr::get($data, 'stok', 0),
                'barcode' => Arr::get($data, 'barcode'),
                'description' => Arr::get($data, 'deskripsi'),
            ]);
        }
        
        // Processing complete
        Notification::make()
            ->title('Impor produk selesai.')
            ->body(count($validated) . ' produk berhasil diimpor.')
            ->success()
            ->send();
    }

    private function validate(Collection $collection): array
    {
        // Validate row count
        if ($collection->count() > 1000) {
            throw new ImportStoppedException('Jumlah baris melebihi batas maksimum 1000. Silakan kurangi data dan coba lagi.', 'error');
        }

        // Validate required headers
        $requiredHeaders = ['kategori', 'nama_produk', 'harga_modal', 'harga_jual'];
        $headers = $collection->first()->keys()->toArray();
        foreach ($requiredHeaders as $header) {
            if (!in_array($header, $headers)) {
                throw new ImportStoppedException("Header wajib '$header' tidak ditemukan dalam file impor.", 'error');
            }
        }

        // return validated data only for new products
        $existingProducts = Product::query()
            ->whereIn('name', $collection->pluck('nama_produk')->toArray())
            ->pluck('name')
            ->toArray();

        return $collection->reject(function ($item) use ($existingProducts) {
            return in_array($item['nama_produk'], $existingProducts);
        })->toArray();
    }
}
