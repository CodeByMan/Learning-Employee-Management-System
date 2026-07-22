@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-[18px] border border-slate-200 bg-white/95 px-4 py-3 text-slate-900 shadow-sm focus:border-green-500 focus:ring-green-500']) }}>
