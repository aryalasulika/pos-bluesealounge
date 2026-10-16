<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users
        $admin = User::create([
            'name' => 'Administrator Bluesea',
            'username' => 'admin',
            'email' => 'admin@bluesea.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $cashier = User::create([
            'name' => 'Siska Kasir',
            'username' => 'kasir1',
            'email' => 'kasir1@bluesea.com',
            'password' => Hash::make('kasir123'),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'system_initialized',
            'entity_type' => 'System',
            'entity_id' => 1,
            'reason' => 'Inisialisasi sistem Bluesea Lounge POS pertama kali',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
        ]);

        // 2. Categories
        $categoriesData = [
            ['name' => 'Makanan Utama', 'description' => 'Aneka hidangan makanan berat & makanan utama'],
            ['name' => 'Kopi Spesial', 'description' => 'Minuman kopi espresso, latte, & cappuccino'],
            ['name' => 'Non-Kopi & Jus', 'description' => 'Aneka teh, jus segar, & minuman ringan'],
            ['name' => 'Snack & Pastry', 'description' => 'Camilan ringan, croissant, & french fries'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['name']] = Category::create([
                'name' => $cat['name'],
                'description' => $cat['description'],
                'is_active' => true,
            ]);
        }

        // 3. Products
        $productsData = [
            [
                'category_name' => 'Makanan Utama',
                'sku' => 'MKN-001',
                'barcode' => '8991001001',
                'name' => 'Nasi Goreng Spesial Bluesea',
                'selling_price' => 45000,
                'current_stock' => 25,
                'minimum_stock' => 5,
                'unit' => 'porsi',
            ],
            [
                'category_name' => 'Makanan Utama',
                'sku' => 'MKN-002',
                'barcode' => '8991001002',
                'name' => 'Club Sandwich Lounge',
                'selling_price' => 38000,
                'current_stock' => 18,
                'minimum_stock' => 5,
                'unit' => 'porsi',
            ],
            [
                'category_name' => 'Makanan Utama',
                'sku' => 'MKN-003',
                'barcode' => '8991001003',
                'name' => 'Mie Goreng Seafood Spesial',
                'selling_price' => 42000,
                'current_stock' => 20,
                'minimum_stock' => 5,
                'unit' => 'porsi',
            ],
            [
                'category_name' => 'Kopi Spesial',
                'sku' => 'KOP-001',
                'barcode' => '8992001001',
                'name' => 'Iced Caffe Latte',
                'selling_price' => 35000,
                'current_stock' => 35,
                'minimum_stock' => 10,
                'unit' => 'cup',
            ],
            [
                'category_name' => 'Kopi Spesial',
                'sku' => 'KOP-002',
                'barcode' => '8992001002',
                'name' => 'Hot Americano Premium',
                'selling_price' => 28000,
                'current_stock' => 30,
                'minimum_stock' => 10,
                'unit' => 'cup',
            ],
            [
                'category_name' => 'Non-Kopi & Jus',
                'sku' => 'NKP-001',
                'barcode' => '8993001001',
                'name' => 'Matcha Green Tea Latte',
                'selling_price' => 32000,
                'current_stock' => 22,
                'minimum_stock' => 8,
                'unit' => 'cup',
            ],
            [
                'category_name' => 'Non-Kopi & Jus',
                'sku' => 'NKP-002',
                'barcode' => '8993001002',
                'name' => 'Fresh Orange Juice',
                'selling_price' => 25000,
                'current_stock' => 15,
                'minimum_stock' => 5,
                'unit' => 'cup',
            ],
            [
                'category_name' => 'Non-Kopi & Jus',
                'sku' => 'NKP-003',
                'barcode' => '8993001003',
                'name' => 'Air Mineral 600ml',
                'selling_price' => 12000,
                'current_stock' => 50,
                'minimum_stock' => 15,
                'unit' => 'botol',
            ],
            [
                'category_name' => 'Snack & Pastry',
                'sku' => 'SNK-001',
                'barcode' => '8994001001',
                'name' => 'Butter Croissant Premium',
                'selling_price' => 28000,
                'current_stock' => 14,
                'minimum_stock' => 5,
                'unit' => 'pcs',
            ],
            [
                'category_name' => 'Snack & Pastry',
                'sku' => 'SNK-002',
                'barcode' => '8994001002',
                'name' => 'Truffle French Fries',
                'selling_price' => 30000,
                'current_stock' => 4, // Intentionally low stock for alert testing
                'minimum_stock' => 6,
                'unit' => 'porsi',
            ],
        ];

        foreach ($productsData as $item) {
            $cat = $categories[$item['category_name']];
            $prod = Product::create([
                'category_id' => $cat->id,
                'sku' => $item['sku'],
                'barcode' => $item['barcode'],
                'name' => $item['name'],
                'selling_price' => $item['selling_price'],
                'current_stock' => $item['current_stock'],
                'minimum_stock' => $item['minimum_stock'],
                'unit' => $item['unit'],
                'is_active' => true,
            ]);

            // Initial stock movement
            StockMovement::create([
                'product_id' => $prod->id,
                'movement_type' => 'manual_in',
                'quantity_change' => $item['current_stock'],
                'stock_before' => 0,
                'stock_after' => $item['current_stock'],
                'reference_type' => 'initial_setup',
                'reason' => 'Stok awal setup lounge',
                'user_id' => $admin->id,
                'created_at' => now(),
            ]);
        }

        // 4. Sample Purchase
        $purchase = Purchase::create([
            'purchase_number' => 'PO-' . date('Ymd') . '-0001',
            'purchase_date' => now()->subDays(2)->toDateString(),
            'supplier_name' => 'PT Suplai Boga Nusantara',
            'total_amount' => 650000,
            'notes' => 'Pengadaan bahan baku kemasan dan kopi bulanan',
            'recorded_by' => $admin->id,
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_name_snapshot' => 'Biji Kopi Arabika House Blend 1kg',
            'quantity' => 5,
            'unit' => 'pack',
            'purchase_price' => 130000,
            'subtotal' => 650000,
        ]);

        // 5. Sample Expense
        Expense::create([
            'expense_date' => now()->toDateString(),
            'category' => 'Operasional Kebersihan',
            'amount' => 75000,
            'description' => 'Pembelian tisu lounge dan sabun cuci bar',
            'recorded_by' => $admin->id,
        ]);
    }
}
