<native:column class="w-full h-full bg-theme-background">
    <native:top-bar title="Geiriau newydd" back />

    @if(! $word)
        <native:column class="w-full flex-1 items-center justify-center p-6">
            <native:text class="text-theme-on-surface-variant">No new words today.</native:text>
        </native:column>
    @else
        <native:column class="w-full flex-1 gap-6 px-5 py-6">
            <native:row class="w-full items-start gap-4">
                <native:text class="shrink-0 text-sm text-theme-on-surface-variant">Gair newydd {{ $index + 1 }} / {{ $words->count() }}</native:text>
                <native:text class="flex-1 text-right text-sm capitalize text-theme-on-surface-variant">{{ $word->level === 'own' ? 'my word' : $word->level }}{{ $word->theme ? " · {$word->theme}" : '' }}</native:text>
            </native:row>

            <native:column class="w-full flex-1 items-center gap-6 rounded-3xl bg-theme-surface p-8 shadow-sm">
                <native:column class="w-full flex-1 items-center justify-center gap-2">
                    <native:text class="text-center text-4xl font-bold leading-tight text-theme-on-surface">{{ $word->cy }}</native:text>

                    @if($word->genderLabel())
                        <native:text class="text-center text-sm font-medium {{ $word->gender === 'f' ? 'text-theme-feminine' : 'text-theme-masculine' }}">{{ $word->genderLabel() }}</native:text>
                    @endif

                    <native:text class="text-center mt-4 text-xl text-theme-on-surface-variant">{{ $word->en }}</native:text>

                    @if($word->note)
                        <native:text class="text-center mt-2 text-sm text-theme-on-surface-variant">{{ $word->note }}</native:text>
                    @endif
                </native:column>

                <native:text class="w-full text-center text-xs text-theme-on-surface-variant">Say it out loud, then use it in a sentence of your own.</native:text>
            </native:column>

            <native:row class="w-full gap-3">
                <native:button variant="secondary" class="flex-1" :disabled="$index === 0" @press="previous">Yn ôl</native:button>

                @if($index < $words->count() - 1)
                    <native:button variant="primary" class="flex-1" @press="next">Nesaf</native:button>
                @else
                    <native:button variant="primary" class="flex-1" @press="practise">Practise now</native:button>
                @endif
            </native:row>
        </native:column>
    @endif
</native:column>
