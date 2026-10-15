<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\asset;

use yii2\extensions\filepond\asset\plugin\{
    FileEncodeAsset,
    FilePosterAsset,
    FileRenameAsset,
    FileValidateSizeAsset,
    FileValidateTypeAsset,
    ImageCropAsset,
    ImageEditAsset,
    ImageExifOrientationAsset,
    ImagePreviewAsset,
    ImageTransformAsset,
    PdfPreviewAsset,
};

use function in_array;
use function str_replace;
use function strtolower;

/**
 * Represents the FilePond plugins supported by the widget.
 *
 * The case value is the global JavaScript identifier under which the plugin registers itself, as expected by
 * `FilePond.registerPlugin()`.
 *
 * @see https://pqina.nl/filepond/docs/api/plugins/
 */
enum Plugin: string
{
    /**
     * File Encode plugin, enabled by `allowFileEncode`: submits files as base64 JSON payloads.
     */
    case FILE_ENCODE = 'FilePondPluginFileEncode';

    /**
     * File Poster plugin, enabled by `allowFilePoster`: renders `metadata.poster` images inside items.
     */
    case FILE_POSTER = 'FilePondPluginFilePoster';

    /**
     * File Rename plugin, enabled by `allowFileRename`: renames files through `fileRenameFunction`.
     */
    case FILE_RENAME = 'FilePondPluginFileRename';

    /**
     * File Validate Size plugin, enabled by `allowFileSizeValidation`: validates file sizes on the client.
     */
    case FILE_VALIDATE_SIZE = 'FilePondPluginFileValidateSize';

    /**
     * File Validate Type plugin, enabled by `allowFileTypeValidation`: validates file types on the client.
     */
    case FILE_VALIDATE_TYPE = 'FilePondPluginFileValidateType';

    /**
     * Image Crop plugin, enabled by `allowImageCrop`: crops previews to `imageCropAspectRatio`.
     */
    case IMAGE_CROP = 'FilePondPluginImageCrop';

    /**
     * Image Edit plugin, enabled by `allowImageEdit`: opens the Cropper.js editor.
     */
    case IMAGE_EDIT = 'FilePondPluginImageEdit';

    /**
     * Image EXIF Orientation plugin, enabled by `allowImageExifOrientation`: corrects orientation from EXIF data.
     */
    case IMAGE_EXIF_ORIENTATION = 'FilePondPluginImageExifOrientation';

    /**
     * Image Preview plugin, enabled by `allowImagePreview`: renders image previews.
     */
    case IMAGE_PREVIEW = 'FilePondPluginImagePreview';

    /**
     * Image Transform plugin, enabled by `allowImageTransform`: applies crop, resize, and output transforms before
     * submission.
     */
    case IMAGE_TRANSFORM = 'FilePondPluginImageTransform';

    /**
     * PDF Preview plugin, enabled by `allowPdfPreview`: renders PDF previews.
     */
    case PDF_PREVIEW = 'FilePondPluginPdfPreview';

    /**
     * Returns the asset bundle class that delivers the plugin files.
     *
     * @return class-string<AbstractPluginAsset> Asset bundle class for the plugin.
     */
    public function assetClass(): string
    {
        return match ($this) {
            self::FILE_ENCODE => FileEncodeAsset::class,
            self::FILE_POSTER => FilePosterAsset::class,
            self::FILE_RENAME => FileRenameAsset::class,
            self::FILE_VALIDATE_SIZE => FileValidateSizeAsset::class,
            self::FILE_VALIDATE_TYPE => FileValidateTypeAsset::class,
            self::IMAGE_CROP => ImageCropAsset::class,
            self::IMAGE_EDIT => ImageEditAsset::class,
            self::IMAGE_EXIF_ORIENTATION => ImageExifOrientationAsset::class,
            self::IMAGE_PREVIEW => ImagePreviewAsset::class,
            self::IMAGE_TRANSFORM => ImageTransformAsset::class,
            self::PDF_PREVIEW => PdfPreviewAsset::class,
        };
    }

    /**
     * Returns whether the plugin ships a stylesheet in its distribution.
     *
     * @return bool `true` when the plugin distribution includes a CSS file.
     */
    public function hasStyles(): bool
    {
        return in_array($this, [self::FILE_POSTER, self::IMAGE_EDIT, self::IMAGE_PREVIEW, self::PDF_PREVIEW], true);
    }

    /**
     * Returns the FilePond option that enables or disables the plugin.
     *
     * @return string FilePond boolean option name.
     */
    public function option(): string
    {
        return match ($this) {
            self::FILE_ENCODE => 'allowFileEncode',
            self::FILE_POSTER => 'allowFilePoster',
            self::FILE_RENAME => 'allowFileRename',
            self::FILE_VALIDATE_SIZE => 'allowFileSizeValidation',
            self::FILE_VALIDATE_TYPE => 'allowFileTypeValidation',
            self::IMAGE_CROP => 'allowImageCrop',
            self::IMAGE_EDIT => 'allowImageEdit',
            self::IMAGE_EXIF_ORIENTATION => 'allowImageExifOrientation',
            self::IMAGE_PREVIEW => 'allowImagePreview',
            self::IMAGE_TRANSFORM => 'allowImageTransform',
            self::PDF_PREVIEW => 'allowPdfPreview',
        };
    }

    /**
     * Returns the npm package name of the plugin.
     *
     * @return string npm package name.
     */
    public function package(): string
    {
        return 'filepond-plugin-' . strtolower(str_replace('_', '-', $this->name));
    }
}
