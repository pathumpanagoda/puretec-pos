<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeatureDefinition;

class FeatureDefinitionSeeder extends Seeder
{
    /**
     * Seed the feature_definitions table with all POS features.
     */
    public function run(): void
    {
        FeatureDefinition::seedDefaults();
        $this->command->info('✅ Feature definitions seeded successfully!');
    }
}
