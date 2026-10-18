<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\tests\file;

use ArgumentCountError;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\{DataProviderExternal, Group};
use ReflectionClass;
use RuntimeException;
use TypeError;
use yii2\extensions\filepond\exception\Message;
use yii2\extensions\filepond\file\{EncodedFile, ImageCropper};
use yii2\extensions\filepond\tests\provider\ImageCropperLimitsProvider;
use yii2\extensions\filepond\tests\support\stub\MockerFunctions;
use yii2\extensions\filepond\tests\support\TestCase;

use function base64_encode;
use function crc32;
use function function_exists;
use function imagecreatetruecolor;
use function ob_get_clean;
use function ob_start;
use function pack;

/**
 * Tests required image-processing budgets, predecode rejection, and supported codec boundaries.
 *
 * {@see ImageCropperLimitsProvider} for processing limits and supported format cases.
 */
#[Group('file')]
final class ImageCropperLimitsTest extends TestCase
{
    #[DataProviderExternal(ImageCropperLimitsProvider::class, 'supportedFormats')]
    public function testCropAcceptsInclusiveLimitsForSupportedFormats(string $format): void
    {
        $file = self::image($format);
        $cropped = self::cropper(maxInputBytes: $file->getSize())->crop($file);

        $dimensions = getimagesizefromstring($cropped->getData());

        self::assertNotFalse(
            $dimensions,
            'The admitted file must remain decodable.',
        );
        self::assertSame(
            [4, 2],
            [$dimensions[0], $dimensions[1]],
            'The configured crop must preserve its dimensions.',
        );
    }

    public function testCropWithoutRectangleDoesNotParseOrDecode(): void
    {
        $file = EncodedFile::fromArray(['name' => 'unchanged.bin', 'data' => base64_encode('not an image')]);

        self::assertSame(
            $file,
            self::cropper(1, 1, 1)->crop($file),
            'A no-op must return the original file.',
        );
        self::assertNull(
            MockerFunctions::argumentType('getimagesizefromstring'),
            'A no-op must not inspect the header.',
        );
        self::assertNull(
            MockerFunctions::argumentType('imagecreatefromstring'),
            'A no-op must not decode the image.',
        );
    }

    /**
     * @param array<array-key, int> $arguments Incomplete constructor configuration.
     */
    #[DataProviderExternal(ImageCropperLimitsProvider::class, 'missingBudgets')]
    public function testThrowArgumentCountErrorWhenAnyBudgetIsMissing(array $arguments): void
    {
        $this->expectException(ArgumentCountError::class);

        (new ReflectionClass(ImageCropper::class))->newInstanceArgs($arguments);
    }

    #[DataProviderExternal(ImageCropperLimitsProvider::class, 'nonPositiveBudgets')]
    public function testThrowInvalidArgumentExceptionWhenBudgetIsNotPositive(
        int $maxPixels,
        int $maxDimension,
        int $maxInputBytes,
        string $limit,
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(Message::INVALID_IMAGE_PROCESSING_LIMIT->getMessage($limit));

        self::cropper($maxPixels, $maxDimension, $maxInputBytes);
    }

    #[DataProviderExternal(ImageCropperLimitsProvider::class, 'decodedDimensionLimits')]
    public function testThrowRuntimeExceptionWhenDecodedDimensionsExceedLimits(int $maxPixels, int $maxDimension): void
    {
        MockerFunctions::override('getimagesizefromstring', [1, 1]);

        $file = self::image('png');

        $message = $maxPixels === 1 ? Message::IMAGE_PIXEL_LIMIT_EXCEEDED : Message::IMAGE_DIMENSION_LIMIT_EXCEEDED;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            $message->getMessage($file->name, 1),
        );

