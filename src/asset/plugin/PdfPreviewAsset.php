<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset\plugin;

use yii2\extensions\filepond\asset\{AbstractPluginAsset, Plugin};

/**
 * Delivers the FilePond PDF Preview plugin.
 *
 * @see https://pqina.nl/filepond/docs/api/plugins/pdf-preview/
 */
final class PdfPreviewAsset extends AbstractPluginAsset
{
    public function plugin(): Plugin
    {
        return Plugin::PDF_PREVIEW;
    }
}
