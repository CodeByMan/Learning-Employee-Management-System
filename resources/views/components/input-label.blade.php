@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-sm text-slate-900']) }}>
    {{ $value ?? $slot }}
</label>
