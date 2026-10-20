<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\tests\file;

use GdImage;
use PHPUnit\Framework\Attributes\{Group, RequiresOperatingSystem, TestWith};
use RuntimeException;
use yii2\extensions\filepond\exception\Message;
use yii2\extensions\filepond\file\{EncodedFile, ImageCropper};
use yii2\extensions\filepond\tests\support\stub\MockerFunctions;
use yii2\extensions\filepond\tests\support\TestCase;

use function base64_encode;
use function count;
use function imagebmp;
use function imagecolorallocate;
use function imagecolorallocatealpha;
use function imagecolorat;
use function imagecreatefromstring;
use function imagecreatetruecolor;
use function imagecrop;
use function imagefill;
use function imagefilledrectangle;
use function imagegif;
use function imagejpeg;
use function imagepng;
use function imagesx;
use function imagesy;
use function imagewebp;
use function ob_get_clean;
use function ob_start;
use function pack;
use function strlen;
use function substr;

/**
 * Unit tests for {@see ImageCropper} server-side cropping, EXIF orientation handling, and encoding with GD.
 */
#[Group('file')]
final class ImageCropperTest extends TestCase
{
    private const int HEIGHT = 8;

    /**
     * Expected size and probe pixels after applying each EXIF orientation to the 16x8 fixture whose top-left
     * quadrant is red. Values match `magick -auto-orient` on the same fixture tagged with each orientation.
     *
     * @var array<int, array{int, int, array{int, int}, array{int, int}}>
     */
    private const array ORIENTATIONS = [
        1 => [16, 8, [2, 1], [13, 6]],
        2 => [16, 8, [13, 1], [2, 6]],
        3 => [16, 8, [13, 6], [2, 1]],
        4 => [16, 8, [2, 6], [13, 1]],
        5 => [8, 16, [1, 3], [6, 12]],
        6 => [8, 16, [6, 2], [1, 13]],
        7 => [8, 16, [6, 12], [1, 3]],
        8 => [8, 16, [1, 13], [6, 2]],
    ];

    private const int WIDTH = 16;

    #[RequiresOperatingSystem('Linux|Darwin')]
    public function testCropAppliesEachExifOrientation(): void
    {
        $cropper = self::cropper();

        foreach (self::ORIENTATIONS as $orientation => [$width, $height, $red, $dark]) {
            MockerFunctions::override('exif_read_data', ['Orientation' => $orientation]);

            $image = self::decode($cropper->crop(self::image('jpeg', self::fullRect())));

            self::assertSame(
                $width,
                imagesx($image),
                "Orientation {$orientation} must yield width {$width}."
            );
            self::assertSame(
                $height,
                imagesy($image),
                "Orientation {$orientation} must yield height {$height}."
            );
            self::assertRed(
                $image,
                $red[0],
                $red[1],
                "Orientation {$orientation} must move the red quadrant."
            );
            self::assertDark(
                $image,
                $dark[0],
                $dark[1],
                "Orientation {$orientation} must move the dark area."
            );
        }
    }

    public function testCropClampsNegativeOriginToImageBounds(): void
    {
        $cropped = self::cropper()->crop(self::png(['x' => -4, 'y' => -2, 'width' => 10, 'height' => 5]));

        $image = self::decode($cropped);

        self::assertSame(
            6,
            imagesx($image),
            'Width must exclude the columns left of the image.',
        );
        self::assertSame(
            3,
            imagesy($image),
            'Height must exclude the rows above the image.',
        );
        self::assertRed(
            $image,
            0,
            0,
            'Origin pixel must come from the image, not from padding.',
        );
    }

    public function testCropClampsRectangleBeforeImageToFirstPixel(): void
    {
        $cropped = self::cropper()->crop(self::png(['x' => -20, 'y' => -20, 'width' => 5, 'height' => 5]));

        $image = self::decode($cropped);

        self::assertSame(
            1,
            imagesx($image),
            'Rectangle left of the image must keep the first column.',
        );
        self::assertSame(
            1,
            imagesy($image),
            'Rectangle above the image must keep the first row.',
        );
    }

