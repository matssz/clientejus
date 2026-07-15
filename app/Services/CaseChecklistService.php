<?php

namespace App\Services;

use App\Models\LegalCase;

class CaseChecklistService
{
    public function generateDefaults(LegalCase $case): int
    {
        $case->loadMissing('caseType');

        $configuration = config('case_checklists');
        $typeName = $case->caseType?->name;
        $itemNames = $configuration['types'][$typeName] ?? $configuration['default'];
        $createdItems = 0;

        foreach ($itemNames as $name) {
            $item = $case->checklistItems()->firstOrCreate(
                ['name' => $name],
                ['is_required' => true],
            );

            if ($item->wasRecentlyCreated) {
                $createdItems++;
            }
        }

        return $createdItems;
    }
}
