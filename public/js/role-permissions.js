(() => {
    const openMap = {
        create: 'rp-d-create',
        rename: 'rp-d-rename',
        delete: 'rp-d-delete',
    };

    document.querySelectorAll('[data-rp-open]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const key = btn.getAttribute('data-rp-open');
            const id = key ? openMap[key] : null;
            if (!id) {
                return;
            }
            const dialog = document.getElementById(id);
            if (dialog && typeof dialog.showModal === 'function') {
                dialog.showModal();
            }
        });
    });

    document.querySelectorAll('[data-rp-close]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const dialog = btn.closest('dialog');
            if (dialog && typeof dialog.close === 'function') {
                dialog.close();
            }
        });
    });

    document.querySelectorAll('dialog.rp-dialog').forEach((dialog) => {
        dialog.addEventListener('click', (e) => {
            if (e.target === dialog) {
                dialog.close();
            }
        });
    });

    document.querySelectorAll('dialog[data-rp-open-on-load]').forEach((dialog) => {
        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        }
    });
})();
