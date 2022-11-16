<?php

// Add new field to SiteConfiguration
$GLOBALS['SiteConfiguration']['site']['columns']['umami_statistic_url'] = [
    'label' => 'Statistic url',
    'config' => [
        'type' => 'input',
        'renderType' => 'inputLink',
    ],
];

// Position field in SiteConfiguration
$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] .= ',--div--;LLL:EXT:umami/Resources/Private/Language/locallang_db.xlf:umami.title,umami_statistic_url,';
