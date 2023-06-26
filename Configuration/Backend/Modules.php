<?php

return [
    'web_umami' => [
        'parent' => 'web',
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/umami',
        'labels' => 'LLL:EXT:umami/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'Umami',
        'icon' => 'EXT:umami/Resources/Public/Icons/module_info.svg',
        'routes' => [
            '_default' => [
                'target' => \B13\Umami\Controller\StatisticsController::class . '::handle',
            ],
        ],
    ],
];
