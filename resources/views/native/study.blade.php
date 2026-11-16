<native:column class="w-full h-full bg-theme-background">
    @if(! $card)
        <native:column class="w-full flex-1 items-center justify-center gap-3 p-6">
            <native:icon ios="checkmark.seal.fill" android="verified" :size="56" class="text-theme-success" />
            <native:text class="text-center text-2xl font-bold text-theme-on-surface">Gwych!</native:text>
            <native:text class="text-center text-theme-on-surface-variant">{{ $doneThisSession > 0 ? "That's everything for now — {$doneThisSession} answered." : 'Nothing due right now.' }}</native:text>

            @if($canLearnMore)
                <native:text class="mt-4 text-center text-sm text-theme-on-surface-variant">Hawdd? · Found that easy?</native:text>
                <native:button variant="success" class="w-full" @press="learnMore">Mwy o eiriau · {{ $newPerDay }} more words</native:button>
            @endif

            <native:button variant="secondary" class="w-full" @press="goHome">Adref</native:button>
        </native:column>
    @else
        <native:column class="w-full flex-1 gap-4 px-5 py-6">
            <native:row class="w-full items-start gap-4">
                <native:text class="shrink-0 text-xs text-theme-on-surface-variant">{{ $remaining }} left</native:text>
                <native:row class="flex-1 justify-end gap-1">
                    <native:text class="text-xs text-theme-on-surface-variant">{{ $produce ? 'English → Cymraeg' : 'Cymraeg → English' }}</native:text>

                    @if(in_array($card->state, ['new', 'learning']))
                        <native:text class="text-xs text-theme-accent">· newydd</native:text>
                    @endif

                    @if($card->state === 'relearning')
                        <native:text class="text-xs text-theme-destructive">· eto</native:text>
                    @endif
                </native:row>
            </native:row>

            <native:pressable class="w-full flex-1 rounded-3xl bg-theme-surface p-8 shadow-sm" @press="reveal" :disabled="$revealed || $produce">
                <native:column class="w-full h-full items-center justify-center gap-2">
                    <native:text class="text-center text-4xl font-bold leading-tight text-theme-on-surface">{{ $produce ? $word->en : $word->cy }}</native:text>

                    @if($produce && $word->genderLabel())
                        <native:text class="text-center text-xs text-theme-on-surface-variant">(noun)</native:text>
                    @endif

                    @if($revealed)
                        <native:divider class="my-4 w-[64]" />

                        <native:text class="text-center text-2xl {{ $produce ? 'font-bold text-theme-on-surface' : 'text-theme-on-surface-variant' }}">{{ $produce ? $word->cy : $word->en }}</native:text>

                        @if($word->genderLabel())
                            <native:text class="text-center text-sm font-medium {{ $word->gender === 'f' ? 'text-theme-feminine' : 'text-theme-masculine' }}">{{ $word->genderLabel() }}</native:text>
                        @endif

                        @if($verdict === 'exact')
                            <native:text class="mt-2 text-center text-sm font-medium text-theme-success">✓ Cywir — spot on</native:text>
                        @elseif($verdict === 'close')
                            <native:text class="mt-2 text-center text-sm font-medium text-theme-accent">≈ Close — check the accents</native:text>
                        @elseif($verdict === 'wrong')
                            <native:text class="mt-2 text-center text-sm font-medium text-theme-destructive">✗ You wrote “{{ $attempt }}”</native:text>
                        @endif

                        @if($word->note)
                            <native:text class="mt-2 text-center text-sm text-theme-on-surface-variant">{{ $word->note }}</native:text>
                        @endif
                    @elseif(! $produce)
                        <native:text class="mt-8 text-center text-xs text-theme-on-surface-variant">Think of the meaning, then tap</native:text>
                    @endif
                </native:column>
            </native:pressable>

            @if(! $revealed)
                @if($produce)
                    <native:outlined-text-input class="w-full" placeholder="Ysgrifennwch yn Gymraeg… (optional)"
                        autocapitalize="none" native:model.debounce.300ms="attempt" @submit="reveal" />
                @endif

                <native:button variant="primary" size="lg" class="w-full" @press="reveal">Dangos yr ateb</native:button>
            @else
                <native:text class="w-full text-center text-xs text-theme-on-surface-variant">How well did you remember it?</native:text>

                <native:row class="w-full gap-2">
                    @foreach($grades as $grade)
                        <native:pressable native:key="grade-{{ $grade->value }}" class="flex-1 rounded-xl bg-theme-{{ strtolower($grade->name) }} py-3" @press="grade({{ $grade->value }})">
                            <native:column class="w-full items-center gap-1">
                                <native:text class="text-center font-semibold text-theme-on-{{ strtolower($grade->name) }}">{{ $grade->label() }}</native:text>
                                <native:text class="text-center text-xs text-theme-on-{{ strtolower($grade->name) }}">{{ $previews[$grade->value] ?? '' }}</native:text>
                            </native:column>
                        </native:pressable>
                    @endforeach
                </native:row>
            @endif
        </native:column>
    @endif
</native:column>
