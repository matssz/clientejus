<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\LegalCase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
{
    public function definition(): array
    {
        $signedAt = fake()->dateTimeBetween('-1 year', '-1 month');

        return [
            'legal_case_id' => LegalCase::factory(),
            'title' => fake()->sentence(4),
            'signed_at' => $signedAt,
            'expires_at' => fake()->dateTimeBetween('+1 month', '+2 years'),
            'status' => Contract::STATUS_ACTIVE,
        ];
    }
}
