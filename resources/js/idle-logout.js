document.addEventListener('DOMContentLoaded', () => {
    const meta = document.querySelector('meta[name="idle-logout-minutes"]');
    const form = document.getElementById('idle-logout-form');

    if (! meta || ! form) {
        return;
    }

    const timeoutMs = parseFloat(meta.content) * 60 * 1000;

    if (! timeoutMs || timeoutMs <= 0) {
        return;
    }

    let timer;

    const resetTimer = () => {
        clearTimeout(timer);
        timer = setTimeout(() => form.submit(), timeoutMs);
    };

    ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart'].forEach((event) => {
        document.addEventListener(event, resetTimer, { passive: true });
    });

    resetTimer();
});
