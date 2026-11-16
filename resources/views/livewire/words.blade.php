<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Geiriau</h1>
        <button wire:click="$toggle('adding')" class="rounded-full bg-red-700 px-4 py-1.5 text-sm font-semibold text-white">
            {{ $adding ? 'Close' : '+ Add word' }}
        </button>
    </div>

    @if ($adding)
        <form wire:submit="save" class="space-y-3 rounded-2xl bg-white p-4 shadow-sm dark:bg-stone-900">
            <p class="text-xs text-stone-500">Add a word from class or from your coursebook.</p>
            <input wire:model="cy" placeholder="Cymraeg" autocapitalize="off" class="w-full select-text rounded-lg border border-stone-300 px-3 py-2 dark:border-stone-700 dark:bg-stone-950">
            @error('cy') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
            <input wire:model="en" placeholder="English" class="w-full select-text rounded-lg border border-stone-300 px-3 py-2 dark:border-stone-700 dark:bg-stone-950">
            @error('en') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
            <div class="flex gap-2 text-sm">
                @foreach (['' => 'Not a noun', 'm' => 'eg (masc.)', 'f' => 'eb (fem.)'] as $value => $label)
                    <label class="flex-1 rounded-lg border px-2 py-2 text-center {{ (string) $gender === (string) $value ? 'border-red-700 text-red-700' : 'border-stone-300 dark:border-stone-700' }}">
                        <input type="radio" wire:model.live="gender" value="{{ $value }}" class="hidden"> {{ $label }}
                    </label>
                @endforeach
            </div>
            <input wire:model="note" placeholder="Note (optional), e.g. South Wales form" class="w-full select-text rounded-lg border border-stone-300 px-3 py-2 dark:border-stone-700 dark:bg-stone-950">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" wire:model="learnToday" class="accent-red-700"> Start learning it today (on top of the daily 5)
            </label>
            <button class="w-full rounded-lg bg-stone-900 py-2.5 font-semibold text-white dark:bg-stone-100 dark:text-stone-900">Cadw</button>
        </form>
    @endif

    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Chwilio… / Search"
           class="w-full select-text rounded-xl border border-stone-300 bg-white px-4 py-2.5 dark:border-stone-700 dark:bg-stone-900">

    <div class="flex gap-2 overflow-x-auto text-sm">
        @foreach (['all' => 'All', 'mynediad' => 'Mynediad', 'sylfaen' => 'Sylfaen', 'own' => 'My words'] as $value => $label)
            <button wire:click="$set('level', '{{ $value }}')"
                    class="whitespace-nowrap rounded-full px-3 py-1 {{ $level === $value ? 'bg-stone-900 text-white dark:bg-stone-100 dark:text-stone-900' : 'bg-stone-200 dark:bg-stone-800' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <ul class="divide-y divide-stone-200 rounded-2xl bg-white shadow-sm dark:divide-stone-800 dark:bg-stone-900">
        @forelse ($words as $w)
            @php
                $card = $w->recognise;
                [$label, $colour] = match (true) {
                    ! $card || ! $card->active => ['to come', 'bg-stone-100 text-stone-500 dark:bg-stone-800'],
                    $card->state !== 'review' => ['learning', 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'],
                    $card->interval >= 21 => ['solid', 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'],
                    default => [$card->interval.'d', 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300'],
                };
            @endphp
            <li class="flex items-center justify-between gap-3 px-4 py-3">
                <div class="min-w-0">
                    <div class="font-medium">{{ $w->cy }}
                        @if ($w->genderLabel()) <span class="text-xs {{ $w->gender === 'f' ? 'text-rose-600' : 'text-sky-600' }}">{{ $w->genderLabel() }}</span> @endif
                    </div>
                    <div class="truncate text-sm text-stone-500">{{ $w->en }}</div>
                </div>
                <span class="shrink-0 rounded-full px-2 py-0.5 text-xs {{ $colour }}">{{ $label }}</span>
            </li>
        @empty
            <li class="px-4 py-6 text-center text-sm text-stone-500">Dim geiriau — no words found.</li>
        @endforelse
    </ul>
</div>