    public function testCropClampsRectanglePositionToImageBounds(): void
    {
        $cropped = self::cropper()->crop(self::png(['x' => 20, 'y' => 20, 'width' => 10, 'height' => 10]));

        $image = self::decode($cropped);

        self::assertSame(
            1,
            imagesx($image),
            'Position past the right edge must keep the last column.',
        );
        self::assertSame(
            1,
            imagesy($image),
            'Position past the bottom edge must keep the last row.',
        );
        self::assertSame(
            127,
            (imagecolorat($image, 0, 0) >> 24) & 0x7F,
            'Kept pixel must be the transparent corner, not opaque padding.',
        );
    }

    public function testCropClampsRectangleSizeToImageBounds(): void
    {
        $cropped = self::cropper()->crop(self::png(['x' => 12, 'y' => 6, 'width' => 10, 'height' => 10]));

        $image = self::decode($cropped);

        self::assertSame(
            4,
            imagesx($image),
            'Width must stop at the right edge.',
        );
        self::assertSame(
            2,
            imagesy($image),
            'Height must stop at the bottom edge.',
        );
    }

    public function testCropEncodesGifJpegAndWebp(): void
    {
        $cropper = self::cropper(50);

        $rect = ['x' => 0, 'y' => 0, 'width' => 4, 'height' => 2];

        $gif = $cropper->crop(self::image('gif', $rect));
        $jpeg = $cropper->crop(self::image('jpeg', $rect));
        $webp = $cropper->crop(self::image('webp', $rect));

        self::assertSame(
            'image/gif',
            $gif->getMimeType(),
            'GIF must stay GIF.',
        );
        self::assertSame(
            'image/jpeg',
            $jpeg->getMimeType(),
            'JPEG must stay JPEG.',
        );
        self::assertSame(
            'image/webp',
            $webp->getMimeType(),
            'WebP must stay WebP.',
        );
        self::assertSame(
            4,
            imagesx(self::decode($gif)),
            'GIF must be cropped.',
        );
        self::assertSame(
            4,
            imagesx(self::decode($jpeg)),
            'JPEG must be cropped.',
        );
        self::assertSame(
            4,
            imagesx(self::decode($webp)),
            'WebP must be cropped.',
        );
    }

    public function testCropFallsBackToPngForUnknownTypes(): void
    {
        $cropped = self::cropper()->crop(self::image('bmp', ['x' => 0, 'y' => 0, 'width' => 1, 'height' => 1]));

        self::assertSame(
            'image/png',
            $cropped->getMimeType(),
            'Unsupported encoders must fall back to PNG.',
        );
        self::assertSame(
            'image/png',
            $cropped->type,
            'Type must describe the PNG bytes.',
        );
        self::assertSame(
            'image.png',
            $cropped->name,
            'Extension must describe the PNG bytes.',
        );
    }

    public function testCropIgnoresJpegWithoutExif(): void
    {
        $image = self::decode(self::cropper()->crop(self::image('jpeg', self::fullRect())));

        self::assertSame(
            self::WIDTH,
            imagesx($image),
            'Missing EXIF must keep the width.',
        );
        self::assertRed(
            $image,
            2,
            1,
            'Missing EXIF must keep the orientation.',
        );
    }

    public function testCropIgnoresNonIntegerOrientation(): void
    {
        $cropper = self::cropper();

        foreach (['6', '2', 6.0, true] as $tag) {
            MockerFunctions::override('exif_read_data', ['Orientation' => $tag]);

            $image = self::decode($cropper->crop(self::image('jpeg', self::fullRect())));

            self::assertSame(
                self::WIDTH,
                imagesx($image),
                'Non-integer tags must not rotate the image.',
            );
            self::assertRed(
                $image,
                2,
                1,
                'Non-integer tags must not flip the image.',
            );
        }
    }

    public function testCropIgnoresOrientationForNonJpegImages(): void
    {
        MockerFunctions::override('exif_read_data', ['Orientation' => 6]);

        $image = self::decode(self::cropper()->crop(self::png(self::fullRect())));

        self::assertSame(
            self::WIDTH,
            imagesx($image),
            'PNG images must not be rotated.',
        );
        self::assertRed(
            $image,
            2,
            1,
            'PNG images must keep the orientation.',
        );
    }

