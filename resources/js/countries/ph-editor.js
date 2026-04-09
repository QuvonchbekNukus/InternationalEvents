import Quill from 'quill';

const isHtmlContent = (value) => /<\s*\/?[a-z][^>]*>/i.test(value);

const escapeHtml = (value) => value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-country-ph-quill]').forEach((editorRoot) => {
        const editorContainer = editorRoot.querySelector('[data-country-ph-quill-editor]');
        const input = editorRoot.querySelector('[data-country-ph-quill-input]');
        const form = editorRoot.closest('form');

        if (!editorContainer || !input || !form) {
            return;
        }

        const quill = new Quill(editorContainer, {
            theme: 'snow',
            modules: {
                toolbar: editorRoot.querySelector('[data-country-ph-quill-toolbar]') || [
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['clean'],
                ],
            },
        });

        const rawValue = input.value || '';
        if (rawValue !== '') {
            quill.root.innerHTML = isHtmlContent(rawValue)
                ? rawValue
                : escapeHtml(rawValue).replace(/\r?\n/g, '<br>');
        }

        const syncInput = () => {
            const html = quill.root.innerHTML.trim();
            input.value = html === '<p><br></p>' ? '' : html;
        };

        quill.on('text-change', syncInput);
        form.addEventListener('submit', syncInput);
        syncInput();
    });
});

