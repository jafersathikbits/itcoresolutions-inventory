<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <x-slot name="header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Today's activity and stock health at a glance.</p>
        </div>
        <div class="page-actions">
            @can('orders.create')
                <a href="{{ route('orders.create') }}" class="btn btn-primary">
                    <x-icon name="plus" class="h-4 w-4" /> Place Order
                </a>
            @endcan
            @can('materials.view')
                <a href="{{ route('materials.index', ['filter' => 'low']) }}" class="btn btn-secondary">
                    <x-icon name="warning" class="h-4 w-4" /> Low Stock
                </a>
            @endcan
        </div>
    </x-slot>

    @php
        $cards = [
            ['label' => 'Orders Today', 'value' => $ordersTodayTotal, 'icon' => 'clipboard', 'tone' => 'bg-slate-900 text-white', 'href' => route('orders.index', ['date' => 'today'])],
            ['label' => 'Received', 'value' => $statusCounts['Received'], 'icon' => 'inbox', 'tone' => 'bg-brand-50 text-brand-600', 'href' => route('orders.index', ['date' => 'today', 'status' => 'Received'])],
            ['label' => 'In Progress', 'value' => $statusCounts['In Progress'], 'icon' => 'refresh', 'tone' => 'bg-amber-50 text-amber-600', 'href' => route('orders.index', ['date' => 'today', 'status' => 'In Progress'])],
            ['label' => 'Completed', 'value' => $statusCounts['Completed'], 'icon' => 'check-circle', 'tone' => 'bg-emerald-50 text-emerald-600', 'href' => route('orders.index', ['date' => 'today', 'status' => 'Completed'])],
            ['label' => 'Dispatched', 'value' => $statusCounts['Dispatched'], 'icon' => 'truck', 'tone' => 'bg-violet-50 text-violet-600', 'href' => route('orders.index', ['date' => 'today', 'status' => 'Dispatched'])],
            ['label' => 'Low Stock', 'value' => $lowStockCount, 'icon' => 'warning', 'tone' => $lowStockCount > 0 ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600', 'href' => auth()->user()->can('materials.view') ? route('materials.index', ['filter' => 'low']) : null],
        ];
    @endphp

    <div class="page space-y-6">
        <!-- KPI cards -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
            @foreach($cards as $card)
                @php $tag = $card['href'] ? 'a' : 'div'; @endphp
                <{{ $tag }} @if($card['href']) href="{{ $card['href'] }}" @endif class="stat-card">
                    <div class="stat-icon {{ $card['tone'] }}">
                        <x-icon :name="$card['icon']" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <div class="stat-label">{{ $card['label'] }}</div>
                        <div class="stat-value">{{ number_format($card['value']) }}</div>
                    </div>
                </{{ $tag }}>
            @endforeach
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
            <div class="card lg:col-span-2">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Order Status (Today)</h3>
                        <p class="card-subtitle">Click a slice to open those orders.</p>
                    </div>
                </div>
                <div class="card-body">
                    @if($ordersTodayTotal > 0)
                        <div class="h-72"><canvas id="statusDonut" aria-label="Order status chart" role="img"></canvas></div>
                    @else
                        <x-empty-state icon="clipboard" title="No orders placed today" class="h-72" />
                    @endif
                </div>
            </div>

            <div class="card lg:col-span-3">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Most Critical Materials</h3>
                        <p class="card-subtitle">Current stock vs threshold for materials at or below threshold.</p>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($topLowForChart) > 0)
                        <div class="h-72"><canvas id="lowBar" aria-label="Low stock chart" role="img"></canvas></div>
                    @else
                        <x-empty-state icon="check-circle" title="All materials are above threshold" class="h-72" />
                    @endif
                </div>
            </div>
        </div>

        <!-- Tables -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
            <div class="card xl:col-span-3 overflow-hidden">
                <div class="card-header">
                    <h3 class="card-title">Latest Orders</h3>
                    @can('orders.view')
                        <a href="{{ route('orders.index') }}" class="link text-sm">View all</a>
                    @endcan
                </div>
                @if($latestOrders->isEmpty())
                    <x-empty-state icon="clipboard" title="No orders yet" />
                @else
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Product</th>
                                    <th class="text-right">Qty</th>
                                    <th>Status</th>
                                    <th>Placed</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestOrders as $o)
                                    @php $item = $o->items->first(); @endphp
                                    <tr>
                                        <td class="font-medium text-slate-900 whitespace-nowrap">{{ $o->order_no }}</td>
                                        <td>{{ $item?->product?->name ?? '-' }}</td>
                                        <td class="num"><x-qty :value="$item?->quantity" /></td>
                                        <td><x-status-badge :status="$o->status" /></td>
                                        <td class="whitespace-nowrap text-slate-500" title="{{ $o->placed_at?->format('Y-m-d H:i') }}">{{ $o->placed_at?->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="card xl:col-span-2 overflow-hidden">
                <div class="card-header">
                    <h3 class="card-title">Low Stock Materials</h3>
                    @can('materials.view')
                        <a href="{{ route('materials.index', ['filter' => 'low']) }}" class="link text-sm">View all</a>
                    @endcan
                </div>
                @if($lowMaterials->isEmpty())
                    <x-empty-state icon="check-circle" title="Nothing is running low" />
                @else
                    <ul class="divide-y divide-slate-100">
                        @foreach($lowMaterials as $m)
                            @php
                                $pct = (float) $m->threshold > 0 ? min(100, max(0, ((float) $m->stock / (float) $m->threshold) * 100)) : 0;
                            @endphp
                            <li class="px-5 py-3">
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    @can('materials.view')
                                        <a href="{{ route('materials.show', $m) }}" class="font-medium text-slate-800 hover:text-brand-700 truncate">{{ $m->name }}</a>
                                    @else
                                        <span class="font-medium text-slate-800 truncate">{{ $m->name }}</span>
                                    @endcan
                                    <span class="shrink-0 tabular-nums text-slate-600">
                                        <x-qty :value="$m->stock" class="font-semibold text-red-600" /> / <x-qty :value="$m->threshold" :unit="$m->unit?->symbol" />
                                    </span>
                                </div>
                                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $pct < 50 ? 'bg-red-500' : 'bg-amber-500' }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            (function () {
                if (typeof Chart === 'undefined') return;

                Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, sans-serif';
                Chart.defaults.color = '#64748b';

                /* ---------- Donut: order status today ---------- */
                const donutEl = document.getElementById('statusDonut');
                if (donutEl) {
                    const donutLabels = @json($donutLabels);
                    const donutValues = @json($donutValues);
                    const ordersIndexBase = @json(route('orders.index'));

                    new Chart(donutEl, {
                        type: 'doughnut',
                        data: {
                            labels: donutLabels,
                            datasets: [{
                                data: donutValues,
                                backgroundColor: ['#2563eb', '#f59e0b', '#10b981', '#8b5cf6'],
                                borderWidth: 2,
                                borderColor: '#fff',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '65%',
                            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } } },
                            onHover: (evt, elements) => { evt.native.target.style.cursor = elements.length ? 'pointer' : 'default'; },
                            onClick: (evt, elements) => {
                                if (!elements.length) return;
                                const status = donutLabels[elements[0].index];
                                window.location.href = ordersIndexBase + '?date=today&status=' + encodeURIComponent(status);
                            }
                        }
                    });
                }

                /* ---------- Horizontal bar: stock vs threshold ---------- */
                const barEl = document.getElementById('lowBar');
                if (barEl) {
                    const lowRows = @json($topLowForChart);

                    new Chart(barEl, {
                        type: 'bar',
                        data: {
                            labels: lowRows.map(r => r.unit ? `${r.name} (${r.unit})` : r.name),
                            datasets: [
                                { label: 'Stock', data: lowRows.map(r => r.stock), backgroundColor: '#ef4444', borderRadius: 4 },
                                { label: 'Threshold', data: lowRows.map(r => r.threshold), backgroundColor: '#cbd5e1', borderRadius: 4 }
                            ]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                                y: { ticks: { autoSkip: false }, grid: { display: false } }
                            },
                            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } } }
                        }
                    });
                }
            })();
        </script>
    @endpush
</x-app-layout>
