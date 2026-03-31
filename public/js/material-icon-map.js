document.addEventListener('DOMContentLoaded', () => {
    const buttonIconRules = [
        { pattern: /^(yangi|\u044F\u043D\u0433\u0438|\u043D\u043E\u0432)/i, icon: 'add', replaceExisting: false, className: 'is-create-action' },
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

    decorateButtons();
});
