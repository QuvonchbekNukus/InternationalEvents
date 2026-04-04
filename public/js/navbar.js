document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-topbar]').forEach((topbar) => {
        topbar.querySelectorAll('[data-notification-dropdown]').forEach((wrap) => {
            const trigger = wrap.querySelector('[data-notification-dropdown-trigger]');
            const panel = wrap.querySelector('[data-notification-dropdown-panel]');

            if (!trigger || !panel) {
                return;
            }

            let bellTimer = null;

            const closePanel = () => {
                wrap.classList.remove('is-open');
                panel.hidden = true;
                trigger.setAttribute('aria-expanded', 'false');
            };

            const openPanel = () => {
                document.querySelectorAll('[data-notification-dropdown].is-open').forEach((other) => {
                    if (other === wrap) {
                        return;
                    }
                    const otherTrigger = other.querySelector('[data-notification-dropdown-trigger]');
                    const otherPanel = other.querySelector('[data-notification-dropdown-panel]');
                    other.classList.remove('is-open');
                    if (otherPanel) {
                        otherPanel.hidden = true;
                    }
                    if (otherTrigger) {
                        otherTrigger.setAttribute('aria-expanded', 'false');
                    }
                });

                wrap.classList.add('is-open');
                panel.hidden = false;
                trigger.setAttribute('aria-expanded', 'true');

                trigger.classList.remove('is-active');
                if (bellTimer) {
                    window.clearTimeout(bellTimer);
                }
                window.requestAnimationFrame(() => {
                    trigger.classList.add('is-active');
                });
                bellTimer = window.setTimeout(() => {
                    trigger.classList.remove('is-active');
                }, 700);
            };

            const togglePanel = (event) => {
                event.stopPropagation();
                const willOpen = !wrap.classList.contains('is-open');
                if (willOpen) {
                    openPanel();
                } else {
                    closePanel();
                }
            };

            trigger.addEventListener('click', togglePanel);

            panel.addEventListener('click', (event) => {
                event.stopPropagation();
            });
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('[data-notification-dropdown].is-open').forEach((wrap) => {
            const trigger = wrap.querySelector('[data-notification-dropdown-trigger]');
            const panel = wrap.querySelector('[data-notification-dropdown-panel]');
            wrap.classList.remove('is-open');
            if (panel) {
                panel.hidden = true;
            }
            if (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }
        document.querySelectorAll('[data-notification-dropdown].is-open').forEach((wrap) => {
            const trigger = wrap.querySelector('[data-notification-dropdown-trigger]');
            const panel = wrap.querySelector('[data-notification-dropdown-panel]');
            wrap.classList.remove('is-open');
            if (panel) {
                panel.hidden = true;
            }
            if (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
                trigger.focus();
            }
        });
    });
});
