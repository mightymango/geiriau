<native:column class="w-full h-full bg-theme-background">
    <native:top-bar title="Geiriau">
        <native:top-bar-action id="add-word" label="Add word" ios-icon="plus" android-icon="add" @tap="startAdding" />
    </native:top-bar>

    <native:column class="w-full gap-3 px-5 pt-4">
        <native:outlined-text-input class="w-full" placeholder="Chwilio… / Search" leading-icon="magnifyingglass"
            autocapitalize="none" native:model.debounce.300ms="search" />

        <native:button-group class="w-full" :options="['All', 'Mynediad', 'Sylfaen', 'My words']" native:model="levelIndex" />
    </native:column>

    @if($words->isEmpty())
        <native:column class="w-full flex-1 items-center justify-center p-6">
            <native:text class="text-center text-sm text-theme-on-surface-variant">Dim geiriau — no words found.</native:text>
        </native:column>
    @else
        <native:list class="w-full flex-1" separator>
            @foreach($words as $word)
                @php
                    $card = $word->recognise;
                    $progress = match (true) {
                        ! $card || ! $card->active => 'to come',
                        $card->state !== 'review' => 'learning',
                        $card->interval >= 21 => 'solid',
                        default => "{$card->interval}d",
                    };
                    $meaning = $word->genderLabel() ? "{$word->en} · {$word->genderLabel()}" : $word->en;
                @endphp
                <native:list-item native:key="word-{{ $word->id }}"
                    :headline="$word->cy"
                    :supporting="$meaning"
                    :trailingText="$progress" />
            @endforeach
        </native:list>
    @endif

    <native:bottom-sheet :visible="$adding" @dismiss="stopAdding" title="Add a word" detents="large">
        <native:scroll-view class="w-full">
            <native:column class="w-full gap-4 p-5">
                <native:text class="text-sm text-theme-on-surface-variant">Add a word from class or from your coursebook.</native:text>

                <native:outlined-text-input class="w-full" label="Cymraeg" autocapitalize="none" native:model.debounce.300ms="cy" />
                <native:outlined-text-input class="w-full" label="English" native:model.debounce.300ms="en" />

                <native:button-group class="w-full" :options="['Not a noun', 'eg (masc.)', 'eb (fem.)']" native:model="genderIndex" />

                <native:outlined-text-input class="w-full" label="Note (optional)" placeholder="e.g. South Wales form" native:model.debounce.300ms="note" />

                <native:toggle class="w-full" label="Start learning it today (on top of the daily 5)" native:model="learnToday" />

                <native:button variant="primary" size="lg" class="w-full" @press="save">Cadw</native:button>
            </native:column>
        </native:scroll-view>
    </native:bottom-sheet>
</native:column>
