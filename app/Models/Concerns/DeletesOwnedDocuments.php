<?php

namespace App\Models\Concerns;

use App\Models\Document;

trait DeletesOwnedDocuments
{
    protected static function bootDeletesOwnedDocuments(): void
    {
        static::deleting(function ($model): void {
            $model->ownedDocumentsQuery()
                ->get()
                ->unique('id')
                ->each(function (Document $document): void {
                    $document->delete();
                });
        });
    }

    abstract protected function ownedDocumentsQuery();
}
