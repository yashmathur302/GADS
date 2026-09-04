<?php

/*
|--------------------------------------------------------------------------
| Admin Sidebar Navigation
|--------------------------------------------------------------------------
|
| Single source of truth for the admin sidebar: `routes/web.php` registers
| a route for every child entry, and resources/views/components/admin/
| sidebar.blade.php renders the same structure. Each child currently maps
| to a placeholder page (resources/views/pages/placeholder.blade.php)
| until that section gets real content and a dedicated controller.
|
*/

return [

    'admin' => [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'dashboard',
        ],

        [
            'label' => 'Google Ads Playbooks',
            'icon' => 'target',
            'children' => [
                [
                    'label' => 'By Industry / Niche',
                    'uri' => 'playbooks/industry',
                    'route' => 'playbooks.industry',
                    'description' => 'Proven Google Ads approaches broken down by industry and niche.',
                ],
                [
                    'label' => 'By Campaign Type',
                    'uri' => 'playbooks/campaign-type',
                    'route' => 'playbooks.campaign-type',
                    'description' => 'Playbooks organized by campaign type — Search, Performance Max, Shopping, and more.',
                ],
                [
                    'label' => 'Bidding & Budget Strategies',
                    'uri' => 'playbooks/bidding-budget',
                    'route' => 'playbooks.bidding-budget',
                    'description' => 'Bidding strategies and budget-allocation approaches that have worked.',
                ],
            ],
        ],

        [
            'label' => 'Experiment Logs',
            'icon' => 'bulb',
            'children' => [
                [
                    'label' => 'Winning Strategies',
                    'uri' => 'experiments/winning',
                    'route' => 'experiments.winning',
                    'description' => 'A record of tests that produced a measurable win, and why.',
                ],
                [
                    'label' => 'Failed Tests',
                    'uri' => 'experiments/failed',
                    'route' => 'experiments.failed',
                    'description' => 'What NOT to do — tests that failed, and the takeaway from each.',
                ],
            ],
        ],

        [
            'label' => 'Assets & Creative Library',
            'icon' => 'wrench',
            'children' => [
                [
                    'label' => 'Top Headlines',
                    'uri' => 'assets/top-headlines',
                    'route' => 'assets.top-headlines',
                    'description' => 'High-performing RSA headlines, ready to reuse.',
                ],
                [
                    'label' => 'Top Description',
                    'uri' => 'assets/top-descriptions',
                    'route' => 'assets.top-descriptions',
                    'description' => 'High-performing RSA description lines, ready to reuse.',
                ],
                [
                    'label' => 'Keyword Vault',
                    'uri' => 'assets/keyword-vault',
                    'route' => 'assets.keyword-vault',
                    'description' => 'A shared vault of keywords worth targeting across accounts.',
                ],
                [
                    'label' => 'Negative Keyword Vault',
                    'uri' => 'assets/negative-keyword-vault',
                    'route' => 'assets.negative-keywords',
                    'description' => 'A shared vault of negative keywords worth applying across accounts.',
                ],
                [
                    'label' => 'Targeted Location',
                    'uri' => 'assets/targeted-locations',
                    'route' => 'assets.targeted-locations',
                    'description' => 'Geographic targeting setups that have performed well.',
                ],
            ],
        ],

        [
            'label' => 'System & Admin',
            'icon' => 'gear',
            'children' => [
                [
                    'label' => 'Master Settings',
                    'uri' => 'settings',
                    'route' => 'settings.master',
                    'description' => 'Application-wide configuration for this admin panel.',
                ],
            ],
        ],
    ],

];
