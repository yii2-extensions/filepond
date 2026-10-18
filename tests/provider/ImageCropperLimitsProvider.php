<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\tests\provider;

use yii2\extensions\filepond\tests\file\ImageCropperLimitsTest;

/**
 * Data provider for {@see ImageCropperLimitsTest} processing limits and supported format cases.
 */
final class ImageCropperLimitsProvider
{
    /**
     * @return iterable<string, array{int, int}>
     */
    public static function decodedDimensionLimits(): iterable
    {
        yield 'pixel limit' => [1, 16];
        yield 'dimension limit' => [128, 1];
    }

    /**
     * @return iterable<string, array{list<float|int>}>
     */
    public static function invalidSourceDimensions(): iterable
    {
        yield 'missing dimensions' => [[]];
        yield 'missing height' => [[16]];
        yield 'zero width' => [[0, 8]];
        yield 'zero height' => [[16, 0]];
        yield 'negative width' => [[-1, 8]];
        yield 'negative height' => [[16, -1]];
        yield 'float width' => [[16.0, 8]];
        yield 'float height' => [[16, 8.0]];
    }

    /**
     * @return iterable<string, array{array<array-key, int>}>
     */
    public static function missingBudgets(): iterable
    {
        yield 'no arguments' => [[]];
        yield 'positional quality only' => [[85]];
        yield 'named quality only' => [['quality' => 85]];
        yield 'missing maxPixels' => [['maxDimension' => 16, 'maxInputBytes' => 65_536]];
        yield 'missing maxDimension' => [['maxPixels' => 128, 'maxInputBytes' => 65_536]];
        yield 'missing maxInputBytes' => [['maxPixels' => 128, 'maxDimension' => 16]];
    }

    /**
     * @return iterable<string, array{int, int}>
     */
    public static function narrowSourceDimensions(): iterable
    {
        yield 'tall source' => [1, 128];
        yield 'wide source' => [128, 1];
    }

    /**
     * @return iterable<string, array{int, int, int, 'maxPixels'|'maxDimension'|'maxInputBytes'}>
     */
    public static function nonPositiveBudgets(): iterable
    {
        yield 'zero maxPixels' => [0, 16, 65_536, 'maxPixels'];
        yield 'negative maxPixels' => [-1, 16, 65_536, 'maxPixels'];
        yield 'zero maxDimension' => [128, 0, 65_536, 'maxDimension'];
        yield 'negative maxDimension' => [128, -1, 65_536, 'maxDimension'];
        yield 'zero maxInputBytes' => [128, 16, 0, 'maxInputBytes'];
        yield 'negative maxInputBytes' => [128, 16, -1, 'maxInputBytes'];
    }

    /**
     * @return iterable<string, array{array{maxPixels: int|null, maxDimension: int|null, maxInputBytes: int|null}}>
     */
    public static function nullBudgets(): iterable
    {
        yield 'null maxPixels' => [['maxPixels' => null, 'maxDimension' => 16, 'maxInputBytes' => 65_536]];
        yield 'null maxDimension' => [['maxPixels' => 128, 'maxDimension' => null, 'maxInputBytes' => 65_536]];
        yield 'null maxInputBytes' => [['maxPixels' => 128, 'maxDimension' => 16, 'maxInputBytes' => null]];
    }

    /**
     * @return iterable<string, array{'png'|'jpeg'|'gif'|'webp'|'bmp'|'wbmp'|'avif'}>
     */
    public static function supportedFormats(): iterable
    {
        yield 'png' => ['png'];
        yield 'jpeg' => ['jpeg'];
        yield 'gif' => ['gif'];
        yield 'webp' => ['webp'];
        yield 'bmp' => ['bmp'];
        yield 'wbmp' => ['wbmp'];
        yield 'avif' => ['avif'];
    }
}
