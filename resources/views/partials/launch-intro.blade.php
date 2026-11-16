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

        try {
            if (sessionStorage.getItem('launchIntroShown')) {
                return;
            }

            sessionStorage.setItem('launchIntroShown', '1');
        } catch (error) {
            return;
        }

        intro.classList.remove('hidden');

        const dismiss = () => {
            intro.classList.add('opacity-0', 'pointer-events-none');
            setTimeout(() => intro.remove(), 500);
        };

        intro.addEventListener('click', dismiss, { once: true });
        setTimeout(dismiss, 1200);
    })();
</script>
