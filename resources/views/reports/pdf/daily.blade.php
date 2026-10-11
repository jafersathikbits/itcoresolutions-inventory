<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Report - {{ $dateYmd }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 6px; }
        .sub { color: #555; margin-bottom: 14px; }
        .section { margin-top: 16px; }
        .section h2 { font-size: 14px; margin: 0 0 8px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 10px; font-size: 11px; }
        .low { background: #fee2e2; color: #991b1b; }
        .ok { background: #dcfce7; color: #166534; }
        .muted { color: #555; font-size: 11px; }
    </style>
</head>
<body>
    <div class="title">IT Core Solutions — Daily Report</div>
    <div class="sub">
        Date: <b>{{ $dateYmd }}</b>
        <span class="muted">Generated at: {{ now()->format('Y-m-d H:i') }}</span>
    </div>

    <div class="section">
        <h2>Orders (Grouped by Status)</h2>

        @foreach($ordersByStatus as $status => $list)
            <div style="margin-bottom: 10px;">
                <div style="font-weight: bold; margin-bottom: 4px;">
                    {{ $status }} ({{ $list->count() }})
                </div>

                @if($list->count() === 0)
                    <div class="muted">No orders in this status.</div>
                @else
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 18%;">Order No</th>
                                <th style="width: 18%;">Placed At</th>
                                <th style="width: 18%;">Placed By</th>
                                <th style="width: 32%;">Product</th>
                                <th style="width: 14%;">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($list as $o)
                                @php $item = $o->items->first(); @endphp
                                <tr>
                                    <td>{{ $o->order_no }}</td>
                                    <td>{{ $o->placed_at?->format('Y-m-d H:i') }}</td>
                                    <td>{{ $o->user?->name ?? '-' }}</td>
                                    <td>{{ $item?->product?->name ?? '-' }}</td>
                                    <td>{{ $item?->quantity ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endforeach
    </div>

    <div class="section">
        <h2>Materials Stock</h2>
        <div class="muted" style="margin-bottom: 8px;">
            Low-stock definition: stock <= threshold
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Material</th>
                    <th style="width: 15%;">Stock</th>
                    <th style="width: 15%;">Threshold</th>
                    <th style="width: 10%;">Unit</th>
                    <th style="width: 20%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($materials as $m)
                    @php
                        $isLow = ((float)$m->stock <= (float)$m->threshold);
                    @endphp
                    <tr>
                        <td>{{ $m->name }}</td>
                        <td>{{ $m->stock }}</td>
                        <td>{{ $m->threshold }}</td>
                        <td>{{ $m->unit?->symbol }}</td>
                        <td>
                            @if($isLow)
                                <span class="badge low">LOW</span>
                            @else
                                <span class="badge ok">OK</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($lowMaterials->count() > 0)
            <div class="section">
                <h2>Low Stock Highlight</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Stock</th>
                            <th>Threshold</th>
                            <th>Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowMaterials as $m)
                            <tr>
                                <td>{{ $m->name }}</td>
                                <td>{{ $m->stock }}</td>
                                <td>{{ $m->threshold }}</td>
                                <td>{{ $m->unit?->symbol }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</body>
</html>
