<div class="flex min-h-[75dvh] flex-col">
    @if (! $card)
        <div class="flex flex-1 flex-col items-center justify-center text-center">
            <div class="text-5xl">🏴󠁧󠁢󠁷󠁬󠁳󠁿</div>
            <h1 class="mt-4 text-2xl font-bold">Gwych!</h1>
            <p class="mt-2 text-stone-500">
                {{ $doneThisSession > 0 ? "That's everything for now — {$doneThisSession} answered." : 'Nothing due right now.' }}
            </p>
            <a href="{{ route('home') }}" wire:navigate class="mt-6 rounded-xl bg-stone-900 px-6 py-3 font-semibold text-white dark:bg-stone-100 dark:text-stone-900">Adref</a>
        </div>
    @else
        @php $produce = $card->direction === \App\Models\Card::PRODUCE; @endphp

        <div class="mb-4 flex items-center justify-between text-xs text-stone-500">
            <span>{{ $remaining }} left</span>
            <span>
                {{ $produce ? 'English → Cymraeg' : 'Cymraeg → English' }}
                @if (in_array($card->state, ['new', 'learning'])) · <span class="text-amber-600">newydd</span>@endif
                @if ($card->state === 'relearning') · <span class="text-rose-600">eto</span>@endif
            </span>
        </div>

        <div class="flex flex-1 flex-col justify-center rounded-3xl bg-white p-8 text-center shadow-sm dark:bg-stone-900"
             @if (! $revealed && ! $produce) wire:click="reveal" @endif>
            {{-- Prompt --}}
            <div class="text-4xl font-bold leading-tight">{{ $produce ? $word->en : $word->cy }}</div>
            @if ($produce && $word->genderLabel())
                <div class="mt-2 text-xs text-stone-400">(noun)</div>
            @endif

            {{-- Answer --}}
            @if ($revealed)
                <div class="mx-auto my-6 h-px w-16 bg-stone-200 dark:bg-stone-700"></div>
                <div class="text-2xl {{ $produce ? 'font-bold' : 'text-stone-600 dark:text-stone-300' }}">{{ $produce ? $word->cy : $word->en }}</div>
                @if ($word->genderLabel())
                    <div class="mt-1 text-sm font-medium {{ $word->gender === 'f' ? 'text-rose-600' : 'text-sky-600' }}">{{ $word->genderLabel() }}</div>
                @endif
                @if ($verdict)
                    <div class="mt-3 text-sm font-medium {{ ['exact' => 'text-emerald-600', 'close' => 'text-amber-600', 'wrong' => 'text-rose-600'][$verdict] }}">
                        {{ ['exact' => '✓ Cywir — spot on', 'close' => '≈ Close — check the accents', 'wrong' => "✗ You wrote “{$attempt}”"][$verdict] }}
                    </div>
                @endif
                @if ($word->note)
                    <div class="mt-4 text-sm text-stone-500">{{ $word->note }}</div>
                @endif
            @elseif (! $produce)
                <p class="mt-10 text-xs text-stone-400">Think of the meaning, then tap</p>
            @endif
        </div>

        <div class="mt-5">
            @if (! $revealed)
                @if ($produce)
                    <form wire:submit="reveal" class="space-y-3">
                        <input type="text" wire:model="attempt" placeholder="Ysgrifennwch yn Gymraeg… (optional)"
                               autocapitalize="off" autocomplete="off" autocorrect="off" spellcheck="false"
                               class="w-full select-text rounded-xl border border-stone-300 bg-white px-4 py-3 text-lg dark:border-stone-700 dark:bg-stone-900">
                        <button class="w-full rounded-xl bg-stone-900 py-4 font-semibold text-white dark:bg-stone-100 dark:text-stone-900">Dangos yr ateb</button>
                    </form>
                @else
                    <button wire:click="reveal" class="w-full rounded-xl bg-stone-900 py-4 font-semibold text-white dark:bg-stone-100 dark:text-stone-900">Dangos yr ateb</button>
                @endif
            @else
                <p class="mb-2 text-center text-xs text-stone-500">How well did you remember it?</p>
                <div class="grid grid-cols-4 gap-2">
                    @foreach ($grades as $g)
                        @php
                            $style = [1 => 'bg-rose-600', 2 => 'bg-amber-500', 3 => 'bg-emerald-600', 4 => 'bg-sky-600'][$g->value];
                        @endphp
                        <button wire:click="grade({{ $g->value }})" wire:loading.attr="disabled"
                                class="{{ $style }} flex flex-col items-center rounded-xl py-3 text-white active:opacity-80">
                            <span class="font-semibold">{{ $g->label() }}</span>
                            <span class="text-[11px] opacity-80">{{ $previews[$g->value] ?? '' }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
