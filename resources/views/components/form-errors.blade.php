@if($errors->any())
    <div {{ $attributes->merge(['class' => 'alert alert-error mb-5']) }} role="alert">
        <x-icon name="warning" class="h-5 w-5 shrink-0" />
        <div>
            <div class="font-medium">Please fix the following:</div>
            <ul class="mt-1 list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
