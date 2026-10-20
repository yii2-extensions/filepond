<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\tests\support\stub;

use yii2\extensions\filepond\asset\AbstractLocalAsset;

/**
 * Local asset bundle with dotted and extensionless file names for minified name resolution and publish list tests.
 */
final class DottedLocalAsset extends AbstractLocalAsset
{
    public $sourcePath = __DIR__;

    protected function scripts(): array
    {
        return ['worker', 'filepond.cropper.js'];
    }

    protected function styles(): array
    {
        return ['filepond.cropper.theme.css'];
    }
}
