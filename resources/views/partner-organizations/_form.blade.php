<form class="resource-form" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <label class="field">
            <span class="field-label">Davlat</span>
            <select name="country_id" required>
                <option value="">Davlatni tanlang</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) old('country_id', $partnerOrganization->country_id) === (string) $country->id)>{{ $country->display_name }}</option>
                @endforeach
            </select>
            @error('country_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Tashkilot turi</span>
            <select name="organization_type_id">
                <option value="">Biriktirilmagan</option>
                @foreach ($organizationTypes as $organizationType)
                    <option value="{{ $organizationType->id }}" @selected((string) old('organization_type_id', $partnerOrganization->organization_type_id) === (string) $organizationType->id)>{{ $organizationType->display_name }}</option>
                @endforeach
            </select>
            @error('organization_type_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Nomi (UZ)</span>
            <input type="text" name="name_uz" value="{{ old('name_uz', $partnerOrganization->name_uz) }}" placeholder="Ichki ishlar vazirligi" required>
            @error('name_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Nomi (RU)</span>
            <input type="text" name="name_ru" value="{{ old('name_ru', $partnerOrganization->name_ru) }}" placeholder="Министерство внутренних дел" required>
            @error('name_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Qisqa nom</span>
            <input type="text" name="short_name" value="{{ old('short_name', $partnerOrganization->short_name) }}" placeholder="IIV">
            @error('short_name')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Holat</span>
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
            <span class="field-label">Shahar</span>
            <input type="text" name="city" value="{{ old('city', $partnerOrganization->city) }}" placeholder="Toshkent">
            @error('city')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Website</span>
            <input type="text" name="website" value="{{ old('website', $partnerOrganization->website) }}" placeholder="example.org">
            @error('website')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">Tashkilot info fayli</span>
            <input
                type="file"
                name="organization_info_file"
                accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
            >
            <span class="field-help">Faqat PDF yoki Word hujjati. Maksimal hajm 50 MB.</span>
            @if ($partnerOrganization->organizationInfoDocument?->file_url)
                <span class="field-help">
                    Joriy fayl:
                    <a href="{{ $partnerOrganization->organizationInfoDocument->file_url }}" download="{{ $partnerOrganization->organizationInfoDocument->file_name }}">
                        {{ $partnerOrganization->organizationInfoDocument->file_name }}
                    </a>
                </span>
                <div class="detail-actions-inline">
                    <a class="action-pill" href="{{ $partnerOrganization->organizationInfoDocument->file_url }}" target="_blank" rel="noopener">
                        <i class="material-icons" aria-hidden="true">open_in_new</i>
                        <span>Ochish</span>
                    </a>
                    <a class="action-pill" href="{{ $partnerOrganization->organizationInfoDocument->file_url }}" download="{{ $partnerOrganization->organizationInfoDocument->file_name }}">
                        <i class="material-icons" aria-hidden="true">file_download</i>
                        <span>Faylni olish</span>
                    </a>
                    <button
                        class="action-pill action-pill--danger"
                        type="submit"
                        form="partner-organization-info-delete-{{ $partnerOrganization->id }}"
                        onclick="return confirm('Ushbu tashkilot info faylini o\\'chirishni tasdiqlaysizmi?')"
                    >
                        <i class="material-icons" aria-hidden="true">delete</i>
                        <span>Faylni o'chirish</span>
                    </button>
                </div>
            @endif
            @error('organization_info_file')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">Manzil</span>
            <input type="text" name="address" value="{{ old('address', $partnerOrganization->address) }}" placeholder="Amir Temur ko'chasi, 10-uy">
            @error('address')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <div class="field field--span-2 partnership-history-field" data-partnership-history-field>
            <label class="field-label" for="partnership_history">Hamkorlik tarixi</label>
            <textarea
                id="partnership_history"
                name="partnership_history"
                placeholder="Hamkorlikning bosqichlari, uchrashuvlar, natijalar va muhim tarixiy ma'lumotlarni kiriting"
                data-partnership-history-editor
            >{{ old('partnership_history', $partnerOrganization->partnership_history) }}</textarea>
            <div class="partnership-history-tools">
                <button class="action-pill" type="button" data-partnership-history-download disabled>
                    <i class="material-icons" aria-hidden="true">description</i>
                    <span>Download as Word</span>
                </button>
                <span
                    class="field-help partnership-history-status"
                    data-partnership-history-status
                    data-default-message="Editor orqali matnni yozing. Saqlash va Word export plain text ko'rinishida bajariladi."
                >
                    Editor yuklanmoqda...
                </span>
            </div>
            @error('partnership_history')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>

        <label class="field field--span-2">
            <span class="field-label">Izoh</span>
            <textarea name="notes" placeholder="Hamkorlik bo'yicha qisqa izoh">{{ old('notes', $partnerOrganization->notes) }}</textarea>
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
                        setStatus(field, "TinyMCE yoki docx kutubxonasi yuklanmadi. Oddiy textarea ishlashda davom etadi.", true);
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
                            setStatus(field, "Editor ishga tushmadi. Oddiy textarea orqali davom etishingiz mumkin.", true);
                            return;
                        }

                        downloadButton.disabled = false;
                        setStatus(field, field.querySelector('[data-partnership-history-status]')?.dataset.defaultMessage || '');

                        downloadButton.addEventListener('click', async () => {
                            const plainText = extractPlainText(editor);

                            if (!plainText) {
                                setStatus(field, "Avval hamkorlik tarixi matnini kiriting, keyin Word faylni yuklab oling.", true);
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
                            setStatus(field, 'Word fayl tayyorlanmoqda...');

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

                                setStatus(field, 'Word fayl muvaffaqiyatli yuklab olindi.');
                            } catch (error) {
                                console.error(error);
                                setStatus(field, 'Word faylni yaratishda xatolik yuz berdi.', true);
                            } finally {
                                downloadButton.disabled = false;
                            }
                        });
                    }).catch((error) => {
                        console.error(error);
                        setStatus(field, "Editorni yuklashda xatolik yuz berdi. Oddiy textarea orqali davom etishingiz mumkin.", true);
                    });
                });
            });
        </script>
    @endpush
@endonce
