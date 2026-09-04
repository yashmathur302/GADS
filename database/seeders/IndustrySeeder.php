<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class IndustrySeeder extends Seeder
{
    /**
     * The industry/niche verticals a Google Ads agency typically manages
     * accounts for. This is the shared taxonomy both the Keyword Vault and
     * Negative Keyword Vault are organized by; no keyword data is seeded —
     * that's added by the admin through the UI.
     */
    private const INDUSTRIES = [
        'Legal Services',
        'Healthcare & Medical',
        'Dental',
        'Home Services (Plumbing, HVAC, Electrical, Roofing)',
        'Real Estate',
        'E-commerce & Retail',
        'SaaS & Software',
        'B2B Professional Services',
        'Finance & Insurance',
        'Automotive (Dealers & Repair)',
        'Education & E-Learning',
        'Restaurants & Hospitality',
        'Travel & Tourism',
        'Fitness & Wellness',
        'Beauty & Spa',
        'Home Improvement & Construction',
        'Moving & Storage',
        'Pest Control',
        'Non-Profit & Charity',
        'Manufacturing & Industrial',
    ];

    public function run(): void
    {
        foreach (self::INDUSTRIES as $name) {
            Industry::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}
