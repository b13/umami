<?php

defined('TYPO3') or die('Access denied!');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
    'site',
    'umami',
    '',
    null,
    [
        'name' => 'site_umami',
        'routeTarget' => \B13\Umami\Controller\StatisticsController::class . '::handle',
        'access' => 'user,group',
        'icon' => 'EXT:umami/Resources/Public/Icons/module_info.svg',
        'labels' => 'LLL:EXT:umami/Resources/Private/Language/locallang_mod.xlf',
    ]
);
