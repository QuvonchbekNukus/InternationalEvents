document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-sidebar-shell]').forEach((shell) => {
        const toggleButton = shell.querySelector('[data-sidebar-toggle]');
        const mobileToggle = shell.querySelector('[data-sidebar-mobile-toggle]');
        const backdrop = shell.querySelector('[data-sidebar-backdrop]');
        const mainItems = Array.from(shell.querySelectorAll('[data-sidebar-item]'));
        const submenuItems = Array.from(shell.querySelectorAll('[data-submenu-item]'));
        const mobileBreakpoint = window.matchMedia('(max-width: 1120px)');
        const submenuGroups = Array.from(shell.querySelectorAll('[data-submenu-group]'))
            .map((groupElement) => {
                const key = groupElement.dataset.submenuGroup || '';
                const trigger = groupElement.querySelector('[data-submenu-trigger]');
                const inlineSubmenu = groupElement.querySelector('[data-inline-submenu]');
                const floatingPanel = shell.querySelector(`[data-floating-panel="${key}"]`);

                if (!key || !trigger || !inlineSubmenu) {
                    return null;
                }

                return {
                    key,
                    trigger,
                    inlineSubmenu,
                    floatingPanel,
                    open: inlineSubmenu.classList.contains('is-open'),
                    hideTimer: null,
                };
            })
            .filter(Boolean);

        const isCollapsed = () => shell.classList.contains('is-collapsed');

        const getGroup = (key) => submenuGroups.find((group) => group.key === key);

        const isFloatingVisible = (group) => group?.floatingPanel?.classList.contains('is-visible') ?? false;

        const syncInlineSubmenuHeight = (group) => {
            if (isCollapsed() || !group.open) {
                group.inlineSubmenu.style.maxHeight = '0px';
                return;
            }

            group.inlineSubmenu.style.maxHeight = `${group.inlineSubmenu.scrollHeight}px`;
        };

        const setMobileOpen = (open) => {
            shell.classList.toggle('is-mobile-open', open);

            if (mobileToggle) {
                mobileToggle.setAttribute('aria-expanded', String(open));
            }
        };

        const hideFloatingPanel = (group) => {
            if (!group?.floatingPanel) {
                return;
            }

            if (group.hideTimer) {
                window.clearTimeout(group.hideTimer);
            }

            group.floatingPanel.classList.remove('is-visible');
            group.hideTimer = window.setTimeout(() => {
                group.floatingPanel.hidden = true;
            }, 240);
        };

        const hideAllFloatingPanels = (exceptKey = null) => {
            submenuGroups.forEach((group) => {
                if (group.key === exceptKey) {
                    return;
                }

                hideFloatingPanel(group);
            });
        };

        const positionFloatingPanel = (group) => {
            if (!group?.floatingPanel) {
                return;
            }

            const compactSheetBreakpoint = 760;
            const shellRect = shell.getBoundingClientRect();
            const triggerTop = group.trigger.offsetTop;
            const surfaceTop = group.trigger.closest('.ie-sidebar__surface')?.offsetTop ?? 0;
            const viewportHeight = window.innerHeight;
            const panelWidth = group.floatingPanel.offsetWidth || 240;
            const desiredTop = shellRect.top + triggerTop + surfaceTop - 4;
            const clampedTop = Math.max(16, Math.min(desiredTop, viewportHeight - 240));

            group.floatingPanel.style.right = 'auto';
            group.floatingPanel.style.bottom = 'auto';

            if (window.innerWidth <= compactSheetBreakpoint) {
                group.floatingPanel.style.left = '12px';
                group.floatingPanel.style.right = '12px';
                group.floatingPanel.style.top = 'auto';
                group.floatingPanel.style.bottom = '12px';
                return;
            }

            if (mobileBreakpoint.matches) {
                const left = Math.min(shellRect.right + 12, window.innerWidth - panelWidth - 12);
                group.floatingPanel.style.left = `${Math.max(12, left)}px`;
                group.floatingPanel.style.top = `${clampedTop}px`;
                return;
            }

            const left = Math.min(shellRect.right + 18, window.innerWidth - panelWidth - 16);
            group.floatingPanel.style.left = `${Math.max(16, left)}px`;
            group.floatingPanel.style.top = `${clampedTop}px`;
        };

        const showFloatingPanel = (group) => {
            if (!group?.floatingPanel || !isCollapsed()) {
                hideFloatingPanel(group);
                return;
            }

            if (group.hideTimer) {
                window.clearTimeout(group.hideTimer);
            }

            positionFloatingPanel(group);
            group.floatingPanel.hidden = false;

            window.requestAnimationFrame(() => {
                group.floatingPanel.classList.add('is-visible');
            });
        };

        const setInlineSubmenu = (group, open) => {
            group.open = open;

            const shouldShowInline = open && !isCollapsed();

            group.inlineSubmenu.classList.toggle('is-open', shouldShowInline);
            group.inlineSubmenu.hidden = !shouldShowInline;
            group.trigger.setAttribute('aria-expanded', String(open));
            syncInlineSubmenuHeight(group);

            if (!shouldShowInline) {
                hideFloatingPanel(group);
            }
        };

        const closeOtherGroups = (activeKey) => {
            submenuGroups.forEach((group) => {
                if (group.key === activeKey) {
                    return;
                }

                setInlineSubmenu(group, false);
            });
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
            toggleButton?.setAttribute('aria-expanded', String(!collapsed));
            toggleButton?.setAttribute('aria-label', collapsed ? "Yon panelni kengaytirish" : "Yon panelni yig'ish");

            submenuGroups.forEach((group) => {
                setInlineSubmenu(group, group.open);
            });

            if (!collapsed) {
                hideAllFloatingPanels();
                return;
            }

            const activeGroup = submenuGroups.find((group) => group.open && group.trigger.classList.contains('is-active'));

            hideAllFloatingPanels(activeGroup?.key ?? null);

            if (activeGroup) {
                showFloatingPanel(activeGroup);
            }
        };

        toggleButton?.addEventListener('click', () => {
            setCollapsed(!isCollapsed());
        });

        mobileToggle?.addEventListener('click', () => {
            setMobileOpen(!shell.classList.contains('is-mobile-open'));
        });

        backdrop?.addEventListener('click', () => {
            setMobileOpen(false);
            hideAllFloatingPanels();
        });

        submenuGroups.forEach((group) => {
            group.trigger.addEventListener('click', () => {
                setMainActive(group.key);

                if (isCollapsed()) {
                    const willOpen = !isFloatingVisible(group);

                    closeOtherGroups(group.key);

                    if (!willOpen) {
                        setInlineSubmenu(group, false);
                        return;
                    }

                    group.open = true;
                    group.trigger.setAttribute('aria-expanded', 'true');
                    hideAllFloatingPanels(group.key);
                    showFloatingPanel(group);
                    return;
                }

                const willOpen = !group.inlineSubmenu.classList.contains('is-open');

                closeOtherGroups(group.key);
                setInlineSubmenu(group, willOpen);
            });
        });

        mainItems.forEach((button) => {
            if (submenuGroups.some((group) => group.trigger === button)) {
                return;
            }

            button.addEventListener('click', () => {
                setMainActive(button.dataset.sidebarItem || '');
                clearSubmenuActive();
                submenuGroups.forEach((group) => {
                    setInlineSubmenu(group, false);
                });
            });
        });

        submenuItems.forEach((button) => {
            button.addEventListener('click', () => {
                const key = button.dataset.submenuItem || '';
                const parentGroup = getGroup(button.dataset.parentGroup || '');

                if (!parentGroup) {
                    return;
                }

                setMainActive(parentGroup.key);
                setSubmenuActive(key);
                closeOtherGroups(parentGroup.key);
                parentGroup.open = true;
                parentGroup.trigger.setAttribute('aria-expanded', 'true');

                if (isCollapsed()) {
                    showFloatingPanel(parentGroup);
                    return;
                }

                setInlineSubmenu(parentGroup, true);
            });
        });

        document.addEventListener('click', (event) => {
            const clickedInsideVisiblePanel = submenuGroups.some((group) => group.floatingPanel?.contains(event.target));

            if ((shell.contains(event.target) || clickedInsideVisiblePanel) === false) {
                hideAllFloatingPanels();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            hideAllFloatingPanels();

            if (shell.classList.contains('is-mobile-open')) {
                setMobileOpen(false);
            }
        });

        const handleViewportChange = () => {
            if (!mobileBreakpoint.matches) {
                setMobileOpen(false);
            } else {
                hideAllFloatingPanels();
            }

            submenuGroups.forEach((group) => {
                positionFloatingPanel(group);
                syncInlineSubmenuHeight(group);
            });
        };

        if (typeof mobileBreakpoint.addEventListener === 'function') {
            mobileBreakpoint.addEventListener('change', handleViewportChange);
        } else {
            mobileBreakpoint.addListener(handleViewportChange);
        }

        window.addEventListener('resize', () => {
            submenuGroups.forEach((group) => {
                positionFloatingPanel(group);
                syncInlineSubmenuHeight(group);
            });
        });

        submenuGroups.forEach((group) => {
            group.inlineSubmenu.hidden = false;

            if (group.floatingPanel) {
                group.floatingPanel.hidden = true;
            }

            syncInlineSubmenuHeight(group);
        });
    });
});