    public function testCropIgnoresOrientationWhenExifIsUnavailable(): void
    {
        MockerFunctions::override('function_exists:exif_read_data', false);
        MockerFunctions::override('exif_read_data', ['Orientation' => 6]);

        $image = self::decode(self::cropper()->crop(self::image('jpeg', self::fullRect())));

        self::assertSame(
            self::WIDTH,
            imagesx($image),
            'Rotation requires the EXIF extension.',
        );
    }

    public function testCropIgnoresOrientationWhenExifStreamCannotOpen(): void
    {
        MockerFunctions::override('exif_read_data', ['Orientation' => 6]);
        MockerFunctions::override('fopen', false);

        $image = self::decode(self::cropper()->crop(self::image('jpeg', self::fullRect())));

        self::assertSame(
            self::WIDTH,
            imagesx($image),
            'Orientation must be skipped without a stream.',
        );
    }

    public function testCropKeepsImageWhenRotationFails(): void
    {
        MockerFunctions::override('exif_read_data', ['Orientation' => 3]);
        MockerFunctions::override('imagerotate', false);

        $image = self::decode(self::cropper()->crop(self::image('jpeg', self::fullRect())));

        self::assertRed(
            $image,
            2,
            1,
            'Original image must be used when rotation fails.',
        );
    }

    public function testCropKeepsPngTransparencyAndMetadata(): void
    {
        $file = self::png(['x' => 8, 'y' => 0, 'width' => 8, 'height' => 8]);

        $cropped = self::cropper()->crop($file);

        $image = self::decode($cropped);

        self::assertSame(
            8,
            imagesx($image),
            'Width must match the rectangle.',
        );
        self::assertSame(
            8,
            imagesy($image),
            'Height must match the rectangle.',
        );
        self::assertSame(
            127,
            (imagecolorat($image, 1, 1) >> 24) & 0x7F,
            'Transparent pixels must be preserved.',
        );
        self::assertSame(
            'image/png',
            $cropped->type,
            'Client type must follow the detected MIME type.',
        );
        self::assertSame(
            $file->metadata,
            $cropped->metadata,
            'Metadata must be preserved.',
        );
        self::assertNotSame(
            $file,
            $cropped,
            'Cropping must return a new instance.',
        );
    }

    #[RequiresOperatingSystem('Linux')]
    public function testCropReadsExifFromStreamInsteadOfUrlWrapper(): void
    {
        $jpeg = self::withExif(self::encode('jpeg'), [0x0112 => 6]);

        $file = EncodedFile::fromArray(
            ['name' => 'a.jpg', 'data' => base64_encode($jpeg), 'metadata' => ['crop' => ['rect' => self::fullRect()]]],
        );

        $image = self::decode(self::cropper()->crop($file));

        self::assertSame(
            'resource (stream)',
            MockerFunctions::argumentType('exif_read_data'),
            'EXIF must not depend on `allow_url_fopen`.',
        );
        self::assertSame(
            self::HEIGHT,
            imagesx($image),
            'Orientation read from the stream must apply.',
        );
    }

    public function testCropReadsExifWithoutOrientationTag(): void
    {
        $jpeg = self::withExif(self::encode('jpeg'), [0x0110 => 1]);

        $file = EncodedFile::fromArray(
            ['name' => 'a.jpg', 'data' => base64_encode($jpeg), 'metadata' => ['crop' => ['rect' => self::fullRect()]]],
        );

        $image = self::decode(self::cropper()->crop($file));

        self::assertSame(
            self::WIDTH,
            imagesx($image),
            'EXIF without orientation must keep the width.',
        );
        self::assertRed(
            $image,
            2,
            1,
            'EXIF without orientation must keep the orientation.',
        );
    }

    /**
     * @param int<1, 8> $orientation EXIF orientation stored in the JPEG fixture.
     */
    #[RequiresOperatingSystem('Linux')]
    #[TestWith([1, 16, 8])]
    #[TestWith([2, 16, 8])]
    #[TestWith([3, 16, 8])]
    #[TestWith([4, 16, 8])]
    #[TestWith([5, 8, 16])]
    #[TestWith([6, 8, 16])]
    #[TestWith([7, 8, 16])]
    #[TestWith([8, 8, 16])]
    public function testCropReadsRealExifOrientation(int $orientation, int $width, int $height): void
    {
        $jpeg = self::withExif(self::encode('jpeg'), [0x0112 => $orientation]);

        $file = EncodedFile::fromArray(
            ['name' => 'a.jpg', 'data' => base64_encode($jpeg), 'metadata' => ['crop' => ['rect' => self::fullRect()]]],
        );

        $image = self::decode(self::cropper()->crop($file));

        self::assertSame(
            [$width, $height],
            [imagesx($image), imagesy($image)],
            'The actual EXIF orientation must determine the dimensions.',
        );
        self::assertRed(
            $image,
            self::ORIENTATIONS[$orientation][2][0],
            self::ORIENTATIONS[$orientation][2][1],
            'The actual EXIF orientation must move the red quadrant.',
        );
    }

