document.addEventListener('DOMContentLoaded', () => {
    const sidebarItemIcons = {
        dashboard: 'dashboard',
        cooperation: 'public',
        agreements: 'description',
        events: 'event',
        visits: 'flight_takeoff',
        documents: 'upload_file',
        settings: 'settings',
    };

    const submenuIcons = {
        countries: 'public',
        'partner-organizations': 'business',
        'partner-contacts': 'perm_contact_calendar',
        'organization-types': 'domain',
        agreements: 'description',
        'agreement-types': 'note_add',
        'agreement-directions': 'playlist_add',
        'events-index': 'event',
        'event-types': 'event_note',
        'visits-index': 'flight_takeoff',
        'visit-types': 'category',
        'documents-index': 'upload_file',
        'document-types': 'description',
        users: 'person_add',
        departments: 'add_business',
        ranks: 'military_tech',
        'activity-logs': 'history',
        'role-permissions': 'task_alt',
    };

    const buttonIconRules = [
        { pattern: /^(yangi|\u044F\u043D\u0433\u0438|\u043D\u043E\u0432)/i, icon: 'add', replaceExisting: true, className: 'is-create-action' },
        { pattern: /^(filtrlash|\u0444\u0438\u043B\u0442\u0440\u043B\u0430\u0448|\u0444\u0438\u043B\u044C\u0442\u0440|qidirish|\u049B\u0438\u0434\u0438\u0440\u0438\u0448|\u043F\u043E\u0438\u0441\u043A)$/i, icon: 'filter_list', replaceExisting: true, className: 'is-filter-action' },
        { pattern: /^(bekor qilish|\u0431\u0435\u043A\u043E\u0440 \u049B\u0438\u043B\u0438\u0448|\u043E\u0442\u043C\u0435\u043D\u0430)$/i, icon: 'close' },
        { pattern: /^(yopish|\u0451\u043F\u0438\u0448|\u0437\u0430\u043A\u0440\u044B\u0442\u044C)$/i, icon: 'close' },
        { pattern: /ro['`]?yxatga qaytish|\u0440\u045E\u0439\u0445\u0430\u0442\u0433\u0430 \u049B\u0430\u0439\u0442\u0438\u0448|orqaga|\u043E\u0440\u0442\u0433\u0430|qaytish|\u049B\u0430\u0439\u0442\u0438\u0448|\u043D\u0430\u0437\u0430\u0434/i, icon: 'arrow_back' },
        { pattern: /^(dashboard|\u0434\u0430\u0448\u0431\u043E\u0440\u0434|\u043F\u0430\u043D\u0435\u043B\u044C)$/i, icon: 'dashboard' },
    ];

    const normalizeText = (value) => value.replace(/\s+/g, ' ').trim();

    const createMaterialIcon = (name) => {
        const icon = document.createElement('i');
        icon.className = 'material-icons';
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = name;
        return icon;
    };

    const replaceContainerIcon = (container, iconName) => {
        if (!container || !iconName) {
            return;
        }

        container.replaceChildren(createMaterialIcon(iconName));
    };

    const decorateSidebar = () => {
        replaceContainerIcon(document.querySelector('[data-sidebar-mobile-toggle]'), 'menu');
        replaceContainerIcon(document.querySelector('[data-sidebar-toggle]'), 'chevron_left');

        document.querySelectorAll('[data-sidebar-item]').forEach((item) => {
            replaceContainerIcon(
                item.querySelector('.ie-sidebar__item-icon'),
                sidebarItemIcons[item.dataset.sidebarItem]
            );

            if (item.hasAttribute('data-submenu-trigger')) {
                replaceContainerIcon(item.querySelector('.ie-sidebar__item-chevron'), 'chevron_right');
            }
        });

        document.querySelectorAll('[data-submenu-item]').forEach((item) => {
            const iconName = submenuIcons[item.dataset.submenuItem];
            const labelText = normalizeText(item.textContent);

            if (!iconName || !labelText || item.querySelector('.ie-sidebar__submenu-icon')) {
                return;
            }

            const iconWrapper = document.createElement('span');
            iconWrapper.className = 'ie-sidebar__submenu-icon';
            iconWrapper.setAttribute('aria-hidden', 'true');
            iconWrapper.append(createMaterialIcon(iconName));

            const label = document.createElement('span');
            label.className = 'ie-sidebar__submenu-label';
            label.textContent = labelText;

            item.replaceChildren(iconWrapper, label);
        });
    };

    const resolveButtonIconRule = (labelText) => buttonIconRules.find((rule) => rule.pattern.test(labelText)) ?? null;

    const decorateButtons = () => {
        document.querySelectorAll('.btn, .action-pill').forEach((element) => {
            const labelText = normalizeText(element.textContent);
            const iconRule = resolveButtonIconRule(labelText);
            const existingIcon = element.querySelector('.material-icons');

            if (!iconRule) {
                return;
            }

            if (iconRule.className) {
                element.classList.add(iconRule.className);
            }

            if (existingIcon) {
                if (iconRule.replaceExisting) {
                    existingIcon.textContent = iconRule.icon;
                    existingIcon.setAttribute('aria-hidden', 'true');
                }

                return;
            }

            const label = document.createElement('span');
            label.textContent = labelText;

            element.replaceChildren(createMaterialIcon(iconRule.icon), label);
        });
    };

    decorateSidebar();
    decorateButtons();
});
