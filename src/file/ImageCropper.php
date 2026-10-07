<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\file;

use GdImage;
use RuntimeException;
use yii\helpers\FileHelper;
use yii2\extensions\filepond\exception\Message;

use function base64_encode;
use function imagecreatefromstring;
use function imageflip;
use function imagegif;
use function imagejpeg;
use function imagepng;
use function imagesavealpha;
use function imagesx;
use function imagesy;
use function in_array;
use function is_array;
use function is_int;
use function max;
use function min;
use function ob_get_clean;
use function ob_start;
use function pathinfo;

/**
 * Applies the crop rectangle stored by the Cropper.js editor to an image on the server with GD.
 *
 * Use it when the Image Transform plugin is disabled, so the browser uploads the original image and the server
 * produces the cropped file from `metadata.crop.rect`. JPEG orientation metadata is honored before cropping when the
 * `exif` extension is available, matching the orientation the editor displayed.
 */
final readonly class ImageCropper
{
    /**
     * EXIF orientations that mirror the image horizontally before rotating.
     */
    private const array FLIPPED_ORIENTATIONS = [2, 4, 5, 7];
    /**
     * Extension given to the output file for each encodable MIME type; other types are encoded as PNG.
     */
    private const array OUTPUT_EXTENSIONS = [
        'image/gif' => 'gif',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    /**
     * Counterclockwise rotation angle, in degrees, for each EXIF orientation that rotates the image, applied after
     * the horizontal mirror of {@see FLIPPED_ORIENTATIONS}.
     */
    private const array ROTATION_ANGLES = [3 => 180, 4 => 180, 5 => 90, 6 => -90, 7 => -90, 8 => 90];

    /**
     * @param int $quality Output quality for lossy formats, from `0` to `100`.
     *
     * @throws RuntimeException if the `gd` extension is not loaded.
     */
    public function __construct(private int $quality = 90)
    {
        if (!extension_loaded('gd')) {
            throw new RuntimeException(
                Message::GD_EXTENSION_REQUIRED->getMessage(),
            );
        }
    }

    /**
     * Returns a copy of the file cropped to its {@see EncodedFile::getCropRectangle()} rectangle.
     *
     * The rectangle is intersected with the image bounds, keeping at least the nearest edge pixel when they do not
     * overlap. GIF, JPEG, PNG, and WebP keep their format; other formats, such as BMP, are encoded as PNG and the copy
     * receives the `image/png` type and a `.png` extension. Files without a rectangle are returned unchanged.
     *
     * @param EncodedFile $file Image file carrying crop metadata.
     *
     * @throws RuntimeException if the image cannot be decoded or encoded.
     *
     * @return EncodedFile Cropped copy, or the same instance when no crop rectangle is present.
     */
    public function crop(EncodedFile $file): EncodedFile
    {
        $rect = $file->getCropRectangle();

        if ($rect === null) {
            return $file;
        }

        $image = @imagecreatefromstring($file->getData());

        if ($image === false) {
            throw new RuntimeException(
                Message::IMAGE_DECODE_FAILED->getMessage($file->name),
            );
        }

        $mimeType = $file->getMimeType();

        $image = $this->applyOrientation($image, $file->getData(), $mimeType);

        $width = imagesx($image);
        $height = imagesy($image);
        $x = max(0, min($rect['x'], $width - 1));
        $y = max(0, min($rect['y'], $height - 1));

        $bounds = [
            'x' => $x,
            'y' => $y,
            'width' => max(1, min($rect['x'] + $rect['width'], $width) - $x),
            'height' => max(1, min($rect['y'] + $rect['height'], $height) - $y),
        ];

        $cropped = imagecrop($image, $bounds);

        if ($cropped === false) {
            throw new RuntimeException(
                Message::IMAGE_DECODE_FAILED->getMessage($file->name),
            );
        }

        $extension = self::OUTPUT_EXTENSIONS[$mimeType] ?? 'png';
        $outputType = $extension === 'png' ? 'image/png' : $mimeType;

        return $file->withData(
            $this->encode($cropped, $outputType, $file->name),
            $outputType,
            self::outputName($file, $outputType, $extension),
        );
    }

    /**
     * Rotates and flips the image according to its EXIF orientation tag.
     */
    private function applyOrientation(GdImage $image, string $data, string $mimeType): GdImage
    {
        if ($mimeType !== 'image/jpeg' || !function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data('data://image/jpeg;base64,' . base64_encode($data));
        $orientation = is_array($exif) ? ($exif['Orientation'] ?? null) : null;

        if (in_array($orientation, self::FLIPPED_ORIENTATIONS, true)) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        }

        $angle = is_int($orientation) ? (self::ROTATION_ANGLES[$orientation] ?? 0) : 0;
        // Right-angle rotations never expose the background color. @infection-ignore-all
        $rotated = imagerotate($image, $angle, 0);

        return $rotated === false ? $image : $rotated;
    }

    /**
     * @throws RuntimeException if the image cannot be encoded as the given MIME type.
     */
    private function encode(GdImage $image, string $mimeType, string $name): string
    {
        ob_start();

        $encoded = match ($mimeType) {
            'image/gif' => imagegif($image),
            'image/jpeg' => imagejpeg($image, null, $this->quality),
            'image/webp' => function_exists('imagewebp') && imagewebp($image, null, $this->quality),
            default => self::encodePng($image),
        };

        $output = ob_get_clean();

        if ($encoded === false || $output === false) {
            throw new RuntimeException(
                Message::IMAGE_ENCODE_FAILED->getMessage($name, $mimeType),
            );
        }

        return $output;
    }

    private static function encodePng(GdImage $image): bool
    {
        imagesavealpha($image, true);

        return imagepng($image);
    }

    /**
     * Keeps the client name when its extension is registered for the output type, otherwise replaces the extension.
     */
    private static function outputName(EncodedFile $file, string $outputType, string $extension): string
    {
        if (in_array($file->getExtension(), FileHelper::getExtensionsByMimeType($outputType), true)) {
            return $file->name;
        }

        return pathinfo($file->name, PATHINFO_FILENAME) . ".{$extension}";
    }
}
