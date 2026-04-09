<?php

namespace App\Services;

use App\Models\Document;
use App\Models\PartnerOrganization;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;

class PartnerOrganizationPartnershipHistoryWordService
{
    private const STORAGE_DISK = 'documents';

    public function readPartnershipHistoryContent(PartnerOrganization $partnerOrganization): string
    {
        $document = $this->resolveDocument($partnerOrganization);

        if (! $document || ! $document->file_path || ! Storage::disk(self::STORAGE_DISK)->exists($document->file_path)) {
            return '';
        }

        try {
            $phpWord = IOFactory::load(Storage::disk(self::STORAGE_DISK)->path($document->file_path));
        } catch (\Throwable) {
            return '';
        }

        $lines = [];

        foreach ($phpWord->getSections() as $section) {
            $this->extractLinesFromElements($section->getElements(), $lines);
        }

        return trim(implode(PHP_EOL, $lines));
    }

    public function upsertForPartnerOrganization(PartnerOrganization $partnerOrganization, string $content, int $userId): int
    {
        $document = $this->resolveDocument($partnerOrganization);
        $oldPath = $document?->file_path;
        $targetPath = $this->targetFilePath($partnerOrganization);

        Storage::disk(self::STORAGE_DISK)->makeDirectory($this->targetDirectory($partnerOrganization));
        $this->writeWordFile($targetPath, $content);

        $fileName = pathinfo($targetPath, PATHINFO_BASENAME);
        $absolutePath = Storage::disk(self::STORAGE_DISK)->path($targetPath);

        if (! $document) {
            $document = new Document();
        }

        $displayNameUz = $partnerOrganization->name_uz ?: $partnerOrganization->display_name;
        $displayNameRu = $partnerOrganization->name_ru ?: $partnerOrganization->display_name;

        $document->fill([
            'title_ru' => "{$displayNameRu} история сотрудничества",
            'title_uz' => "{$displayNameUz} hamkorlik tarixi",
            'document_number' => null,
            'document_type_id' => null,
            'file_name' => $fileName,
            'file_path' => $targetPath,
            'file_ext' => 'docx',
            'file_size' => @filesize($absolutePath) ?: 0,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'country_id' => $partnerOrganization->country_id,
            'partner_organization_id' => $partnerOrganization->id,
            'agreement_id' => null,
            'visit_id' => null,
            'event_id' => null,
            'uploaded_by' => $userId,
            'status' => 'faol',
            'is_confidential' => false,
            'description' => 'Hamkor tashkilot hamkorlik tarixi Word hujjati',
        ]);
        $document->save();

        if ($oldPath && $oldPath !== $targetPath && Storage::disk(self::STORAGE_DISK)->exists($oldPath)) {
            Storage::disk(self::STORAGE_DISK)->delete($oldPath);
        }

        return (int) $document->id;
    }

    private function resolveDocument(PartnerOrganization $partnerOrganization): ?Document
    {
        if ($partnerOrganization->partnership_history) {
            $document = Document::query()
                ->whereKey((int) $partnerOrganization->partnership_history)
                ->where('partner_organization_id', $partnerOrganization->id)
                ->first();

            if ($document) {
                return $document;
            }
        }

        return Document::query()
            ->where('partner_organization_id', $partnerOrganization->id)
            ->where('file_path', $this->targetFilePath($partnerOrganization))
            ->first();
    }

    private function writeWordFile(string $relativePath, string $content): void
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $normalized = trim((string) $content);

        if ($normalized === '') {
            $section->addText('');
        } elseif (str_contains($normalized, '<') && str_contains($normalized, '>')) {
            $safeHtml = strip_tags($normalized, '<p><br><strong><b><em><i><u><ul><ol><li>');
            $safeHtml = preg_replace('/<br\s*>/i', '<br/>', $safeHtml) ?? $safeHtml;
            $safeHtml = preg_replace('/&(?![a-zA-Z0-9#]+;)/', '&amp;', $safeHtml) ?? $safeHtml;

            try {
                Html::addHtml($section, '<div>'.$safeHtml.'</div>', false, false);
            } catch (\Throwable) {
                $plainText = html_entity_decode(strip_tags($safeHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $this->writePlainTextLines($section, $plainText);
            }
        } else {
            $this->writePlainTextLines($section, $normalized);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save(Storage::disk(self::STORAGE_DISK)->path($relativePath));
    }

    private function writePlainTextLines($section, string $text): void
    {
        $lines = preg_split('/\R/u', str_replace("\r\n", "\n", trim($text))) ?: [];

        if ($lines === [] || (count($lines) === 1 && $lines[0] === '')) {
            $section->addText('');

            return;
        }

        foreach ($lines as $line) {
            $section->addText($line);
        }
    }

    /**
     * @param  array<int, mixed>  $elements
     * @param  array<int, string>  $lines
     */
    private function extractLinesFromElements(array $elements, array &$lines): void
    {
        foreach ($elements as $element) {
            if (is_object($element) && method_exists($element, 'getText')) {
                $text = trim((string) $element->getText());

                if ($text !== '') {
                    $lines[] = $text;
                }
            }

            if (is_object($element) && method_exists($element, 'getElements')) {
                /** @var array<int, mixed> $childElements */
                $childElements = $element->getElements();
                $this->extractLinesFromElements($childElements, $lines);
            }
        }
    }

    private function targetDirectory(PartnerOrganization $partnerOrganization): string
    {
        return 'partner organizations/'.$this->safeSlug($partnerOrganization);
    }

    private function targetFilePath(PartnerOrganization $partnerOrganization): string
    {
        $base = $this->safeSlug($partnerOrganization);

        return $this->targetDirectory($partnerOrganization).'/'.$base.'_ph.docx';
    }

    private function safeSlug(PartnerOrganization $partnerOrganization): string
    {
        $source = $partnerOrganization->short_name
            ?: $partnerOrganization->name_uz
            ?: $partnerOrganization->name_ru
            ?: 'organization-'.$partnerOrganization->id;

        $slug = (string) str($source)->lower()->slug('_');

        return $slug !== '' ? $slug : 'organization_'.$partnerOrganization->id;
    }
}

