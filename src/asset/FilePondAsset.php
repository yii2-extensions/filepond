<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset;

/**
 * Delivers the FilePond core library.
 *
 * @see https://pqina.nl/filepond/
 */
final class FilePondAsset extends AbstractNpmAsset
{
    protected function package(): string
    {
        return 'filepond';
    }

    protected function scripts(): array
    {
        return ['filepond.js'];
    }

    protected function styles(): array
    {
        return ['filepond.css'];
    }
}
