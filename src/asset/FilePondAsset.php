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
    /**
     * Returns the npm package that ships the FilePond core library.
     *
     * @return string npm package name.
     */
    protected function package(): string
    {
        return 'filepond';
    }

    /**
     * Returns the FilePond core script file name.
     *
     * @return list<string> Script file names.
     */
    protected function scripts(): array
    {
        return ['filepond.js'];
    }

    /**
     * Returns the FilePond core stylesheet file name.
     *
     * @return list<string> Stylesheet file names.
     */
    protected function styles(): array
    {
        return ['filepond.css'];
    }
}
