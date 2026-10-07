<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\tests\support\stub;

use GdImage;

use function array_key_exists;
use function is_int;
use function is_string;

/**
 * Stateful stub for internal functions mocked in the `file` namespace.
 *
 * Each function delegates to the native implementation unless a test registers an override through
 * {@see MockerFunctions::override()}.
 */
final class MockerFunctions
{
    /**
     * @var array<string, mixed> Return values keyed by function name.
     */
    private static array $overrides = [];

    /**
     * @return array<string, mixed>|false
     */
    public static function exif_read_data(mixed $file): array|false
    {
        if (array_key_exists('exif_read_data', self::$overrides)) {
            /** @var array<string, mixed>|false */
            return self::$overrides['exif_read_data'];
        }

        /** @var array<string, mixed>|false */
        return is_string($file) ? @\exif_read_data($file) : false;
    }

    public static function extension_loaded(string $extension): bool
    {
        if (array_key_exists('extension_loaded', self::$overrides)) {
            return (bool) self::$overrides['extension_loaded'];
        }

        return \extension_loaded($extension);
    }

    public static function file_put_contents(string $filename, mixed $data, int $flags): int|false
    {
        if (array_key_exists('file_put_contents', self::$overrides)) {
            /** @var int|false */
            return self::$overrides['file_put_contents'];
        }

        return \file_put_contents($filename, $data, $flags);
    }

    public static function function_exists(string $function): bool
    {
        $key = "function_exists:{$function}";

        if (array_key_exists($key, self::$overrides)) {
            return (bool) self::$overrides[$key];
        }

        return \function_exists($function);
    }

    /**
     * @param array<array-key, mixed> $rectangle
     */
    public static function imagecrop(GdImage $image, array $rectangle): GdImage|false
    {
        if (array_key_exists('imagecrop', self::$overrides)) {
            /** @var GdImage|false */
            return self::$overrides['imagecrop'];
        }

        return \imagecrop(
            $image,
            [
                'x' => self::int($rectangle['x'] ?? 0),
                'y' => self::int($rectangle['y'] ?? 0),
                'width' => self::int($rectangle['width'] ?? 0),
                'height' => self::int($rectangle['height'] ?? 0),
            ],
        );
    }

    public static function imagerotate(GdImage $image, float $angle, int $backgroundColor): GdImage|false
    {
        if (array_key_exists('imagerotate', self::$overrides)) {
            /** @var GdImage|false */
            return self::$overrides['imagerotate'];
        }

        return \imagerotate($image, $angle, $backgroundColor);
    }

    public static function imagewebp(GdImage $image, mixed $file, int $quality): bool
    {
        if (array_key_exists('imagewebp', self::$overrides)) {
            return (bool) self::$overrides['imagewebp'];
        }

        return \imagewebp($image, is_string($file) ? $file : null, $quality);
    }

    public static function is_writable(string $filename): bool
    {
        if (array_key_exists('is_writable', self::$overrides)) {
            return (bool) self::$overrides['is_writable'];
        }

        return \is_writable($filename);
    }

    /**
     * Registers a fixed return value for a mocked function.
     *
     * Use `function_exists:<name>` to override the existence check of one specific function.
     */
    public static function override(string $function, mixed $value): void
    {
        self::$overrides[$function] = $value;
    }

    /**
     * Removes every registered override.
     */
    public static function reset(): void
    {
        self::$overrides = [];
    }

    private static function int(mixed $value): int
    {
        return is_int($value) ? $value : 0;
    }
}
