<?php

return [
    'insights' => [
        'labels' => 'LLL:EXT:umami/Resources/Private/Language/locallang_mod_insights.xlf',
        'iconIdentifier' => 'module-insights',
        'extensionName' => 'umami',
        'position' => ['after' => 'web'],
    ],
    'insights_umami' => [
        'parent' => 'insights',
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/umami',
        'labels' => 'LLL:EXT:umami/Resources/Private/Language/locallang_mod_umami.xlf',
        'extensionName' => 'Umami',
        'icon' => 'EXT:umami/Resources/Public/Icons/module_info.svg',
        'inheritNavigationComponentFromMainModule' => false,
        'routes' => [
            '_default' => [
                'target' => \B13\Umami\Controller\StatisticsController::class . '::handle',
            ],
        ],
    ],
];
