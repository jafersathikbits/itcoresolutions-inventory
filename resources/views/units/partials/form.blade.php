@php $unit = $unit ?? null; @endphp
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label for="name" class="form-label">Name <span class="text-red-500">*</span></label>
        <input id="name" name="name" value="{{ old('name', $unit?->name) }}" class="form-input" placeholder="e.g. Square feet" maxlength="100" required autofocus>
    </div>
    <div>
        <label for="symbol" class="form-label">Symbol <span class="text-red-500">*</span></label>
        <input id="symbol" name="symbol" value="{{ old('symbol', $unit?->symbol) }}" class="form-input" placeholder="e.g. sqft" maxlength="30" required>
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-4 hover:bg-slate-50 cursor-pointer">
            <input id="allow_decimal" name="allow_decimal" type="checkbox" value="1" class="form-checkbox mt-0.5" @checked(old('allow_decimal', $unit?->allow_decimal))>
            <span>
                <span class="block text-sm font-medium text-slate-800">Allow decimal quantities</span>
                <span class="block text-xs text-slate-500">Enable for measures like kg, ft or sqft. Leave off for countable items (nos).</span>
            </span>
        </label>
    </div>
</div>
