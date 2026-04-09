<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;

class CountryPartnershipHistoryWordService
{
    private const STORAGE_DISK = 'documents';

    public function readPartnershipHistoryContent(Country $country): string
    {
        $document = $this->resolveDocument($country);

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

    public function upsertForCountry(Country $country, string $content, int $userId): int
    {
        $document = $this->resolveDocument($country);
        $oldPath = $document?->file_path;
        $targetPath = $this->targetFilePath($country);

        Storage::disk(self::STORAGE_DISK)->makeDirectory($this->targetDirectory($country));
        $this->writeWordFile($targetPath, $content);

        $fileName = pathinfo($targetPath, PATHINFO_BASENAME);
        $absolutePath = Storage::disk(self::STORAGE_DISK)->path($targetPath);

        if (! $document) {
            $document = new Document();
        }

        $document->fill([
            'title_ru' => "История сотрудничества ({$this->safeIso3($country)})",
            'title_uz' => "Hamkorlik tarixi ({$this->safeIso3($country)})",
            'document_number' => null,
            'document_type_id' => null,
            'file_name' => $fileName,
            'file_path' => $targetPath,
            'file_ext' => 'docx',
            'file_size' => @filesize($absolutePath) ?: 0,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'country_id' => $country->id,
            'partner_organization_id' => null,
            'agreement_id' => null,
            'visit_id' => null,
            'event_id' => null,
            'uploaded_by' => $userId,
            'status' => 'faol',
            'is_confidential' => false,
            'description' => "Davlat hamkorlik tarixi Word hujjati ({$this->safeIso3($country)})",
        ]);
        $document->save();

        if ($oldPath && $oldPath !== $targetPath && Storage::disk(self::STORAGE_DISK)->exists($oldPath)) {
            Storage::disk(self::STORAGE_DISK)->delete($oldPath);
        }

        return (int) $document->id;
    }

    private function resolveDocument(Country $country): ?Document
    {
        if ($country->partnership_history) {
            $document = Document::query()
                ->whereKey((int) $country->partnership_history)
                ->where('country_id', $country->id)
                ->first();

            if ($document) {
                return $document;
            }
        }

        return Document::query()
            ->where('country_id', $country->id)
            ->where('file_path', $this->targetFilePath($country))
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

    private function targetDirectory(Country $country): string
    {
        return 'countries/'.$this->safeIso3Slug($country);
    }

    private function targetFilePath(Country $country): string
    {
        $iso3 = $this->safeIso3Slug($country);

        return $this->targetDirectory($country).'/'.$iso3.'_ph.docx';
    }

    private function safeIso3(Country $country): string
    {
        return strtoupper((string) ($country->iso3 ?: 'UNK'));
    }

    private function safeIso3Slug(Country $country): string
    {
        return strtolower((string) ($country->iso3 ?: 'unk'));
    }
}

