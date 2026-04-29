<?php

return [
    'insights' => [
        'labels' => 'LLL:EXT:umami/Resources/Private/Language/locallang_mod_insights.xlf',
        'iconIdentifier' => 'module-insights',
        'extensionName' => 'umami',
        'position' => ['after' => 'content'],
    ],
    'insights_umami' => [
        'parent' => 'insights',
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/umami',
        'labels' => 'LLL:EXT:umami/Resources/Private/Language/locallang_mod_umami.xlf',
        'extensionName' => 'Umami',
        'iconIdentifier' => 'module-umami',
        'inheritNavigationComponentFromMainModule' => false,
        'routes' => [
            '_default' => [
                'target' => \B13\Umami\Controller\StatisticsController::class . '::handle',
            ],
        ],
    ],
];
