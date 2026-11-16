<div class="space-y-6">
    <header>
        <h1 class="text-2xl font-bold">{{ $onboarded ? 'Gosodiadau' : 'Croeso!' }}</h1>
        @unless ($onboarded)
            <p class="mt-1 text-stone-500">A few quick choices and you're ready.</p>
        @endunless
    </header>

    <form wire:submit="save" class="space-y-6">
        <section class="rounded-2xl bg-white p-5 shadow-sm dark:bg-stone-900">
            <h2 class="font-semibold">Mynediad words</h2>
            <p class="mb-3 text-sm text-stone-500">How should the entry-level vocabulary be handled?</p>
            @foreach ([
                'review' => ['I know them — keep them fresh', 'Mixed into your reviews, about 10 a day. Any you\'ve forgotten get re-taught.'],
                'learn' => ['Teach them again', 'Mynediad words come first in your 5 new a day, then Sylfaen.'],
                'off' => ['Leave them out', 'Sylfaen words only.'],
            ] as $value => [$title, $desc])
                <label class="mb-2 flex gap-3 rounded-xl border p-3 {{ $mynediadMode === $value ? 'border-red-700 bg-red-50 dark:bg-red-950/30' : 'border-stone-200 dark:border-stone-700' }}">
                    <input type="radio" wire:model.live="mynediadMode" value="{{ $value }}" class="mt-1 accent-red-700">
                    <span><span class="block font-medium">{{ $title }}</span><span class="text-sm text-stone-500">{{ $desc }}</span></span>
                </label>
            @endforeach
            @if ($onboarded && $mynediadMode !== 'review')
                <p class="text-xs text-stone-500">Switching this only affects Mynediad words you haven't met yet.</p>
            @endif
        </section>

        <section class="rounded-2xl bg-white p-5 shadow-sm dark:bg-stone-900">
            <div class="flex items-baseline justify-between">
                <h2 class="font-semibold">New words per day</h2>
                <span class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $newPerDay }}</span>
            </div>
            <input type="range" min="1" max="20" wire:model.live="newPerDay" class="mt-3 w-full accent-red-700">
            <p class="mt-2 text-xs text-stone-500">Each new word adds roughly 6–8 reviews over the following month. 5 a day settles at about 20–30 cards a day.</p>
        </section>

        <section class="rounded-2xl bg-white p-5 shadow-sm dark:bg-stone-900">
            <label class="flex items-center justify-between gap-4">
                <span>
                    <span class="block font-semibold">Daily reminder</span>
                    <span class="text-sm text-stone-500">Same time every day, to build a habit. Skipped once you've finished for the day.</span>
                </span>
                <input type="checkbox" wire:model.live="reminderEnabled" class="h-6 w-6 shrink-0 accent-red-700">
            </label>
            @if ($reminderEnabled)
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-sm">Remind me at</span>
                    <input type="time" wire:model="reminderTime"
                           class="rounded-lg border border-stone-300 px-3 py-2 dark:border-stone-700 dark:bg-stone-950">
                </div>
                @error('reminderTime') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                <p class="mt-2 text-xs text-stone-500">Tip: tie it to something you already do every day, e.g. with your morning paned.</p>
            @endif
        </section>

        <button class="w-full rounded-2xl bg-red-700 py-4 text-lg font-semibold text-white">{{ $onboarded ? 'Cadw' : 'Dechrau' }}</button>
        @if ($saved) <p class="text-center text-sm text-emerald-700">Wedi cadw ✓</p> @endif
    </form>

    @if ($onboarded)
        <section class="rounded-2xl border border-rose-200 p-5 dark:border-rose-900">
            <h2 class="font-semibold text-rose-700">Start again</h2>
            <p class="mb-3 text-sm text-stone-500">Wipes all progress. Your own added words are kept.</p>
            <button wire:click="wipeProgress" class="w-full rounded-xl py-2.5 font-semibold {{ $confirmingReset ? 'bg-rose-600 text-white' : 'border border-rose-300 text-rose-700' }}">
                {{ $confirmingReset ? 'Tap again to confirm' : 'Reset progress' }}
            </button>
        </section>
    @endif
</div>
