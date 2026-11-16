<div id="launch-intro"
     class="fixed inset-0 z-50 hidden flex-col items-center justify-center bg-stone-50 transition-opacity duration-500 motion-reduce:transition-none dark:bg-stone-950">
    <p class="text-5xl font-bold tracking-tight text-red-700 dark:text-red-400">Geiriau</p>
    <p class="mt-3 text-lg font-medium text-stone-700 dark:text-stone-300">Pum gair y dydd</p>
    <p class="text-sm text-stone-500 dark:text-stone-400">Five words a day</p>
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

        intro.classList.replace('hidden', 'flex');

        const dismiss = () => {
            intro.classList.add('opacity-0', 'pointer-events-none');
            setTimeout(() => intro.remove(), 500);
        };

        intro.addEventListener('click', dismiss, { once: true });
        setTimeout(dismiss, 1200);
    })();
</script>
