<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Umami Tracking',
    'description' => 'Umami Tracking for your TYPO3 site',
    'category' => 'be',
    'version' => '2.0.0',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'author' => 'b13 GmbH',
    'author_email' => 'typo3@b13.com',
    'author_company' => 'b13 GmbH',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
