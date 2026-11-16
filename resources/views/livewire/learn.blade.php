<div class="flex min-h-[70dvh] flex-col">
    @if (! $word)
        <p class="text-stone-500">No new words today.</p>
        <a href="{{ route('home') }}" wire:navigate class="mt-4 text-red-700">Back</a>
    @else
        <div class="mb-6 flex items-center justify-between text-sm text-stone-500">
            <span>Gair newydd {{ $index + 1 }} / {{ $words->count() }}</span>
            <span class="capitalize">{{ $word->level === 'own' ? 'my word' : $word->level }}{{ $word->theme ? ' · '.$word->theme : '' }}</span>
        </div>

        <div class="flex flex-1 flex-col justify-center rounded-3xl bg-white p-8 text-center shadow-sm dark:bg-stone-900">
            <div class="text-4xl font-bold leading-tight">{{ $word->cy }}</div>
            @if ($word->genderLabel())
                <div class="mt-2 text-sm font-medium {{ $word->gender === 'f' ? 'text-rose-600' : 'text-sky-600' }}">{{ $word->genderLabel() }}</div>
            @endif
            <div class="mt-6 text-xl text-stone-600 dark:text-stone-300">{{ $word->en }}</div>
            @if ($word->note)
                <div class="mt-4 text-sm text-stone-500">{{ $word->note }}</div>
            @endif
            <p class="mt-8 text-xs text-stone-400">Say it out loud, then picture yourself using it.</p>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-3">
            <button wire:click="previous" @disabled($index === 0)
                    class="rounded-xl border border-stone-300 py-3 font-medium disabled:opacity-30 dark:border-stone-700">Yn ôl</button>
            @if ($index < $words->count() - 1)
                <button wire:click="next({{ $words->count() }})" class="rounded-xl bg-stone-900 py-3 font-semibold text-white dark:bg-stone-100 dark:text-stone-900">Nesaf</button>
            @else
                <a href="{{ route('study') }}" wire:navigate class="rounded-xl bg-red-700 py-3 text-center font-semibold text-white">Practise now</a>
            @endif
        </div>
    @endif
</div>
