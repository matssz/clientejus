<?php

namespace Database\Seeders;

use App\Models\CaseType;
use Illuminate\Database\Seeder;

class CaseTypeSeeder extends Seeder
{
    public function run(): void
    {
        $caseTypes = [
            'Previdenciário' => 'Benefícios, aposentadorias e demandas contra o INSS.',
            'Consumidor' => 'Relações de consumo, cobranças e indenizações.',
            'Família' => 'Divórcio, alimentos, guarda e outras relações familiares.',
            'Contratos' => 'Elaboração, revisão, vigência e alterações contratuais.',
        ];

        foreach ($caseTypes as $name => $description) {
            CaseType::updateOrCreate(
                ['name' => $name],
                ['description' => $description],
            );
        }
    }
}
