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
    /**
     * Returns the Image EXIF Orientation plugin delivered by this bundle.
     *
     * @return Plugin Delivered plugin.
     */
    public function plugin(): Plugin
    {
        return Plugin::IMAGE_EXIF_ORIENTATION;
    }
}
