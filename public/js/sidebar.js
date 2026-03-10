document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-sidebar-shell]').forEach((shell) => {
        const toggleButton = shell.querySelector('[data-sidebar-toggle]');
        const mobileToggle = shell.querySelector('[data-sidebar-mobile-toggle]');
        const backdrop = shell.querySelector('[data-sidebar-backdrop]');
        const submenuTrigger = shell.querySelector('[data-submenu-trigger]');
        const inlineSubmenu = shell.querySelector('[data-inline-submenu]');
        const floatingPanel = shell.querySelector('[data-floating-panel]');
        const mainItems = Array.from(shell.querySelectorAll('[data-sidebar-item]'));
        const submenuItems = Array.from(shell.querySelectorAll('[data-submenu-item]'));
        const mobileBreakpoint = window.matchMedia('(max-width: 1120px)');
        let floatingHideTimer = null;
        let submenuOpen = inlineSubmenu.classList.contains('is-open');

        const isCollapsed = () => shell.classList.contains('is-collapsed');

        const isFloatingVisible = () => floatingPanel.classList.contains('is-visible');

        const setMobileOpen = (open) => {
            shell.classList.toggle('is-mobile-open', open);
            if (mobileToggle) {
                mobileToggle.setAttribute('aria-expanded', String(open));
            }
        };

        const hideFloatingPanel = () => {
            if (floatingHideTimer) {
                window.clearTimeout(floatingHideTimer);
            }

            floatingPanel.classList.remove('is-visible');
            floatingHideTimer = window.setTimeout(() => {
                floatingPanel.hidden = true;
            }, 240);
        };

        const positionFloatingPanel = () => {
            if (!submenuTrigger) {
                return;
            }

            const compactSheetBreakpoint = 760;
            const shellRect = shell.getBoundingClientRect();
            const triggerTop = submenuTrigger.offsetTop;
            const surfaceTop = submenuTrigger.closest('.ie-sidebar__surface')?.offsetTop ?? 0;
            const viewportHeight = window.innerHeight;
            const panelWidth = floatingPanel.offsetWidth || 240;
            const desiredTop = shellRect.top + triggerTop + surfaceTop - 4;
            const clampedTop = Math.max(16, Math.min(desiredTop, viewportHeight - 240));

            floatingPanel.style.right = 'auto';
            floatingPanel.style.bottom = 'auto';

            if (window.innerWidth <= compactSheetBreakpoint) {
                floatingPanel.style.left = '12px';
                floatingPanel.style.right = '12px';
                floatingPanel.style.top = 'auto';
                floatingPanel.style.bottom = '12px';
                return;
            }

            if (mobileBreakpoint.matches) {
                const left = Math.min(shellRect.right + 12, window.innerWidth - panelWidth - 12);
                floatingPanel.style.left = `${Math.max(12, left)}px`;
                floatingPanel.style.top = `${clampedTop}px`;
                return;
            }

            const left = Math.min(shellRect.right + 18, window.innerWidth - panelWidth - 16);
            floatingPanel.style.left = `${Math.max(16, left)}px`;
            floatingPanel.style.top = `${clampedTop}px`;
        };

        const showFloatingPanel = () => {
            if (!isCollapsed()) {
                hideFloatingPanel();
                return;
            }

            if (floatingHideTimer) {
                window.clearTimeout(floatingHideTimer);
            }

            positionFloatingPanel();
            floatingPanel.hidden = false;
            window.requestAnimationFrame(() => {
                floatingPanel.classList.add('is-visible');
            });
        };

        const setInlineSubmenu = (open) => {
            submenuOpen = open;
            const shouldShowInline = open && !isCollapsed();

            inlineSubmenu.classList.toggle('is-open', shouldShowInline);
            inlineSubmenu.hidden = !shouldShowInline;
            submenuTrigger.setAttribute('aria-expanded', String(open));

            if (!shouldShowInline) {
                hideFloatingPanel();
            }
        };

        const setMainActive = (key) => {
            mainItems.forEach((button) => {
                button.classList.toggle('is-active', button.dataset.sidebarItem === key);
            });
        };

        const setSubmenuActive = (key) => {
            submenuItems.forEach((button) => {
                button.classList.toggle('is-active', button.dataset.submenuItem === key);
            });
        };

        const clearSubmenuActive = () => {
            submenuItems.forEach((button) => {
                button.classList.remove('is-active');
            });
        };

        const setCollapsed = (collapsed) => {
            shell.classList.toggle('is-collapsed', collapsed);
            toggleButton.setAttribute('aria-expanded', String(!collapsed));
            toggleButton.setAttribute('aria-label', collapsed ? "Yon panelni kengaytirish" : "Yon panelni yig'ish");

            if (collapsed && submenuOpen && submenuTrigger.classList.contains('is-active')) {
                showFloatingPanel();
            } else {
                hideFloatingPanel();
            }

            setInlineSubmenu(submenuOpen && submenuTrigger.classList.contains('is-active'));
        };

        toggleButton?.addEventListener('click', () => {
            setCollapsed(!isCollapsed());
        });

        mobileToggle?.addEventListener('click', () => {
            setMobileOpen(!shell.classList.contains('is-mobile-open'));
        });

        backdrop?.addEventListener('click', () => {
            setMobileOpen(false);
            hideFloatingPanel();
        });

        submenuTrigger?.addEventListener('click', () => {
            setMainActive('asosiy');

            if (isCollapsed()) {
                if (isFloatingVisible()) {
                    submenuOpen = false;
                    hideFloatingPanel();
                } else {
                    submenuOpen = true;
                    showFloatingPanel();
                }

                submenuTrigger.setAttribute('aria-expanded', String(submenuOpen));
                return;
            }

            const willOpen = !inlineSubmenu.classList.contains('is-open');
            setInlineSubmenu(willOpen);
        });

        mainItems.forEach((button) => {
            if (button === submenuTrigger) {
                return;
            }

            button.addEventListener('click', () => {
                setMainActive(button.dataset.sidebarItem || '');
                clearSubmenuActive();
                submenuOpen = false;
                setInlineSubmenu(false);
            });
        });

        submenuItems.forEach((button) => {
            button.addEventListener('click', () => {
                const key = button.dataset.submenuItem || '';
                setMainActive('asosiy');
                setSubmenuActive(key);
                submenuOpen = true;

                if (isCollapsed()) {
                    showFloatingPanel();
                } else {
                    setInlineSubmenu(true);
                }
            });
        });

        document.addEventListener('click', (event) => {
            if (!shell.contains(event.target) && isFloatingVisible()) {
                hideFloatingPanel();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            if (isFloatingVisible()) {
                hideFloatingPanel();
            }

            if (shell.classList.contains('is-mobile-open')) {
                setMobileOpen(false);
            }
        });

        const handleViewportChange = () => {
            if (!mobileBreakpoint.matches) {
                setMobileOpen(false);
                positionFloatingPanel();
                return;
            }

            hideFloatingPanel();
        };

        if (typeof mobileBreakpoint.addEventListener === 'function') {
            mobileBreakpoint.addEventListener('change', handleViewportChange);
        } else {
            mobileBreakpoint.addListener(handleViewportChange);
        }

        window.addEventListener('resize', positionFloatingPanel);

        inlineSubmenu.hidden = false;
        floatingPanel.hidden = true;
        positionFloatingPanel();
    });
});