    public function testCropRenamesOnlyExtensionsThatDoNotMatchOutput(): void
    {
        $cropper = self::cropper();

        $mismatched = $cropper->crop(
            EncodedFile::fromArray(
                [
                    'name' => 'photo.jpg',
                    'data' => base64_encode(self::encode('png')),
                    'metadata' => ['crop' => ['rect' => self::fullRect()]],
                ],
            ),
        );
        $matched = $cropper->crop(self::image('jpeg', self::fullRect()));

        self::assertSame(
            'photo.png',
            $mismatched->name,
            'Extension must follow the detected content.',
        );
        self::assertSame(
            'image.jpeg',
            $matched->name,
            'Registered extension must be preserved.',
        );
    }

    public function testCropReturnsSameFileWithoutRectangle(): void
    {
        $file = EncodedFile::fromArray(['name' => 'a.png', 'data' => base64_encode(self::encode('png'))]);

        self::assertSame(
            $file,
            self::cropper()->crop($file),
            'Files without a rectangle must be untouched.',
        );
    }

    public function testCropUsesConfiguredJpegQuality(): void
    {
        $rect = ['x' => 0, 'y' => 0, 'width' => 8, 'height' => 4];

        $defaultCropper = (
            new ImageCropper(
                maxPixels: self::WIDTH * self::HEIGHT,
                maxDimension: self::WIDTH,
                maxInputBytes: 65_536,
            )
        );

        $default = $defaultCropper
            ->crop(self::image('jpeg', $rect))
            ->getData();

        $custom = self::cropper(50)->crop(self::image('jpeg', $rect))->getData();

        self::assertSame(
            self::reference($rect, 90),
            $default,
            "Default quality must be '90'.",
        );
        self::assertSame(
            self::reference($rect, 50),
            $custom,
            'Configured quality must be applied.',
        );
    }

    public function testThrowRuntimeExceptionWhenCropFails(): void
    {
        MockerFunctions::override('imagecrop', false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            Message::IMAGE_DECODE_FAILED->getMessage('image.png'),
        );

        self::cropper()->crop(self::png(['x' => 0, 'y' => 0, 'width' => 1, 'height' => 1]));
    }

    public function testThrowRuntimeExceptionWhenEncodingFails(): void
    {
        MockerFunctions::override('imagewebp', false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            Message::IMAGE_ENCODE_FAILED->getMessage('image.webp', 'image/webp'),
        );

        self::cropper()->crop(self::image('webp', ['x' => 0, 'y' => 0, 'width' => 1, 'height' => 1]));
    }

    public function testThrowRuntimeExceptionWhenGdIsMissing(): void
    {
        MockerFunctions::override('extension_loaded', false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            Message::GD_EXTENSION_REQUIRED->getMessage(),
        );

        self::cropper();
    }

    public function testThrowRuntimeExceptionWhenImageCannotBeDecoded(): void
    {
        $file = EncodedFile::fromArray(
            [
                'name' => 'broken.png',
                'data' => base64_encode('not an image'),
                'metadata' => ['crop' => ['rect' => ['x' => 0, 'y' => 0, 'width' => 1, 'height' => 1]]],
            ],
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            Message::IMAGE_DECODE_FAILED->getMessage('broken.png'),
        );

        self::cropper()->crop($file);
    }

    public function testThrowRuntimeExceptionWhenWebpEncoderIsUnavailable(): void
    {
        MockerFunctions::override('function_exists:imagewebp', false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            Message::IMAGE_ENCODE_FAILED->getMessage('image.webp', 'image/webp'),
        );

        self::cropper()->crop(self::image('webp', ['x' => 0, 'y' => 0, 'width' => 1, 'height' => 1]));
    }

