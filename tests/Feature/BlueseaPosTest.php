<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BlueseaPosTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected User $cashier;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['username' => 'test_admin'],
            [
                'name' => 'Test Admin',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->cashier = User::firstOrCreate(
            ['username' => 'test_cashier'],
            [
                'name' => 'Test Cashier',
                'password' => bcrypt('password123'),
                'role' => 'cashier',
                'is_active' => true,
            ]
        );

        $category = Category::firstOrCreate(['name' => 'Test Category']);

        $this->product = Product::firstOrCreate(
            ['sku' => 'TEST-001'],
            [
                'category_id' => $category->id,
                'name' => 'Test Coffee',
                'selling_price' => 30000,
                'current_stock' => 15,
                'minimum_stock' => 5,
                'unit' => 'cup',
                'is_active' => true,
            ]
        );
    }

    /**
     * Test FR-01: Authentication with username & password
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'username' => 'test_admin',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'username' => 'test_admin',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $inactive = User::create([
            'name' => 'Inactive User',
            'username' => 'inactive_user',
            'password' => bcrypt('password123'),
            'role' => 'cashier',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'username' => 'inactive_user',
            'password' => 'password123',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    /**
     * Test Role Matrix (Matriks Hak Akses): Cashier cannot access Admin routes
     */
    public function test_cashier_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->cashier)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_cashier_cannot_access_admin_products(): void
    {
        $response = $this->actingAs($this->cashier)->get('/admin/products');
        $response->assertStatus(403);
    }

    public function test_cashier_can_access_pos_terminal(): void
    {
        $response = $this->actingAs($this->cashier)->get('/pos');
        $response->assertStatus(200);
    }

    /**
     * Test FR-06 & BR-05: POS Sale process atomicity and stock deduction
     */
    public function test_pos_transaction_deducts_stock_and_creates_records(): void
    {
        $initialStock = $this->product->current_stock;
        $qtyToBuy = 2;

        $response = $this->actingAs($this->cashier)->postJson('/pos/process', [
            'items' => [
                ['id' => $this->product->id, 'quantity' => $qtyToBuy],
            ],
            'payment_method' => 'cash',
            'payment_amount' => 100000,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify stock is decremented
        $this->product->refresh();
        $this->assertEquals($initialStock - $qtyToBuy, $this->product->current_stock);

        // Verify sale record created
        $this->assertDatabaseHas('sales', [
            'cashier_id' => $this->cashier->id,
            'total' => $this->product->selling_price * $qtyToBuy,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        // Verify stock movement created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'movement_type' => 'sale',
            'quantity_change' => -$qtyToBuy,
        ]);
    }

    /**
     * Test FR-06 & BR-08: Stock cannot become negative
     */
    public function test_pos_transaction_fails_when_quantity_exceeds_stock(): void
    {
        $excessiveQty = $this->product->current_stock + 10;

        $response = $this->actingAs($this->cashier)->postJson('/pos/process', [
            'items' => [
                ['id' => $this->product->id, 'quantity' => $excessiveQty],
            ],
            'payment_method' => 'cash',
            'payment_amount' => 1000000,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test FR-19, BR-10, BR-12: Cancellation restores stock atomically
     */
    public function test_admin_can_cancel_sale_and_stock_is_returned(): void
    {
        // 1. First complete a sale
        $initialStock = $this->product->current_stock;
        $qty = 3;

        $this->actingAs($this->cashier)->postJson('/pos/process', [
            'items' => [
                ['id' => $this->product->id, 'quantity' => $qty],
            ],
            'payment_method' => 'cash',
            'payment_amount' => 100000,
        ]);

        $this->product->refresh();
        $this->assertEquals($initialStock - $qty, $this->product->current_stock);

        $sale = Sale::latest()->first();

        // 2. Cashier cannot cancel
        $cashierCancel = $this->actingAs($this->cashier)->post('/admin/sales/' . $sale->id . '/cancel', [
            'cancellation_reason' => 'Batal oleh kasir',
        ]);
        $cashierCancel->assertStatus(403);

        // 3. Admin cancels sale with required reason
        $adminCancel = $this->actingAs($this->admin)->post('/admin/sales/' . $sale->id . '/cancel', [
            'cancellation_reason' => 'Tamu mendadak boarding ke pesawat',
        ]);
        $adminCancel->assertSessionHas('success');

        // 4. Verify stock is returned
        $this->product->refresh();
        $this->assertEquals($initialStock, $this->product->current_stock);

        // 5. Verify sale status is cancelled
        $sale->refresh();
        $this->assertEquals('cancelled', $sale->status);
        $this->assertEquals($this->admin->id, $sale->cancelled_by);

        // 6. Verify cancellation return stock movement recorded
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'movement_type' => 'cancellation_return',
            'quantity_change' => $qty,
        ]);
    }

    /**
     * Test FR-18: Excel & PDF Exports
     */
    public function test_admin_can_export_sales_report_pdf_and_excel(): void
    {
        $pdfResponse = $this->actingAs($this->admin)->get('/admin/reports/sales?export=pdf');
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');

        $excelResponse = $this->actingAs($this->admin)->get('/admin/reports/sales?export=excel');
        $excelResponse->assertStatus(200);
        $excelResponse->assertHeader('content-type', 'application/vnd.ms-excel; charset=utf-8');
    }

    /**
     * Test FR-11: Stocktake process
     */
    public function test_admin_can_record_stocktake_and_stock_is_adjusted(): void
    {
        $newPhysicalStock = 18;

        $response = $this->actingAs($this->admin)->post('/admin/stocktakes', [
            'product_id' => $this->product->id,
            'physical_stock' => $newPhysicalStock,
            'reason' => 'Audit berkala tim gudang lounge',
        ]);

        $response->assertSessionHas('success');

        $this->product->refresh();
        $this->assertEquals($newPhysicalStock, $this->product->current_stock);
        $this->assertDatabaseHas('stocktakes', [
            'product_id' => $this->product->id,
            'physical_stock' => $newPhysicalStock,
        ]);
    }

    /**
     * Test FR-12 & BR-07: Purchase record does not auto increment stock
     */
    public function test_purchase_recording_does_not_modify_product_stock(): void
    {
        $stockBefore = $this->product->current_stock;

        $response = $this->actingAs($this->admin)->post('/admin/purchases', [
            'purchase_date' => date('Y-m-d'),
            'supplier_name' => 'Supplier Test',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 10,
                    'unit' => 'cup',
                    'purchase_price' => 15000,
                ]
            ]
        ]);

        $response->assertSessionHas('success');

        $this->product->refresh();
        $this->assertEquals($stockBefore, $this->product->current_stock);
    }

    /**
     * Test FR-06 & Kriteria #25: repeated submission with the same idempotency
     * key must not create a duplicate transaction nor deduct stock twice.
     */
    public function test_pos_transaction_is_idempotent_on_repeated_submission(): void
    {
        $initialStock = $this->product->current_stock;
        $key = 'idem-key-' . uniqid();

        $payload = [
            'items' => [
                ['id' => $this->product->id, 'quantity' => 2],
            ],
            'payment_method' => 'cash',
            'payment_amount' => 100000,
            'idempotency_key' => $key,
        ];

        $first = $this->actingAs($this->cashier)->postJson('/pos/process', $payload);
        $first->assertStatus(200)->assertJson(['success' => true]);

        // Simulate a double-click / retry of the exact same request.
        $second = $this->actingAs($this->cashier)->postJson('/pos/process', $payload);
        $second->assertStatus(200)->assertJson(['success' => true]);

        $this->assertEquals($first->json('sale_id'), $second->json('sale_id'));
        $this->assertEquals(1, Sale::where('idempotency_key', $key)->count());

        // Stock must only be deducted once.
        $this->product->refresh();
        $this->assertEquals($initialStock - 2, $this->product->current_stock);
    }

    /**
     * Test FR-08: a cashier may only open their own receipt.
     */
    public function test_cashier_cannot_view_another_cashiers_receipt(): void
    {
        $otherCashier = User::create([
            'name' => 'Other Cashier',
            'username' => 'other_cashier_' . uniqid(),
            'password' => bcrypt('password123'),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $sale = Sale::create([
            'transaction_number' => 'TRX-TEST-' . uniqid(),
            'cashier_id' => $otherCashier->id,
            'transaction_date' => now(),
            'subtotal' => 10000,
            'total' => 10000,
            'payment_method' => 'cash',
            'payment_amount' => 10000,
            'change_amount' => 0,
            'status' => 'completed',
        ]);

        $this->actingAs($this->cashier)->get('/sales/' . $sale->id)->assertStatus(403);
        $this->actingAs($this->cashier)->get('/sales/' . $sale->id . '/print')->assertStatus(403);

        // Admin has full visibility.
        $this->actingAs($this->admin)->get('/sales/' . $sale->id)->assertStatus(200);

        // Cashier can open their own receipt.
        $own = Sale::create([
            'transaction_number' => 'TRX-TEST-' . uniqid(),
            'cashier_id' => $this->cashier->id,
            'transaction_date' => now(),
            'subtotal' => 5000,
            'total' => 5000,
            'payment_method' => 'qris',
            'payment_amount' => 5000,
            'change_amount' => 0,
            'status' => 'completed',
        ]);

        $this->actingAs($this->cashier)->get('/sales/' . $own->id)->assertStatus(200);
    }

    /**
     * Test FR-04: products with stock history cannot be permanently deleted.
     */
    public function test_product_with_stock_history_cannot_be_deleted(): void
    {
        StockMovement::create([
            'product_id' => $this->product->id,
            'movement_type' => 'manual_in',
            'quantity_change' => 5,
            'stock_before' => 10,
            'stock_after' => 15,
            'reference_type' => 'manual_addition',
            'reason' => 'Uji integritas data',
            'user_id' => $this->admin->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->delete('/admin/products/' . $this->product->id);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('products', ['id' => $this->product->id]);
    }

    /**
     * Smoke test: main pages render without server errors for their role.
     */
    public function test_main_pages_render_successfully(): void
    {
        $adminPages = [
            '/admin/dashboard',
            '/admin/products',
            '/admin/categories',
            '/admin/stocks',
            '/admin/stocks/movements',
            '/admin/stocktakes',
            '/admin/purchases',
            '/admin/expenses',
            '/admin/reports/sales',
            '/admin/reports/top-products',
            '/admin/reports/stock',
            '/admin/reports/purchases',
            '/admin/users',
            '/admin/audit-logs',
            '/sales',
        ];

        foreach ($adminPages as $page) {
            $this->actingAs($this->admin)->get($page)->assertStatus(200);
        }

        $cashierPages = ['/pos', '/sales', '/profile'];

        foreach ($cashierPages as $page) {
            $this->actingAs($this->cashier)->get($page)->assertStatus(200);
        }
    }

    /**
     * Test FR-12: purchase correction is traceable and recalculates the total.
     */
    public function test_admin_can_correct_purchase_with_audit_trail(): void
    {
        $this->actingAs($this->admin)->post('/admin/purchases', [
            'purchase_date' => date('Y-m-d'),
            'supplier_name' => 'Supplier Awal',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => 'Barang Awal',
                    'quantity' => 5,
                    'unit' => 'pack',
                    'purchase_price' => 10000,
                ],
            ],
        ]);

        $purchase = Purchase::latest()->firstOrFail();

        $response = $this->actingAs($this->admin)->put('/admin/purchases/' . $purchase->id, [
            'purchase_date' => date('Y-m-d'),
            'supplier_name' => 'Supplier Baru',
            'notes' => 'Koreksi harga & supplier',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => 'Barang Koreksi',
                    'quantity' => 2,
                    'unit' => 'pack',
                    'purchase_price' => 20000,
                ],
            ],
        ]);

        $response->assertSessionHas('success');

        $purchase->refresh();
        $this->assertEquals('Supplier Baru', $purchase->supplier_name);
        $this->assertEquals(40000, (float) $purchase->total_amount);
        $this->assertEquals(1, $purchase->items()->count());
        $this->assertDatabaseHas('purchase_items', ['product_name_snapshot' => 'Barang Koreksi']);

        // Koreksi tercatat di audit log dengan before & after.
        $log = \App\Models\AuditLog::where('action', 'purchase_updated')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertEquals('Supplier Awal', $log->before_data['supplier_name']);
        $this->assertEquals('Supplier Baru', $log->after_data['supplier_name']);
    }

    /**
     * Test FR-12 / BR-09: purchase deletion is a soft delete with audit trail.
     */
    public function test_admin_can_soft_delete_purchase_with_audit_trail(): void
    {
        $this->actingAs($this->admin)->post('/admin/purchases', [
            'purchase_date' => date('Y-m-d'),
            'supplier_name' => 'Supplier Hapus',
            'items' => [
                [
                    'product_name' => 'Barang Hapus',
                    'quantity' => 1,
                    'unit' => 'pcs',
                    'purchase_price' => 9000,
                ],
            ],
        ]);

        $purchase = Purchase::latest()->firstOrFail();

        $response = $this->actingAs($this->admin)->delete('/admin/purchases/' . $purchase->id);
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('purchases', ['id' => $purchase->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'purchase_deleted']);
        $this->assertDatabaseHas('purchase_items', ['purchase_id' => $purchase->id]); // riwayat rincian tetap tersimpan

        // Soft-deleted purchases tidak lagi muncul pada query normal (mis. daftar & laporan).
        $this->assertNull(Purchase::find($purchase->id));
        $this->assertNotNull(Purchase::withTrashed()->find($purchase->id));
    }

    /**
     * Test FR-16: product stock detail page renders its movement history.
     */
    public function test_stock_detail_page_shows_movement_history(): void
    {
        StockMovement::create([
            'product_id' => $this->product->id,
            'movement_type' => 'manual_in',
            'quantity_change' => 7,
            'stock_before' => 15,
            'stock_after' => 22,
            'reference_type' => 'manual_addition',
            'reason' => 'Uji halaman detail stok',
            'user_id' => $this->admin->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/stocks/' . $this->product->id);

        $response->assertStatus(200);
        $response->assertSee($this->product->name);
        $response->assertSee($this->product->sku);
        $response->assertSee('Uji halaman detail stok');
    }

    /**
     * Test FR-12: purchase edit page renders for an existing purchase.
     */
    public function test_purchase_edit_page_renders(): void
    {
        $this->actingAs($this->admin)->post('/admin/purchases', [
            'purchase_date' => date('Y-m-d'),
            'supplier_name' => 'Supplier Edit',
            'items' => [
                [
                    'product_name' => 'Barang Edit',
                    'quantity' => 3,
                    'unit' => 'pcs',
                    'purchase_price' => 5000,
                ],
            ],
        ]);

        $purchase = Purchase::latest()->firstOrFail();

        $this->actingAs($this->admin)
            ->get('/admin/purchases/' . $purchase->id . '/edit')
            ->assertStatus(200)
            ->assertSee('Barang Edit');
    }

    /**
     * Kebijakan keamanan: Kasir tidak dapat mengubah kata sandinya sendiri.
     */
    public function test_cashier_cannot_change_own_password(): void
    {
        $response = $this->actingAs($this->cashier)->post('/profile/password', [
            'current_password' => 'password123',
            'new_password' => 'rahasia-baru-123',
            'new_password_confirmation' => 'rahasia-baru-123',
        ]);

        $response->assertStatus(403);
        $this->assertTrue(Hash::check('password123', $this->cashier->fresh()->password));
    }

    /**
     * Admin tetap dapat mengubah kata sandi miliknya sendiri.
     */
    public function test_admin_can_change_own_password(): void
    {
        $response = $this->actingAs($this->admin)->post('/profile/password', [
            'current_password' => 'password123',
            'new_password' => 'admin-baru-123',
            'new_password_confirmation' => 'admin-baru-123',
        ]);

        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('admin-baru-123', $this->admin->fresh()->password));
    }

    /**
     * Halaman profil Kasir tampil read-only (tanpa form ubah sandi).
     */
    public function test_cashier_profile_is_read_only(): void
    {
        $response = $this->actingAs($this->cashier)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Kata Sandi Dikelola Administrator');
        $response->assertDontSee('Simpan Perubahan Sandi');
    }

    /**
     * Halaman cetak struk thermal tampil dengan ukuran kertas yang benar.
     */
    public function test_thermal_receipt_page_renders(): void
    {
        $sale = Sale::create([
            'transaction_number' => 'TRX-TEST-' . uniqid(),
            'cashier_id' => $this->cashier->id,
            'transaction_date' => now(),
            'subtotal' => 30000,
            'total' => 30000,
            'payment_method' => 'cash',
            'payment_amount' => 50000,
            'change_amount' => 20000,
            'status' => 'completed',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'product_name_snapshot' => $this->product->name,
            'sku_snapshot' => $this->product->sku,
            'quantity' => 1,
            'unit_price' => 30000,
            'subtotal' => 30000,
        ]);

        $response = $this->actingAs($this->cashier)->get('/sales/' . $sale->id . '/print');

        $response->assertStatus(200);
        $response->assertSee('BLUESEA LOUNGE');
        $response->assertSee($sale->transaction_number);
        // Ukuran kertas thermal 80mm harus diterapkan agar struk tidak terpotong.
        $response->assertSee('size: 80mm auto');
    }
}
