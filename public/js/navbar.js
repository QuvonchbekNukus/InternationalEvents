document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-topbar]').forEach((topbar) => {
        const notificationButton = topbar.querySelector('[data-topbar-notification]');
        let notificationTimer = null;

        notificationButton?.addEventListener('click', () => {
            notificationButton.classList.remove('is-active');

            if (notificationTimer) {
                window.clearTimeout(notificationTimer);
            }

            window.requestAnimationFrame(() => {
                notificationButton.classList.add('is-active');
            });

            notificationTimer = window.setTimeout(() => {
                notificationButton.classList.remove('is-active');
            }, 700);
        });
    });
});
