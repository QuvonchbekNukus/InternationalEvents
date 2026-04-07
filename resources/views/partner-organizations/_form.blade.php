<form class="resource-form" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.country') }}</span>
            <select name="country_id" required>
                <option value="">{{ __('ui.pages.partner_organizations.form.placeholders.select_country') }}</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) old('country_id', $partnerOrganization->country_id) === (string) $country->id)>{{ $country->display_name }}</option>
                @endforeach
            </select>
            @error('country_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.organization_type') }}</span>
            <select name="organization_type_id">
                <option value="">{{ __('ui.pages.partner_organizations.form.values.unassigned') }}</option>
                @foreach ($organizationTypes as $organizationType)
                    <option value="{{ $organizationType->id }}" @selected((string) old('organization_type_id', $partnerOrganization->organization_type_id) === (string) $organizationType->id)>{{ $organizationType->display_name }}</option>
                @endforeach
            </select>
            @error('organization_type_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.name_uz') }}</span>
            <input type="text" name="name_uz" value="{{ old('name_uz', $partnerOrganization->name_uz) }}" placeholder="{{ __('ui.pages.partner_organizations.form.placeholders.name_uz') }}" required>
            @error('name_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.name_ru') }}</span>
            <input type="text" name="name_ru" value="{{ old('name_ru', $partnerOrganization->name_ru) }}" placeholder="{{ __('ui.pages.partner_organizations.form.placeholders.name_ru') }}" required>
            @error('name_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.short_name') }}</span>
            <input type="text" name="short_name" value="{{ old('short_name', $partnerOrganization->short_name) }}" placeholder="{{ __('ui.pages.partner_organizations.form.placeholders.short_name') }}">
            @error('short_name')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.status') }}</span>
            <select name="status" required>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected(old('status', $partnerOrganization->status) === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>
            @error('status')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.city') }}</span>
            <input type="text" name="city" value="{{ old('city', $partnerOrganization->city) }}" placeholder="{{ __('ui.pages.partner_organizations.form.placeholders.city') }}">
            @error('city')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.website') }}</span>
            <input type="text" name="website" value="{{ old('website', $partnerOrganization->website) }}" placeholder="{{ __('ui.pages.partner_organizations.form.placeholders.website') }}">
            @error('website')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.organization_info_file') }}</span>
            <input
                type="file"
                name="organization_info_file"
                accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
            >
            <span class="field-help">{{ __('ui.pages.partner_organizations.form.file.help') }}</span>
            @if ($partnerOrganization->organizationInfoDocument?->file_url)
                <span class="field-help">
                    {{ __('ui.pages.partner_organizations.form.file.current') }}:
                    <a href="{{ $partnerOrganization->organizationInfoDocument->file_url }}" download="{{ $partnerOrganization->organizationInfoDocument->file_name }}">
                        {{ $partnerOrganization->organizationInfoDocument->file_name }}
                    </a>
                </span>
                <div class="detail-actions-inline">
                    <a class="action-pill" href="{{ $partnerOrganization->organizationInfoDocument->file_url }}" target="_blank" rel="noopener">
                        <i class="material-icons" aria-hidden="true">open_in_new</i>
                        <span>{{ __('ui.common.actions.open') }}</span>
                    </a>
                    <a class="action-pill" href="{{ $partnerOrganization->organizationInfoDocument->file_url }}" download="{{ $partnerOrganization->organizationInfoDocument->file_name }}">
                        <i class="material-icons" aria-hidden="true">file_download</i>
                        <span>{{ __('ui.common.actions.download_file') }}</span>
                    </a>
                    <button
                        class="action-pill action-pill--danger"
                        type="submit"
                        form="partner-organization-info-delete-{{ $partnerOrganization->id }}"
                        data-confirm-message="{{ __('ui.pages.partner_organizations.form.file.confirm_delete') }}"
                        onclick="return confirm(this.dataset.confirmMessage)"
                    >
                        <i class="material-icons" aria-hidden="true">delete</i>
                        <span>{{ __('ui.common.actions.delete_file') }}</span>
                    </button>
                </div>
            @endif
            @error('organization_info_file')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.address') }}</span>
            <input type="text" name="address" value="{{ old('address', $partnerOrganization->address) }}" placeholder="{{ __('ui.pages.partner_organizations.form.placeholders.address') }}">
            @error('address')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <div class="field field--span-2 partnership-history-field" data-partnership-history-field>
            <label class="field-label" for="partnership_history">{{ __('ui.pages.partner_organizations.form.labels.partnership_history') }}</label>
            <textarea
                id="partnership_history"
                name="partnership_history"
                placeholder="{{ __('ui.pages.partner_organizations.form.placeholders.partnership_history') }}"
                data-partnership-history-editor
            >{{ old('partnership_history', $partnerOrganization->partnership_history) }}</textarea>
            <div class="partnership-history-tools">
                <button class="action-pill" type="button" data-partnership-history-download disabled>
                    <i class="material-icons" aria-hidden="true">description</i>
                    <span>{{ __('ui.pages.partner_organizations.form.history.download_word') }}</span>
                </button>
                <span
                    class="field-help partnership-history-status"
                    data-partnership-history-status
                    data-default-message="{{ __('ui.pages.partner_organizations.form.history.default_message') }}"
                >
                    {{ __('ui.pages.partner_organizations.form.history.loading') }}
                </span>
            </div>
            @error('partnership_history')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>

        <label class="field field--span-2">
            <span class="field-label">{{ __('ui.pages.partner_organizations.form.labels.notes') }}</span>
            <textarea name="notes" placeholder="{{ __('ui.pages.partner_organizations.form.placeholders.notes') }}">{{ old('notes', $partnerOrganization->notes) }}</textarea>
            @error('notes')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>
    </div>

    <div class="form-actions">
        <a class="btn btn--ghost" href="{{ route('partner-organizations.index') }}">{{ __('ui.common.actions.cancel') }}</a>
        <button class="btn btn--primary" type="submit">
            <i class="material-icons" aria-hidden="true">save</i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</form>

@if ($partnerOrganization->exists && $partnerOrganization->organizationInfoDocument)
    <form
        id="partner-organization-info-delete-{{ $partnerOrganization->id }}"
        method="POST"
        action="{{ route('partner-organizations.organization-info.destroy', $partnerOrganization) }}"
        hidden
    >
        @csrf
        @method('DELETE')
    </form>
@endif

@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tinymce@8.3.2/tinymce.min.js" referrerpolicy="origin"></script>
        <script src="https://unpkg.com/docx@9.6.1/dist/index.umd.cjs"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const editorFields = document.querySelectorAll('[data-partnership-history-field]');

                if (!editorFields.length) {
                    return;
                }

                const setStatus = (field, message, isError = false) => {
                    const status = field.querySelector('[data-partnership-history-status]');

                    if (!status) {
                        return;
                    }

                    status.textContent = message;
                    status.classList.toggle('is-error', isError);
                };

                const slugify = (value) => value
                    .toString()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-zA-Z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '')
                    .toLowerCase();

                const extractPlainText = (editor) => editor
                    .getContent({ format: 'text' })
                    .replace(/\u00a0/g, ' ')
                    .replace(/\r\n/g, '\n')
                    .replace(/\n{3,}/g, '\n\n')
                    .trim();

                if (!window.tinymce || !window.docx) {
                    editorFields.forEach((field) => {
                        setStatus(field, "{{ __('ui.pages.partner_organizations.form.history.messages.libs_missing') }}", true);
                    });

                    return;
                }

                editorFields.forEach((field, index) => {
                    const textarea = field.querySelector('[data-partnership-history-editor]');
                    const downloadButton = field.querySelector('[data-partnership-history-download]');
                    const form = field.closest('form');

                    if (!textarea || !downloadButton) {
                        return;
                    }

                    if (!textarea.id) {
                        textarea.id = `partnership-history-${index + 1}`;
                    }

                    if (form) {
                        form.addEventListener('submit', () => {
                            const editor = window.tinymce.get(textarea.id);

                            if (editor) {
                                textarea.value = extractPlainText(editor);
                            }
                        });
                    }

                    window.tinymce.init({
                        target: textarea,
                        license_key: 'gpl',
                        promotion: false,
                        branding: false,
                        menubar: false,
                        height: 340,
                        skin: 'oxide-dark',
                        content_css: 'dark',
                        plugins: 'lists link table wordcount autolink',
                        toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | alignleft aligncenter alignright | link table | removeformat',
                        content_style: 'body { font-family: Georgia, serif; font-size: 14px; line-height: 1.7; } a { color: #60a5fa; }',
                    }).then(([editor]) => {
                        if (!editor) {
                            setStatus(field, "{{ __('ui.pages.partner_organizations.form.history.messages.editor_init_failed') }}", true);
                            return;
                        }

                        downloadButton.disabled = false;
                        setStatus(field, field.querySelector('[data-partnership-history-status]')?.dataset.defaultMessage || '');

                        downloadButton.addEventListener('click', async () => {
                            const plainText = extractPlainText(editor);

                            if (!plainText) {
                                setStatus(field, "{{ __('ui.pages.partner_organizations.form.history.messages.enter_text_first') }}", true);
                                return;
                            }

                            const nameInput = form?.querySelector('[name="name_uz"]');
                            const fileBaseName = slugify(nameInput?.value || '') || 'partnership-history';
                            const { Document, Packer, Paragraph, TextRun } = window.docx;
                            const paragraphs = plainText
                                .split('\n')
                                .map((line) => new Paragraph({
                                    children: [new TextRun(line)],
                                }));

                            downloadButton.disabled = true;
                            setStatus(field, "{{ __('ui.pages.partner_organizations.form.history.messages.word_generating') }}");

                            try {
                                const documentFile = new Document({
                                    sections: [
                                        {
                                            children: paragraphs.length ? paragraphs : [new Paragraph('')],
                                        },
                                    ],
                                });

                                const blob = await Packer.toBlob(documentFile);
                                const url = URL.createObjectURL(blob);
                                const link = document.createElement('a');

                                link.href = url;
                                link.download = `${fileBaseName}.docx`;
                                document.body.append(link);
                                link.click();
                                link.remove();
                                URL.revokeObjectURL(url);

                                setStatus(field, "{{ __('ui.pages.partner_organizations.form.history.messages.word_downloaded') }}");
                            } catch (error) {
                                console.error(error);
                                setStatus(field, "{{ __('ui.pages.partner_organizations.form.history.messages.word_error') }}", true);
                            } finally {
                                downloadButton.disabled = false;
                            }
                        });
                    }).catch((error) => {
                        console.error(error);
                        setStatus(field, "{{ __('ui.pages.partner_organizations.form.history.messages.editor_load_error') }}", true);
                    });
                });
            });
        </script>
    @endpush
@endonce
