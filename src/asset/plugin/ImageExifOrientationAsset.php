<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset\plugin;

use yii2\extensions\filepond\asset\{AbstractPluginAsset, Plugin};

/**
 * Delivers the FilePond Image EXIF Orientation plugin.
 *
 * @see https://pqina.nl/filepond/docs/api/plugins/image-exif-orientation/
 */
final class ImageExifOrientationAsset extends AbstractPluginAsset
{
    public function plugin(): Plugin
    {
        return Plugin::IMAGE_EXIF_ORIENTATION;
    }
}
