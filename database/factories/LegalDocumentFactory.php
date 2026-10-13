<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LegalDocument>
 */
class LegalDocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'influencer_id' => User::factory()->influencer(),
            'type' => 'identity',
            'file_path' => 'legal-documents/'.fake()->uuid().'.pdf',
            'original_name' => 'cni.pdf',
            'mime_type' => 'application/pdf',
            'size' => 12345,
        ];
    }
}
