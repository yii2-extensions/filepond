<?php

declare(strict_types=1);

use yii\i18n\PhpMessageSource;

return [
    'components' => [
        'i18n' => [
            'translations' => [
                'yii.filepond' => [
                    'class' => PhpMessageSource::class,
                ],
            ],
        ],
    ],
];
