<form class="resource-form" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="form-grid">
        <label class="field field--span-2">
            <span class="field-label">Sarlavha (UZ)</span>
            <input type="text" name="title_uz" value="{{ old('title_uz', $visit->title_uz) }}" placeholder="Delegatsiyaning rasmiy tashrifi" required>
            @error('title_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Sarlavha (RU)</span>
            <input type="text" name="title_ru" value="{{ old('title_ru', $visit->title_ru) }}" placeholder="Официальный визит делегации" required>
            @error('title_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Sarlavha (KRYL)</span>
            <input type="text" name="title_cryl" value="{{ old('title_cryl', $visit->title_cryl) }}" placeholder="Расмий ташриф делегацияси" required>
            @error('title_cryl')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Tashrif turi</span>
            <select name="visit_type_id">
                <option value="">Biriktirilmagan</option>
                @foreach ($visitTypes as $visitType)
                    <option value="{{ $visitType->id }}" @selected((string) old('visit_type_id', $visit->visit_type_id) === (string) $visitType->id)>{{ $visitType->display_name }}</option>
                @endforeach
            </select>
            @error('visit_type_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Davlat</span>
            <select name="country_id" required>
                <option value="">Davlatni tanlang</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) old('country_id', $visit->country_id) === (string) $country->id)>{{ $country->display_name }}</option>
                @endforeach
            </select>
            @error('country_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Hamkor tashkilot</span>
            <select name="partner_organization_id">
                <option value="">Biriktirilmagan</option>
                @foreach ($partnerOrganizations as $partnerOrganization)
                    <option value="{{ $partnerOrganization->id }}" @selected((string) old('partner_organization_id', $visit->partner_organization_id) === (string) $partnerOrganization->id)>{{ $partnerOrganization->display_name }}</option>
                @endforeach
            </select>
            @error('partner_organization_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Yo'nalish</span>
            <select name="direction">
                <option value="">Tanlanmagan</option>
                @foreach ($directions as $directionValue => $directionLabel)
                    <option value="{{ $directionValue }}" @selected(old('direction', $visit->direction) === $directionValue)>{{ $directionLabel }}</option>
                @endforeach
            </select>
            @error('direction')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Holat</span>
            <select name="status" required>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected(old('status', $visit->status) === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>
            @error('status')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Boshlanish sanasi</span>
            <input type="date" name="start_date" value="{{ old('start_date', $visit->start_date?->format('Y-m-d')) }}" required>
            @error('start_date')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Tugash sanasi</span>
            <input type="date" name="end_date" value="{{ old('end_date', $visit->end_date?->format('Y-m-d')) }}">
            @error('end_date')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Javobgar foydalanuvchi</span>
            <select name="responsible_user_id">
                <option value="">Biriktirilmagan</option>
                @foreach ($responsibleUsers as $responsibleUser)
                    <option value="{{ $responsibleUser->id }}" @selected((string) old('responsible_user_id', $visit->responsible_user_id) === (string) $responsibleUser->id)>{{ $responsibleUser->full_name }}</option>
                @endforeach
            </select>
            @error('responsible_user_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Javobgar bo'lim</span>
            <select name="responsible_department_id">
                <option value="">Biriktirilmagan</option>
                @foreach ($responsibleDepartments as $responsibleDepartment)
                    <option value="{{ $responsibleDepartment->id }}" @selected((string) old('responsible_department_id', $visit->responsible_department_id) === (string) $responsibleDepartment->id)>{{ $responsibleDepartment->display_name }}</option>
                @endforeach
            </select>
            @error('responsible_department_id')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Shahar</span>
            <input type="text" name="city" value="{{ old('city', $visit->city) }}" placeholder="Toshkent">
            @error('city')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">Manzil</span>
            <input type="text" name="address" value="{{ old('address', $visit->address) }}" placeholder="Amir Temur ko'chasi, 10-uy">
            @error('address')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Latitude</span>
            <input type="number" step="0.0000001" name="latitude" value="{{ old('latitude', $visit->latitude) }}" placeholder="41.3110810">
            @error('latitude')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Longitude</span>
            <input type="number" step="0.0000001" name="longitude" value="{{ old('longitude', $visit->longitude) }}" placeholder="69.2405620">
            @error('longitude')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">Tashrif maqsadi (UZ)</span>
            <textarea name="purpose_uz" placeholder="Tashrifdan ko'zlangan asosiy maqsad">{{ old('purpose_uz', $visit->purpose_uz) }}</textarea>
            @error('purpose_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Tashrif maqsadi (RU)</span>
            <textarea name="purpose_ru" placeholder="Цель визита">{{ old('purpose_ru', $visit->purpose_ru) }}</textarea>
            @error('purpose_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Tashrif maqsadi (KRYL)</span>
            <textarea name="purpose_cryl" placeholder="Ташриф мақсади">{{ old('purpose_cryl', $visit->purpose_cryl) }}</textarea>
            @error('purpose_cryl')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">Natija (UZ)</span>
            <textarea name="result_summary_uz" placeholder="Tashrif yakunidagi asosiy natijalar">{{ old('result_summary_uz', $visit->result_summary_uz) }}</textarea>
            @error('result_summary_uz')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Natija (RU)</span>
            <textarea name="result_summary_ru" placeholder="Итоги визита">{{ old('result_summary_ru', $visit->result_summary_ru) }}</textarea>
            @error('result_summary_ru')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field">
            <span class="field-label">Natija (KRYL)</span>
            <textarea name="result_summary_cryl" placeholder="Ташриф натижаси">{{ old('result_summary_cryl', $visit->result_summary_cryl) }}</textarea>
            @error('result_summary_cryl')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">Qo'shimcha ma'lumot</span>
            <textarea name="description" placeholder="Protokol, logistika yoki boshqa izohlar">{{ old('description', $visit->description) }}</textarea>
            @error('description')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </label>

        <label class="field field--span-2">
            <span class="field-label">Biriktiriladigan fayllar</span>
            <input
                type="file"
                name="visit_files[]"
                accept=".jpg,.jpeg,.png,.gif,.webp,.bmp,.svg,.pdf,.doc,.docx,image/jpeg,image/png,image/gif,image/webp,image/bmp,image/svg+xml,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                multiple
            >
            <span class="field-help">Tashrifga oid rasm, PDF yoki Word fayllarini bir nechta tanlashingiz mumkin. Maksimal hajm har bir fayl uchun 50 MB. Tahrirlashda yangi fayl tanlansa, mavjud biriktirmalar almashtiriladi, kerak bo'lsa pastdan alohida o'chirish ham mumkin.</span>
            @error('visit_files')
                <span class="field-error">{{ $message }}</span>
            @enderror
            @error('visit_files.*')
                <span class="field-error">{{ $message }}</span>
            @enderror
            @if ($visit->exists && $visit->documents->isNotEmpty())
                @php
                    $documents = $visit->documents->sortByDesc('created_at');
                    $imageDocuments = $documents->filter(fn ($document) => $document->is_image && $document->file_url);
                    $otherDocuments = $documents->reject(fn ($document) => $document->is_image && $document->file_url);
                @endphp

                <span class="field-help">Mavjud biriktirmalar:</span>

                @if ($imageDocuments->isNotEmpty())
                    <div class="attachment-section">
                        <p class="attachment-section__label">Rasmlar</p>

                        <div class="stack-list">
                            @foreach ($imageDocuments as $document)
                                <article class="stack-list__item">
                                    @php($deleteFormId = "visit-attachment-delete-{$visit->id}-{$document->id}")
                                    <div class="detail-media detail-media--compact">
                                        <a href="{{ $document->file_url }}" target="_blank" rel="noopener">
                                            <img
                                                class="detail-media__thumb"
                                                src="{{ $document->file_url }}"
                                                alt="{{ $document->file_name }}"
                                                loading="lazy"
                                            >
                                        </a>

                                        <div class="detail-media__body">
                                            <strong>{{ $document->file_name }}</strong>
                                            <span>
                                                {{ strtoupper($document->file_ext ?: 'fayl') }}
                                                @if ($document->file_size_human)
                                                    {{ ' | '.$document->file_size_human }}
                                                @endif
                                            </span>

                                            <div class="detail-actions-inline">
                                                <a class="action-pill" href="{{ $document->file_url }}" target="_blank" rel="noopener">
                                                    <i class="material-icons" aria-hidden="true">open_in_new</i>
                                                    <span>Ochish</span>
                                                </a>
                                                <a class="action-pill" href="{{ $document->file_url }}" download="{{ $document->file_name }}">
                                                    <i class="material-icons" aria-hidden="true">file_download</i>
                                                    <span>Faylni olish</span>
                                                </a>
                                                <button
                                                    class="action-pill action-pill--danger"
                                                    type="submit"
                                                    form="{{ $deleteFormId }}"
                                                    onclick="return confirm('Ushbu biriktirilgan faylni o\\'chirishni tasdiqlaysizmi?')"
                                                >
                                                    <i class="material-icons" aria-hidden="true">delete</i>
                                                    <span>Faylni o'chirish</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($otherDocuments->isNotEmpty())
                    <div class="attachment-section">
                        <p class="attachment-section__label">Fayllar</p>

                        <div class="stack-list">
                            @foreach ($otherDocuments as $document)
                                <article class="stack-list__item">
                                    @php($deleteFormId = "visit-attachment-delete-{$visit->id}-{$document->id}")
                                    <strong>{{ $document->file_name }}</strong>
                                    <span>
                                        {{ strtoupper($document->file_ext ?: 'fayl') }}
                                        @if ($document->file_size_human)
                                            {{ ' | '.$document->file_size_human }}
                                        @endif
                                    </span>
                                    @if ($document->file_url)
                                        <div class="detail-actions-inline">
                                            <a class="action-pill" href="{{ $document->file_url }}" target="_blank" rel="noopener">
                                                <i class="material-icons" aria-hidden="true">open_in_new</i>
                                                <span>Ochish</span>
                                            </a>
                                            <a class="action-pill" href="{{ $document->file_url }}" download="{{ $document->file_name }}">
                                                <i class="material-icons" aria-hidden="true">file_download</i>
                                                <span>Faylni olish</span>
                                            </a>
                                            <button
                                                class="action-pill action-pill--danger"
                                                type="submit"
                                                form="{{ $deleteFormId }}"
                                                onclick="return confirm('Ushbu biriktirilgan faylni o\\'chirishni tasdiqlaysizmi?')"
                                            >
                                                <i class="material-icons" aria-hidden="true">delete</i>
                                                <span>Faylni o'chirish</span>
                                            </button>
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </label>
    </div>

    <div class="form-actions">
        <a class="btn btn--ghost" href="{{ route('visits.index') }}">{{ __('ui.common.actions.cancel') }}</a>
        <button class="btn btn--primary" type="submit">
            <i class="material-icons" aria-hidden="true">save</i>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>
</form>

@if ($visit->exists && $visit->documents->isNotEmpty())
    @foreach ($visit->documents as $document)
        <form
            id="visit-attachment-delete-{{ $visit->id }}-{{ $document->id }}"
            method="POST"
            action="{{ route('visits.attachments.destroy', [$visit, $document]) }}"
            hidden
        >
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif
