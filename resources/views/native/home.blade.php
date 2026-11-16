<native:scroll-view class="w-full h-full bg-theme-background">
    <native:column class="w-full gap-6 px-5 py-6">
        <native:column class="gap-1">
            <native:text class="text-sm text-theme-on-surface-variant">{{ $today }}</native:text>
            <native:text class="text-3xl font-bold tracking-tight text-theme-on-surface">Shwmae{{ $name ? ", {$name}" : '' }}!</native:text>
        </native:column>

        <native:row class="w-full gap-3">
            <native:column class="flex-1 items-center gap-1 rounded-2xl bg-theme-surface p-4 shadow-sm">
                <native:text class="text-2xl font-bold text-theme-primary">{{ $stats['due'] }}</native:text>
                <native:text class="text-xs text-theme-on-surface-variant">i'w hymarfer</native:text>
                <native:text class="text-xs text-theme-on-surface-variant">to practise</native:text>
            </native:column>
            <native:column class="flex-1 items-center gap-1 rounded-2xl bg-theme-surface p-4 shadow-sm">
                <native:text class="text-2xl font-bold text-theme-success">{{ $stats['known'] }}</native:text>
                <native:text class="text-xs text-theme-on-surface-variant">geiriau</native:text>
                <native:text class="text-xs text-theme-on-surface-variant">words known</native:text>
            </native:column>
            <native:column class="flex-1 items-center gap-1 rounded-2xl bg-theme-surface p-4 shadow-sm">
                <native:text class="text-2xl font-bold text-theme-on-surface">{{ $stats['streak'] }}</native:text>
                <native:text class="text-xs text-theme-on-surface-variant">diwrnod</native:text>
                <native:text class="text-xs text-theme-on-surface-variant">day streak</native:text>
            </native:column>
        </native:row>

        <native:column class="w-full gap-3 rounded-2xl bg-theme-surface p-5 shadow-sm">
            <native:row class="w-full items-center justify-between">
                <native:text class="font-semibold text-theme-on-surface">Geiriau newydd heddiw</native:text>
                <native:text class="text-xs text-theme-on-surface-variant">today's new words</native:text>
            </native:row>

            @if($heldBack)
                <native:text class="text-sm text-theme-on-surface-variant">No new words today — clear the reviews that are waiting first, so the pile doesn't grow.</native:text>
            @elseif($newWords->isEmpty())
                <native:text class="text-sm text-theme-on-surface-variant">You've worked through the whole word bank. Add your own words from class in Geiriau.</native:text>
            @else
                <native:row class="w-full flex-wrap gap-2">
                    @foreach($newWords as $word)
                        <native:text native:key="word-{{ $word->id }}" class="rounded-full bg-theme-surface-variant px-3 py-1 text-sm text-theme-on-surface">{{ $word->cy }}</native:text>
                    @endforeach
                </native:row>

                @if($unseenNew > 0)
                    <native:button variant="secondary" class="w-full" @press="meetWords">Meet today's words</native:button>
                @endif
            @endif
        </native:column>

        @if($stats['due'] > 0)
            <native:button variant="primary" size="lg" class="w-full" @press="practise">Dechrau ymarfer · {{ $stats['due'] }}</native:button>
        @else
            <native:column class="w-full items-center gap-3 rounded-2xl bg-theme-success/15 p-4">
                <native:text class="text-theme-success">Da iawn! Nothing due right now.</native:text>

                @if($canLearnMore)
                    <native:button variant="success" class="w-full" @press="learnMore">Mwy o eiriau · {{ $newPerDay }} more words</native:button>
                @endif
            </native:column>
        @endif

        <native:column class="w-full gap-3 rounded-2xl bg-theme-surface p-5 shadow-sm">
            <native:text class="text-sm font-semibold text-theme-on-surface-variant">Next 7 days</native:text>

            <native:row class="w-full h-[84] items-end gap-2">
                @foreach($stats['forecast'] as $day => $count)
                    <native:column native:key="forecast-{{ $day }}" class="flex-1 items-center gap-1">
                        <native:column class="w-full h-[{{ max(2, (int) round($count / $busiestDay * 64)) }}] rounded-t bg-theme-primary/70" />
                        <native:text class="text-xs text-theme-on-surface-variant">{{ $count }}</native:text>
                    </native:column>
                @endforeach
            </native:row>

            <native:text class="text-xs text-theme-on-surface-variant">{{ $stats['mature'] }} words solid (3+ weeks) · {{ $stats['remaining'] }} still to come</native:text>
        </native:column>
    </native:column>
</native:scroll-view>
