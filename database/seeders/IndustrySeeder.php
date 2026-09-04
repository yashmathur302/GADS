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

    /**
     * Sub-categories (niches) for industries broad enough that lumping all
     * of their keywords together would mix unrelated searches. Industries
     * not listed here get a single "General" niche so keywords can still
     * be added right away — more niches can be added from the UI.
     */
    private const NICHES = [
        'Healthcare & Medical' => [
            'Dermatology Clinic',
            'Pediatric Clinic',
            'Hair Transplant Clinic',
            'General Physician Clinic',
            'Physiotherapy Clinic',
            'Cosmetic & Plastic Surgery Clinic',
            'Eye Care Clinic',
            'Diagnostic & Pathology Lab',
        ],
        'Education & E-Learning' => [
            'Preschool',
            'Playschool',
            'K-12 School (Class 1-12)',
            'Music School',
            'Online Tutoring',
            'Test Prep & Coaching Classes',
        ],
    ];

    public function run(): void
    {
        foreach (self::INDUSTRIES as $name) {
            $industry = Industry::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );

            $niches = self::NICHES[$name] ?? ['General'];

            foreach ($niches as $nicheName) {
                $industry->niches()->firstOrCreate(
                    ['slug' => Str::slug($nicheName)],
                    ['name' => $nicheName]
                );
            }
        }
    }
}
