<?php

namespace App\Services;

use App\Models\CondominiumLibraryDocument;
use App\Models\InternalRegulation;

class LibraryDocumentRegulationSync
{
    public function __construct(
        private LibraryDocumentTextExtractor $textExtractor
    ) {}

    public function syncForCondominium(int $condominiumId): void
    {
        $regulation = InternalRegulation::query()
            ->byCondominium($condominiumId)
            ->active()
            ->first();

        if (!$regulation) {
            return;
        }

        $searchText = $this->textExtractor->fromPlainContent($regulation->content);

        CondominiumLibraryDocument::query()->updateOrCreate(
            [
                'condominium_id' => $condominiumId,
                'internal_regulation_id' => $regulation->id,
            ],
            [
                'title' => 'Regimento Interno',
                'source' => CondominiumLibraryDocument::SOURCE_REGULATION,
                'content' => $regulation->content,
                'search_text' => $searchText,
                'is_active' => true,
                'sort_order' => 0,
                'created_by' => $regulation->updated_by,
            ]
        );
    }

    public function syncFromRegulation(InternalRegulation $regulation): void
    {
        if (!$regulation->is_active) {
            CondominiumLibraryDocument::query()
                ->where('internal_regulation_id', $regulation->id)
                ->update(['is_active' => false]);

            return;
        }

        $searchText = $this->textExtractor->fromPlainContent($regulation->content);

        CondominiumLibraryDocument::query()->updateOrCreate(
            [
                'condominium_id' => $regulation->condominium_id,
                'internal_regulation_id' => $regulation->id,
            ],
            [
                'title' => 'Regimento Interno',
                'source' => CondominiumLibraryDocument::SOURCE_REGULATION,
                'content' => $regulation->content,
                'search_text' => $searchText,
                'is_active' => true,
                'sort_order' => 0,
                'created_by' => $regulation->updated_by,
            ]
        );
    }
}