    private static function assertDark(GdImage $image, int $x, int $y, string $message): void
    {
        $rgb = imagecolorat($image, $x, $y);

        self::assertLessThan(
            100,
            ($rgb >> 16) & 0xFF,
            $message,
        );
    }

    private static function assertRed(GdImage $image, int $x, int $y, string $message): void
    {
        $rgb = imagecolorat($image, $x, $y);

        self::assertGreaterThan(
            150,
            ($rgb >> 16) & 0xFF,
            $message,
        );
        self::assertLessThan(
            100,
            ($rgb >> 8) & 0xFF,
            $message,
        );
        self::assertLessThan(
            100,
            $rgb & 0xFF,
            $message,
        );
    }

    /**
     * Keeps the 16x8 fixtures inside an explicit test-only resource budget.
     */
    private static function cropper(int $quality = 90): ImageCropper
    {
        return new ImageCropper(
            maxPixels: self::WIDTH * self::HEIGHT,
            maxDimension: self::WIDTH,
            maxInputBytes: 65_536,
            quality: $quality,
        );
    }

    private static function decode(EncodedFile $file): GdImage
    {
        $image = imagecreatefromstring($file->getData());

        self::assertInstanceOf(
            GdImage::class,
            $image,
            'Output must be a decodable image.',
        );

        return $image;
    }

    /**
     * Encodes a 16x8 image with an opaque red top-left quadrant over a transparent background.
     */
    private static function encode(string $format, int $quality = 100): string
    {
        $image = imagecreatetruecolor(self::WIDTH, self::HEIGHT);

        self::assertInstanceOf(
            GdImage::class,
            $image,
            'Fixture image must be created.'
        );

        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, (int) imagecolorallocatealpha($image, 0, 0, 0, 127));
        imagefilledrectangle($image, 0, 0, 7, 3, (int) imagecolorallocate($image, 255, 0, 0));

        ob_start();

        match ($format) {
            'bmp' => imagebmp($image),
            'gif' => imagegif($image),
            'jpeg' => imagejpeg($image, null, $quality),
            'webp' => imagewebp($image),
            default => imagepng($image),
        };

        return (string) ob_get_clean();
    }

    /**
     * @return array{x: int, y: int, width: int, height: int}
     */
    private static function fullRect(): array
    {
        return ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100];
    }

    /**
     * @param array{x: int, y: int, width: int, height: int} $rect
     */
    private static function image(string $format, array $rect): EncodedFile
    {
        return EncodedFile::fromArray(
            [
                'name' => "image.{$format}",
                'data' => base64_encode(self::encode($format)),
                'metadata' => ['crop' => ['rect' => $rect]],
            ],
        );
    }

    /**
     * @param array{x: int, y: int, width: int, height: int} $rect
     */
    private static function png(array $rect): EncodedFile
    {
        return self::image('png', $rect);
    }

    /**
     * Crops the JPEG fixture with plain GD calls and encodes it with the given quality.
     *
     * @param array{x: int, y: int, width: int, height: int} $rect
     */
    private static function reference(array $rect, int $quality): string
    {
        $image = imagecreatefromstring(self::encode('jpeg'));

        self::assertInstanceOf(
            GdImage::class,
            $image,
            'Reference image must be decodable.',
        );

        $cropped = imagecrop($image, $rect);

        self::assertInstanceOf(
            GdImage::class,
            $cropped,
            'Reference crop must succeed.',
        );

        ob_start();
        imagejpeg($cropped, null, $quality);

        return (string) ob_get_clean();
    }

    /**
     * Inserts an APP1 EXIF segment with big-endian SHORT entries after the JPEG SOI marker.
     *
     * @param array<int, int> $entries Tag ids mapped to SHORT values.
     */
    private static function withExif(string $jpeg, array $entries): string
    {
        $ifd = pack('n', count($entries));

        foreach ($entries as $tag => $value) {
            $ifd .= pack('nnNnn', $tag, 3, 1, $value, 0);
        }

        $payload = "Exif\0\0" . 'MM' . pack('nN', 0x2A, 8) . $ifd . pack('N', 0);
        $segment = "\xFF\xE1" . pack('n', strlen($payload) + 2) . $payload;

        return substr($jpeg, 0, 2) . $segment . substr($jpeg, 2);
    }
}
