<x-app-layout>
    <x-slot name="title">Orders</x-slot>

    @php
        // The date filter accepts "today" or YYYY-MM-DD; the date picker needs a real date
        $dateValue = $filterDate === 'today' ? now()->toDateString() : $filterDate;
        $hasFilters = $filterStatus || $filterDate;
    @endphp

    <x-slot name="header">
        <div>
            <h1 class="page-title">Orders</h1>
            <p class="page-subtitle">Track orders and update their status.</p>
        </div>
        @can('orders.create')
            <div class="page-actions">
                <a href="{{ route('orders.create') }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4" /> Place Order</a>
            </div>
        @endcan
    </x-slot>

    <div class="page">
        <div class="card overflow-hidden">
            <!-- Filters -->
            <form method="GET" action="{{ route('orders.index') }}" class="flex flex-col gap-3 border-b border-slate-100 p-4 lg:flex-row lg:items-end">
                <div class="lg:w-48">
                    <label for="f-status" class="form-label text-xs">Status</label>
                    <select id="f-status" name="status" class="form-input" onchange="this.form.submit()">
                        <option value="">All statuses</option>
                        @foreach($allowedStatuses as $st)
                            <option value="{{ $st }}" @selected($filterStatus === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:w-48">
                    <label for="f-date" class="form-label text-xs">Date</label>
                    <input id="f-date" type="date" name="date" value="{{ $dateValue }}" max="{{ now()->toDateString() }}" class="form-input" onchange="this.form.submit()">
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('orders.index', array_filter(['status' => $filterStatus, 'date' => 'today'])) }}" class="btn btn-secondary {{ $filterDate === 'today' || $filterDate === now()->toDateString() ? 'ring-2 ring-brand-500' : '' }}">
                        <x-icon name="calendar" class="h-4 w-4" /> Today
                    </a>
                    <button type="submit" class="btn btn-dark">Apply</button>
                    @if($hasFilters)
                        <a href="{{ route('orders.index') }}" class="btn btn-ghost"><x-icon name="x" class="h-4 w-4" /> Clear</a>
                    @endif
                </div>

                <div class="text-sm text-slate-500 lg:ml-auto lg:pb-2">
                    <b class="text-slate-800">{{ number_format($orders->total()) }}</b> {{ \Illuminate\Support\Str::plural('order', $orders->total()) }}
                </div>
            </form>

            @if($orders->isEmpty())
                <x-empty-state icon="clipboard" :title="$hasFilters ? 'No orders match these filters' : 'No orders yet'" />
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Product</th>
                                <th class="text-right">Qty</th>
                                <th>Placed</th>
                                <th>Status</th>
                                <th class="text-right">Update status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $o)
                                @php $item = $o->items->first(); @endphp
                                <tr>
                                    <td class="whitespace-nowrap">
                                        <div class="font-semibold text-slate-900">{{ $o->order_no }}</div>
                                        @if($o->notes)
                                            <div class="max-w-[16rem] truncate text-xs text-slate-500" title="{{ $o->notes }}">{{ $o->notes }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $item?->product?->name ?? '-' }}</td>
                                    <td class="num"><x-qty :value="$item?->quantity" /></td>
                                    <td class="whitespace-nowrap">
                                        <div class="text-slate-800">{{ $o->placed_at?->format('d M Y, H:i') }}</div>
                                        <div class="text-xs text-slate-500">by {{ $o->user?->name ?? '-' }}</div>
                                    </td>
                                    <td><x-status-badge :status="$o->status" /></td>
                                    <td class="text-right">
                                        @can('orders.update_status')
                                            <form method="POST" action="{{ route('orders.status.update', $o) }}" class="inline-flex items-center gap-2">
                                                @csrf
                                                <select name="status" class="form-input mt-0 w-36 py-1.5" aria-label="Status for {{ $o->order_no }}" onchange="this.form.submit()">
                                                    @foreach($allowedStatuses as $st)
                                                        <option value="{{ $st }}" @selected($o->status === $st)>{{ $st }}</option>
                                                    @endforeach
                                                </select>
                                                <noscript><button class="btn btn-secondary btn-sm">Update</button></noscript>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($orders->hasPages())
                    <div class="border-t border-slate-100 px-4 py-3">{{ $orders->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
