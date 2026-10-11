<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Material;
use App\Models\Order;
use App\Models\Product;
use App\Models\Report;
use App\Models\StockLog;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * End-to-end checks of the main inventory flows:
 * units -> materials (raw + composite) -> products + BOM -> orders -> reports,
 * plus role permissions and every main page rendering.
 */
class InventoryFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $super;
    private User $admin;
    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->super = User::factory()->create(['created_at' => now()->subDay()]);
        $this->super->syncRoles(['Super Admin']);

        $this->admin = User::factory()->create();
        $this->admin->syncRoles(['Admin']);

        $this->operator = User::factory()->create();
        $this->operator->syncRoles(['Operator']);
    }

    private function makeCatalog(): array
    {
        $unit = Unit::create(['name' => 'Square feet', 'symbol' => 'sqft', 'allow_decimal' => true]);
        $nos = Unit::create(['name' => 'Numbers', 'symbol' => 'nos', 'allow_decimal' => false]);

        $angle = Material::create(['name' => 'Aluminium L angle', 'unit_id' => $unit->id, 'stock' => 100, 'threshold' => 10]);
        $net = Material::create(['name' => 'Stainless net', 'unit_id' => $unit->id, 'stock' => 20, 'threshold' => 5]);
        $tray = Material::create(['name' => 'Tray', 'unit_id' => $nos->id, 'stock' => 0, 'threshold' => 0, 'is_composite' => true]);
        $tray->components()->create(['child_material_id' => $angle->id, 'qty_per_parent' => 4]);
        $tray->components()->create(['child_material_id' => $net->id, 'qty_per_parent' => 1]);

        $product = Product::create(['name' => 'Dryer']);
        $product->materials()->attach($tray->id, ['qty_per_product' => 2]);
        $product->materials()->attach($angle->id, ['qty_per_product' => 1]);

        return compact('unit', 'nos', 'angle', 'net', 'tray', 'product');
    }

    public function test_all_main_pages_render_for_super_admin(): void
    {
        $c = $this->makeCatalog();
        $this->actingAs($this->super)->post(route('orders.store'), ['product_id' => $c['product']->id, 'quantity' => 1]);
        $this->actingAs($this->super)->post(route('reports.generateNow'));

        $urls = [
            route('dashboard'),
            route('notifications.index'),
            route('notifications.index', ['filter' => 'unread']),
            route('units.index'), route('units.create'), route('units.edit', $c['unit']),
            route('materials.index'), route('materials.index', ['filter' => 'low']), route('materials.index', ['q' => 'net']),
            route('materials.create'), route('materials.show', $c['tray']), route('materials.show', $c['angle']),
            route('materials.edit', $c['angle']), route('materials.components.index', $c['tray']),
            route('products.index'), route('products.index', ['q' => 'dry']), route('products.create'),
            route('products.show', $c['product']), route('products.edit', $c['product']), route('products.bom.index', $c['product']),
            route('orders.index'), route('orders.index', ['date' => 'today', 'status' => 'Received']), route('orders.create'),
            route('reports.index'),
            route('users.index'), route('users.create'), route('users.edit', $this->operator), route('users.transferOwnershipForm'),
            route('profile.edit'),
        ];

        foreach ($urls as $url) {
            $this->actingAs($this->super)->get($url)->assertOk();
        }
    }

    public function test_operator_pages_and_permissions(): void
    {
        $c = $this->makeCatalog();

        foreach ([route('dashboard'), route('materials.index'), route('products.index'), route('orders.index'), route('orders.create'), route('reports.index'), route('profile.edit')] as $url) {
            $this->actingAs($this->operator)->get($url)->assertOk();
        }

        $this->actingAs($this->operator)->get(route('units.index'))->assertForbidden();
        $this->actingAs($this->operator)->get(route('users.index'))->assertForbidden();
        $this->actingAs($this->operator)->post(route('materials.stock.add', $c['angle']), ['qty' => 5])->assertForbidden();
    }

    public function test_order_expands_composites_and_deducts_stock(): void
    {
        $c = $this->makeCatalog();

        // 2 products -> trays: 4, angle: 4*4 + 2*1 = 18, net: 4*1 = 4
        $this->actingAs($this->operator)
            ->post(route('orders.store'), ['product_id' => $c['product']->id, 'quantity' => 2])
            ->assertRedirect(route('orders.index'));

        $this->assertEquals(82.0, (float) $c['angle']->fresh()->stock);
        $this->assertEquals(16.0, (float) $c['net']->fresh()->stock);
        $this->assertSame(1, Order::count());
        $this->assertMatchesRegularExpression('/^ORD-\d{4}-\d{6}$/', Order::first()->order_no);
        $this->assertSame(2, StockLog::where('type', 'deduct')->count());
    }

    public function test_order_is_blocked_when_stock_is_insufficient_and_admins_are_notified(): void
    {
        $c = $this->makeCatalog();

        $this->actingAs($this->operator)
            ->from(route('orders.create'))
            ->post(route('orders.store'), ['product_id' => $c['product']->id, 'quantity' => 50])
            ->assertRedirect(route('orders.create'))
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
        $this->assertEquals(100.0, (float) $c['angle']->fresh()->stock);

        // admin + super admin get an in-app notification, stored as plain text (no HTML tags)
        $this->assertSame(2, AppNotification::where('type', 'order_blocked')->count());
        $this->assertStringNotContainsString('<b>', AppNotification::first()->message);
    }

    public function test_order_warning_when_stock_drops_below_threshold(): void
    {
        $c = $this->makeCatalog();

        // 8 products -> 16 trays: net 20 - 16 = 4 <= threshold 5 -> warning, order still placed
        $this->actingAs($this->operator)
            ->post(route('orders.store'), ['product_id' => $c['product']->id, 'quantity' => 8])
            ->assertSessionHas('success');

        $this->assertSame(1, Order::count());
        $this->assertSame(2, AppNotification::where('type', 'order_warning')->count());
    }

    public function test_status_update_returns_to_previous_page(): void
    {
        $c = $this->makeCatalog();
        $this->actingAs($this->operator)->post(route('orders.store'), ['product_id' => $c['product']->id, 'quantity' => 1]);
        $order = Order::first();

        $back = route('orders.index', ['status' => 'Received', 'page' => 1]);
        $this->actingAs($this->operator)
            ->from($back)
            ->post(route('orders.status.update', $order), ['status' => 'Completed'])
            ->assertRedirect($back);

        $this->assertSame('Completed', $order->fresh()->status);
    }

    public function test_material_crud_with_image_and_stock_add(): void
    {
        $unit = Unit::create(['name' => 'Kilogram', 'symbol' => 'kg', 'allow_decimal' => true]);

        $this->actingAs($this->admin)->post(route('materials.store'), [
            'name' => 'Steel rod',
            'unit_id' => $unit->id,
            'stock' => 5,
            'threshold' => 2,
            'image' => UploadedFile::fake()->image('rod.jpg'),
        ])->assertRedirect(route('materials.index'));

        $material = Material::where('name', 'Steel rod')->firstOrFail();
        Storage::disk('public')->assertExists($material->image_path);

        $this->actingAs($this->admin)->post(route('materials.stock.add', $material), ['qty' => 2.5])->assertRedirect();
        $this->assertEquals(7.5, (float) $material->fresh()->stock);

        $oldImage = $material->image_path;
        $this->actingAs($this->admin)->put(route('materials.update', $material), [
            'name' => 'Steel rod 8mm',
            'unit_id' => $unit->id,
            'stock' => 7.5,
            'threshold' => 2,
            'image' => UploadedFile::fake()->image('rod2.jpg'),
        ])->assertRedirect(route('materials.show', $material));
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($material->fresh()->image_path);

        $this->actingAs($this->admin)->delete(route('materials.destroy', $material))->assertRedirect(route('materials.index'));
        $this->assertNull(Material::find($material->id));
    }

    public function test_material_in_use_keeps_its_image_when_delete_fails(): void
    {
        $c = $this->makeCatalog();
        Storage::disk('public')->put('materials/angle.jpg', 'x');
        $c['angle']->update(['image_path' => 'materials/angle.jpg']);

        // SQLite enforces FKs in tests: angle is used by a composite and a BOM
        $this->actingAs($this->admin)->delete(route('materials.destroy', $c['angle']))->assertSessionHas('error');

        $this->assertNotNull(Material::find($c['angle']->id));
        Storage::disk('public')->assertExists('materials/angle.jpg');
    }

    public function test_component_material_cannot_become_composite(): void
    {
        $c = $this->makeCatalog();

        $this->actingAs($this->admin)->put(route('materials.update', $c['net']), [
            'name' => 'Stainless net',
            'unit_id' => $c['unit']->id,
            'stock' => 20,
            'threshold' => 5,
            'is_composite' => 1,
        ])->assertSessionHasErrors('is_composite');

        $this->assertFalse($c['net']->fresh()->is_composite);
    }

    public function test_report_generation_and_download(): void
    {
        $this->makeCatalog();

        $this->actingAs($this->admin)->post(route('reports.generateNow'))->assertRedirect(route('reports.index'));
        $report = Report::firstOrFail();
        Storage::disk('public')->assertExists($report->file_path);

        $response = $this->actingAs($this->admin)->get(route('reports.download', $report));
        $response->assertOk();
        $this->assertStringContainsString(
            'Daily-Report-' . now()->toDateString() . '.pdf',
            $response->headers->get('content-disposition')
        );
    }

    public function test_super_admin_can_transfer_ownership(): void
    {
        $other = User::factory()->create();
        $other->syncRoles(['Super Admin']);

        $this->actingAs($this->super)
            ->post(route('users.transferOwnership'), ['target_user_id' => $other->id])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertTrue(app(\App\Services\OwnershipService::class)->isOwner($other->fresh()));
    }

    public function test_profile_email_must_be_unique(): void
    {
        $this->actingAs($this->operator)->put(route('profile.update'), [
            'name' => 'Op',
            'email' => $this->admin->email,
        ])->assertSessionHasErrors('email');
    }
}
