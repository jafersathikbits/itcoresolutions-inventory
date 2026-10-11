<x-app-layout>
    <x-slot name="title">Place Order</x-slot>

    <div class="page" x-data="orderForm(@js($requirementsByProduct), @js((string) old('product_id', request('product_id', ''))), @js((string) old('quantity', 1)))">
        <a href="{{ route('orders.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">
            <x-icon name="arrow-left" class="h-4 w-4" /> Orders
        </a>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
            <!-- Form -->
            <div class="card self-start lg:col-span-2">
                <div class="card-header">
                    <div>
                        <h1 class="card-title text-lg">Place Order</h1>
                        <p class="card-subtitle">Stock is checked automatically: orders are allowed, allowed with a low-stock warning, or blocked.</p>
                    </div>
                </div>
                <div class="card-body">
                    <x-form-errors />

                    <form method="POST" action="{{ route('orders.store') }}" class="space-y-5" @submit="submitting = true">
                        @csrf

                        <div>
                            <label for="product_id" class="form-label">Product <span class="text-red-500">*</span></label>
                            <select id="product_id" name="product_id" class="form-input" required x-model="productId">
                                <option value="">Select product</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="quantity" class="form-label">Quantity <span class="text-red-500">*</span></label>
                            <input id="quantity" name="quantity" type="number" step="0.001" min="0.001" class="form-input" required x-model="quantity">
                        </div>

                        <div>
                            <label for="notes" class="form-label">Notes <span class="font-normal text-slate-400">(optional)</span></label>
                            <textarea id="notes" name="notes" rows="3" maxlength="500" class="form-input" placeholder="Customer, delivery details...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="form-actions border-t border-slate-100 pt-5">
                            <button type="submit" class="btn" :class="status === 'blocked' ? 'btn-danger' : 'btn-primary'" :disabled="submitting">
                                <x-icon name="check" class="h-4 w-4" />
                                <span x-text="submitting ? 'Placing...' : 'Place Order'">Place Order</span>
                            </button>
                            <a href="{{ route('orders.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Live stock check -->
            <div class="card overflow-hidden lg:col-span-3">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Stock Check Preview</h3>
                        <p class="card-subtitle">Raw materials this order will use.</p>
                    </div>
                    <template x-if="rows.length">
                        <span class="badge"
                              :class="{ 'badge-green': status === 'success', 'badge-amber': status === 'warning', 'badge-red': status === 'blocked' }"
                              x-text="{ success: 'Stock OK', warning: 'Will go low', blocked: 'Insufficient stock' }[status]"></span>
                    </template>
                </div>

                <template x-if="!productId">
                    <x-empty-state icon="box" title="Select a product">The required materials and stock levels will appear here.</x-empty-state>
                </template>

                <template x-if="productId && !rows.length">
                    <div class="p-5">
                        <div class="alert alert-warning">
                            <x-icon name="warning" class="h-5 w-5 shrink-0" />
                            <div>This product has no BOM configured, so the order will be rejected. Configure the BOM first.</div>
                        </div>
                    </div>
                </template>

                <template x-if="rows.length">
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Material</th>
                                    <th class="text-right">Required</th>
                                    <th class="text-right">In stock</th>
                                    <th class="text-right">After order</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="row in rows" :key="row.name">
                                    <tr :class="row.state === 'short' ? 'bg-red-50/60' : (row.state === 'low' ? 'bg-amber-50/60' : '')">
                                        <td class="font-medium text-slate-800" x-text="row.name"></td>
                                        <td class="num"><span x-text="fmt(row.required)"></span> <span class="text-slate-400" x-text="row.unit"></span></td>
                                        <td class="num text-slate-600" x-text="fmt(row.stock)"></td>
                                        <td class="num font-medium" :class="row.state === 'short' ? 'text-red-600' : (row.state === 'low' ? 'text-amber-700' : 'text-slate-900')" x-text="fmt(row.after)"></td>
                                        <td class="text-right">
                                            <span class="badge" :class="{ 'badge-green': row.state === 'ok', 'badge-amber': row.state === 'low', 'badge-red': row.state === 'short' }"
                                                  x-text="{ ok: 'OK', low: 'Low after', short: 'Short' }[row.state]"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>

                <div class="border-t border-slate-100 bg-slate-50 px-5 py-3 text-xs text-slate-500">
                    Stock shown is as of page load. It is re-checked when you submit, and Admins are notified of blocked orders and low stock.
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function orderForm(requirements, productId, quantity) {
                return {
                    requirements, productId, quantity, submitting: false,
                    fmt(n) { return (Math.round(n * 1000) / 1000).toLocaleString(undefined, { maximumFractionDigits: 3 }); },
                    get rows() {
                        const qty = parseFloat(this.quantity) || 0;
                        return (this.requirements[this.productId] || []).map(r => {
                            const required = Math.round(r.per_unit * qty * 1000) / 1000;
                            const after = Math.round((r.stock - required) * 1000) / 1000;
                            const state = r.stock < required ? 'short' : (after <= r.threshold ? 'low' : 'ok');
                            return { ...r, required, after, state };
                        });
                    },
                    get status() {
                        const rows = this.rows;
                        if (rows.some(r => r.state === 'short')) return 'blocked';
                        if (rows.some(r => r.state === 'low')) return 'warning';
                        return 'success';
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>