        try {
            self::cropper($maxPixels, $maxDimension)->crop($file);
        } finally {
            self::assertSame(
                'string',
                MockerFunctions::argumentType('imagecreatefromstring'),
                'The fixture must reach the post-decode guard.',
            );
            self::assertNull(
                MockerFunctions::argumentType('exif_read_data'),
                'An oversized result must not reach orientation processing.',
            );
        }
    }

    public function testThrowRuntimeExceptionWhenHeaderIsReadableButImageIsTruncated(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            Message::IMAGE_DECODE_FAILED->getMessage('probe.png'),
        );

        try {
            self::cropper()->crop(self::file(self::pngHeader(16, 8)));
        } finally {
            self::assertSame(
                'string',
                MockerFunctions::argumentType('imagecreatefromstring'),
                'GD must still verify image data.',
            );
        }
    }

    public function testThrowRuntimeExceptionWhenInputExceedsByteLimitBeforeHeaderParsing(): void
    {
        $file = self::image('png');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            Message::IMAGE_BYTE_LIMIT_EXCEEDED->getMessage($file->name, $file->getSize() - 1),
        );

        try {
            self::cropper(maxInputBytes: $file->getSize() - 1)->crop($file);
        } finally {
            self::assertNull(
                MockerFunctions::argumentType('getimagesizefromstring'),
                'The byte guard must precede header parsing.',
            );
            self::assertNull(
                MockerFunctions::argumentType('imagecreatefromstring'),
                'The byte guard must precede decoding.',
            );
        }
    }

    public function testThrowRuntimeExceptionWhenInvalidInputExceedsByteLimit(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            Message::IMAGE_BYTE_LIMIT_EXCEEDED->getMessage('probe.png', 2),
        );

        self::cropper(maxInputBytes: 2)->crop(self::file('not an image'));
    }

    #[DataProviderExternal(ImageCropperLimitsProvider::class, 'narrowSourceDimensions')]
    public function testThrowRuntimeExceptionWhenNarrowSourceExceedsDimensionLimit(int $width, int $height): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            Message::IMAGE_DIMENSION_LIMIT_EXCEEDED->getMessage('probe.png', 16),
        );

        try {
            self::cropper()->crop(self::file(self::pngHeader($width, $height)));
        } finally {
            self::assertNull(
                MockerFunctions::argumentType('imagecreatefromstring'),
                'The axis guard must precede decoding.',
            );
        }
    }

    public function testThrowRuntimeExceptionWhenPixelCountWouldOverflow(): void
    {
        $side = PHP_INT_SIZE === 8 ? 0xFFFFFFFF : PHP_INT_MAX;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            Message::IMAGE_PIXEL_LIMIT_EXCEEDED->getMessage('probe.png', PHP_INT_MAX),
        );

        try {
            self::cropper(PHP_INT_MAX, PHP_INT_MAX)->crop(self::file(self::pngHeader($side, $side)));
        } finally {
            self::assertNull(
                MockerFunctions::argumentType('imagecreatefromstring'),
                'Overflowing dimensions must not reach GD.',
            );
        }
    }

    /**
     * @param array<array-key, mixed> $dimensions Unexpected parser output.
     */
    #[DataProviderExternal(ImageCropperLimitsProvider::class, 'invalidSourceDimensions')]
    public function testThrowRuntimeExceptionWhenSourceDimensionsAreInvalid(array $dimensions): void
    {
        MockerFunctions::override('getimagesizefromstring', $dimensions);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            Message::IMAGE_DECODE_FAILED->getMessage('probe.png'),
        );

        try {
            self::cropper()->crop(self::file('parser fixture'));
        } finally {
            self::assertNull(
                MockerFunctions::argumentType('imagecreatefromstring'),
                'Invalid dimensions must not reach GD.',
            );
        }
    }

    #[DataProviderExternal(ImageCropperLimitsProvider::class, 'supportedFormats')]
    public function testThrowRuntimeExceptionWhenSourceExceedsPixelLimitForTinyCrop(string $format): void
    {
        $file = self::image($format);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            Message::IMAGE_PIXEL_LIMIT_EXCEEDED->getMessage($file->name, 127),
        );

        try {
            self::cropper(maxPixels: 127)->crop($file);
        } finally {
            self::assertNull(
                MockerFunctions::argumentType('imagecreatefromstring'),
                'A small output must not bypass the source budget.',
            );
        }
    }

    /**
     * @param array<string, int|null> $arguments Constructor configuration with an explicit `null` budget.
     */
    #[DataProviderExternal(ImageCropperLimitsProvider::class, 'nullBudgets')]
    public function testThrowTypeErrorWhenBudgetIsNull(array $arguments): void
    {
        $this->expectException(TypeError::class);

        (new ReflectionClass(ImageCropper::class))->newInstanceArgs($arguments);
    }

    private static function cropper(int $maxPixels = 128, int $maxDimension = 16, int $maxInputBytes = 65_536): ImageCropper
    {
        return new ImageCropper(maxPixels: $maxPixels, maxDimension: $maxDimension, maxInputBytes: $maxInputBytes);
    }

    private static function file(string $data, string $name = 'probe.png'): EncodedFile
    {
        return EncodedFile::fromArray([
            'name' => $name,
            'data' => base64_encode($data),
            'metadata' => ['crop' => ['rect' => ['x' => 0, 'y' => 0, 'width' => 4, 'height' => 2]]],
        ]);
    }

    private static function image(string $format): EncodedFile
    {
        $encoder = 'image' . $format;

        if (!function_exists($encoder)) {
            self::markTestSkipped("The installed GD build has no {$format} encoder.");
        }

        $image = imagecreatetruecolor(16, 8);

        ob_start();

        $encoded = @$encoder($image);
        $data = ob_get_clean();

        if ($encoded === false) {
            self::markTestSkipped(
                "The installed GD build cannot encode {$format}.",
            );
        }

        self::assertIsString(
            $data,
            'The fixture encoder must produce bytes.',
        );

        return self::file($data, 'probe.' . $format);
    }

    /**
     * Header-only fixtures exercise resource rejection without allocating the declared image.
     */
    private static function pngHeader(int $width, int $height): string
    {
        $header = pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0);

        return "\x89PNG\r\n\x1a\n" . pack('N', 13) . 'IHDR' . $header . pack('N', crc32("IHDR{$header}"));
    }
}
