<div class="space-y-6">
    <header>
        <p class="text-sm text-stone-500">{{ now(config('cymraeg.timezone'))->locale('cy')->translatedFormat('l j F') }}</p>
        <h1 class="text-3xl font-bold tracking-tight">Shwmae, Simon!</h1>
    </header>

    <section class="grid grid-cols-3 gap-3 text-center">
        <div class="rounded-2xl bg-white p-4 shadow-sm dark:bg-stone-900">
            <div class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $stats['due'] }}</div>
            <div class="text-xs text-stone-500">i'w hymarfer<br>to practise</div>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm dark:bg-stone-900">
            <div class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ $stats['known'] }}</div>
            <div class="text-xs text-stone-500">geiriau<br>words known</div>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm dark:bg-stone-900">
            <div class="text-2xl font-bold">{{ $stats['streak'] }}</div>
            <div class="text-xs text-stone-500">diwrnod<br>day streak</div>
        </div>
    </section>

    <section class="rounded-2xl bg-white p-5 shadow-sm dark:bg-stone-900">
        <div class="mb-3 flex items-baseline justify-between">
            <h2 class="font-semibold">Geiriau newydd heddiw</h2>
            <span class="text-xs text-stone-500">today's new words</span>
        </div>

        @if ($heldBack)
            <p class="text-sm text-stone-600 dark:text-stone-400">
                No new words today — clear the reviews that are waiting first, so the pile doesn't grow.
            </p>
        @elseif ($newWords->isEmpty())
            <p class="text-sm text-stone-600 dark:text-stone-400">You've worked through the whole word bank. Add your own words from class in Geiriau.</p>
        @else
            <ul class="mb-4 flex flex-wrap gap-2">
                @foreach ($newWords as $w)
                    <li class="rounded-full bg-stone-100 px-3 py-1 text-sm dark:bg-stone-800">{{ $w->cy }}</li>
                @endforeach
            </ul>
            @if ($unseenNew > 0)
                <a href="{{ route('learn') }}" wire:navigate
                   class="block rounded-xl border border-red-700 py-3 text-center font-semibold text-red-700 dark:border-red-400 dark:text-red-400">
                    Meet today's words
                </a>
            @endif
        @endif
    </section>

    @if ($stats['due'] > 0)
        <a href="{{ route('study') }}" wire:navigate
           class="block rounded-2xl bg-red-700 py-4 text-center text-lg font-semibold text-white shadow active:bg-red-800">
            Dechrau ymarfer · {{ $stats['due'] }}
        </a>
    @else
        <div class="rounded-2xl bg-emerald-50 p-4 text-center text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
            Da iawn! Nothing due right now.
        </div>
    @endif

    <section class="rounded-2xl bg-white p-5 shadow-sm dark:bg-stone-900">
        <h2 class="mb-3 text-sm font-semibold text-stone-500">Next 7 days</h2>
        @php $max = max(1, max($stats['forecast'])); @endphp
        <div class="flex h-20 items-end gap-2">
            @foreach ($stats['forecast'] as $i => $n)
                <div class="flex flex-1 flex-col items-center gap-1">
                    <div class="w-full rounded-t bg-red-700/70 dark:bg-red-400/70" style="height: {{ max(2, $n / $max * 64) }}px"></div>
                    <span class="text-[10px] text-stone-500">{{ $n }}</span>
                </div>
            @endforeach
        </div>
        <p class="mt-3 text-xs text-stone-500">
            {{ $stats['mature'] }} words solid (3+ weeks) · {{ $stats['remaining'] }} still to come
        </p>
    </section>
</div>
