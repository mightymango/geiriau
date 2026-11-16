<div id="launch-intro"
     class="fixed inset-0 z-50 hidden bg-[#C8102E] transition-opacity duration-500 motion-reduce:transition-none dark:bg-[#15100F]">
    <picture>
        <source srcset="{{ asset('splash-dark.png') }}" media="(prefers-color-scheme: dark)">
        <img src="{{ asset('splash.png') }}" alt="Geiriau — Pum gair y dydd" class="h-full w-full object-cover">
    </picture>
</div>

<script>
    (() => {
        const intro = document.getElementById('launch-intro');

        if (window.launchIntroShown) {
            intro.remove();

            return;
        }

        window.launchIntroShown = true;
        intro.classList.remove('hidden');

        const dismiss = () => {
            intro.classList.add('opacity-0', 'pointer-events-none');
            setTimeout(() => intro.remove(), 500);
        };

        const holdAfterNativeSplash = () => setTimeout(dismiss, 1200);

        intro.addEventListener('click', dismiss, { once: true });

        document.readyState === 'complete'
            ? holdAfterNativeSplash()
            : window.addEventListener('load', holdAfterNativeSplash, { once: true });
    })();
</script>
