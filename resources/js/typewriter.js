document.addEventListener('DOMContentLoaded', () => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.querySelectorAll('[data-typewriter]').forEach((el) => {
        const text = el.dataset.typewriter ?? '';
        const speed = parseInt(el.dataset.typewriterSpeed ?? '65', 10);

        if (prefersReducedMotion || ! text) {
            el.textContent = text;
            return;
        }

        el.textContent = '';

        let i = 0;
        const type = () => {
            el.textContent = text.slice(0, i);
            i++;

            if (i <= text.length) {
                setTimeout(type, speed);
            }
        };

        type();
    });
});
