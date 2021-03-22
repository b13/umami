<?php
defined('TYPO3_MODE') or die('Access denied!');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'Umami',
    'web',
    'umami',
    '',
    [
        \B13\Umami\Controller\InfoController::class => 'list,show',
    ],
    [
        'access' => 'user,group',
        'icon' => 'EXT:umami/Resources/Public/Icons/module_info.svg',
        'labels' => 'LLL:EXT:umami/Resources/Private/Language/locallang_mod.xlf',
        'navigationComponentId' => '',
        'inheritNavigationComponentFromMainModule' => false
    ]
);
