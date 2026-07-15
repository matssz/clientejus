<?php

namespace Database\Factories;

use App\Models\CaseType;
use App\Models\Client;
use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LegalCase>
 */
class LegalCaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'case_type_id' => CaseType::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => LegalCase::STATUS_NOVO_ATENDIMENTO,
            'opened_at' => now()->toDateString(),
            'closed_at' => null,
        ];
    }
}
