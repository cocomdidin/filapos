<?php

namespace App\Imports;


use App\Models\Category;
use App\Models\Product;
use EightyNine\ExcelImport\EnhancedDefaultImport;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;


class ProductImportBAK extends EnhancedDefaultImport
{
    private \Illuminate\Database\Eloquent\Collection $existingCategories;

    public function __construct()
    {
        parent::__construct(Product::class);

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

    protected function beforeCollection(Collection $collection): void
    {
        // Validate required headers
        $requiredHeaders = ['kategori', 'nama_produk', 'harga_modal', 'harga_jual'];
        $this->validateHeaders($requiredHeaders, $collection);
 
        // Custom business logic validation
        if ($collection->count() > 1000) {
            $this->stopImportWithError('Jumlah baris melebihi batas maksimum 1000. Silakan kurangi data dan coba lagi.');
        }

        $this->setAfterValidationMutator(function (array $data) {
            return [
                'category_id' => $this->firstOrCreateCategory($data['kategori']),
                'sku' => Arr::get($data, 'sku'),
                'name' => $data['nama_produk'],
                'cost_price' => $data['harga_modal'],
                'price' => $data['harga_jual'],
                'stock' => Arr::get($data, 'stok', 0),
                'barcode' => Arr::get($data, 'barcode'),
                'description' => Arr::get($data, 'deskripsi'),
            ];
        });
    }
    
    /**
     * Validasi sebelum membuat record, hentikan import jika nama produk sudah ada.
     */
    protected function beforeCreateRecord(array $data, $row): void
    {
        if (Product::where('name', $data['name'])->exists()) {
            $this->stopImportWithError("Produk dengan nama {$data['name']} sudah ada di database. Import dihentikan.");
        }
    }

    protected function afterCollection(Collection $collection): void
    {
        // Show success message with statistics
        $count = $collection->count();
        $this->stopImportWithSuccess("Berhasil mengimpor {$count} produk!");
    }
}
