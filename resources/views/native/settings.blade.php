<native:column class="w-full h-full bg-theme-background {{ $onboarded ? '' : 'safe-area' }}">
    <native:column class="w-full gap-1 px-5 pt-6 pb-4">
        <native:text class="text-3xl font-bold tracking-tight text-theme-on-surface">{{ $onboarded ? 'Gosodiadau' : 'Croeso!' }}</native:text>

        @unless($onboarded)
            <native:text class="text-theme-on-surface-variant">A few quick choices and you're ready.</native:text>
        @endunless
    </native:column>

    <native:scroll-view class="w-full flex-1">
        <native:column class="w-full gap-6 px-5 pb-6">
            <native:column class="w-full gap-3 rounded-2xl bg-theme-surface p-5 shadow-sm">
                <native:text class="font-semibold text-theme-on-surface">Eich enw · Your name</native:text>
                <native:text class="text-sm text-theme-on-surface-variant">Optional — used to greet you on the home screen.</native:text>
                <native:outlined-text-input class="w-full" max-length="40" autocapitalize="words" native:model.debounce.300ms="name" />
            </native:column>

            <native:column class="w-full gap-3 rounded-2xl bg-theme-surface p-5 shadow-sm">
                <native:text class="font-semibold text-theme-on-surface">Mynediad words</native:text>
                <native:text class="text-sm text-theme-on-surface-variant">How should the entry-level vocabulary be handled?</native:text>

                <native:radio-group class="w-full" native:model="mynediadMode">
                    @foreach($mynediadOptions as $value => [$title, $description])
                        <native:radio value="{{ $value }}" label="{{ $title }}" />
                    @endforeach
                </native:radio-group>

                <native:text class="text-sm text-theme-on-surface-variant">{{ $mynediadOptions[$mynediadMode][1] }}</native:text>

                @if($onboarded && $mynediadMode !== 'review')
                    <native:text class="text-xs text-theme-on-surface-variant">Switching this only affects Mynediad words you haven't met yet.</native:text>
                @endif
            </native:column>

            <native:column class="w-full gap-3 rounded-2xl bg-theme-surface p-5 shadow-sm">
                <native:row class="w-full items-center justify-between">
                    <native:text class="font-semibold text-theme-on-surface">New words per day</native:text>
                    <native:text class="text-2xl font-bold text-theme-primary">{{ $newPerDay }}</native:text>
                </native:row>

                <native:slider class="w-full" :min="1" :max="20" :step="1" native:model="newPerDay" />

                <native:text class="text-xs text-theme-on-surface-variant">Each new word adds roughly 6–8 reviews over the following month. 5 a day settles at about 20–30 cards a day.</native:text>
            </native:column>

            <native:column class="w-full gap-3 rounded-2xl bg-theme-surface p-5 shadow-sm">
                <native:toggle class="w-full" label="Daily reminder" native:model="reminderEnabled" />
                <native:text class="text-sm text-theme-on-surface-variant">Same time every day, to build a habit. Skipped once you've finished for the day.</native:text>

                @if($reminderEnabled)
                    <native:row class="w-full items-center justify-between">
                        <native:text class="text-theme-on-surface">Remind me at</native:text>
                        <native:date-picker mode="time" native:model="reminderTime" />
                    </native:row>

                    <native:text class="text-xs text-theme-on-surface-variant">Tip: tie it to something you already do every day, e.g. with your morning paned.</native:text>
                @endif
            </native:column>

            <native:button variant="primary" size="lg" class="w-full" @press="save">{{ $onboarded ? 'Cadw' : 'Dechrau' }}</native:button>

            @if($saved)
                <native:text class="w-full text-center text-sm text-theme-success">Wedi cadw ✓</native:text>
            @endif

            @if($onboarded)
                <native:column class="w-full gap-3 rounded-2xl border border-theme-destructive/40 p-5">
                    <native:text class="font-semibold text-theme-destructive">Start again</native:text>
                    <native:text class="text-sm text-theme-on-surface-variant">Wipes all progress. Your own added words are kept.</native:text>
                    <native:button variant="destructive" class="w-full" @press="confirmReset">Reset progress</native:button>
                </native:column>
            @endif
        </native:column>
    </native:scroll-view>
</native:column>
