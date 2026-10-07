<?php

declare(strict_types=1);

$stubs = require __DIR__ . '/../../vendor/xepozz/internal-mocker/src/stubs.php';

if (is_array($stubs) === false) {
    return [];
}

$stubs['exif_read_data'] = [
    'signatureArguments' => '$file, string|null $required_sections = null, bool $as_arrays = false, '
        . 'bool $read_thumbnail = false',
    'arguments' => '$file, $required_sections, $as_arrays, $read_thumbnail',
];

$stubs['file_put_contents'] = [
    'signatureArguments' => 'string $filename, mixed $data, int $flags = 0, $context = null',
    'arguments' => '$filename, $data, $flags, $context',
];

$stubs['fopen'] = [
    'signatureArguments' => 'string $filename, string $mode, bool $use_include_path = false, $context = null',
    'arguments' => '$filename, $mode, $use_include_path, $context',
];

$stubs['imagecrop'] = [
    'signatureArguments' => '\GdImage $image, array $rectangle',
    'arguments' => '$image, $rectangle',
];

$stubs['imagerotate'] = [
    'signatureArguments' => '\GdImage $image, float $angle, int $background_color, bool $ignore_transparent = false',
    'arguments' => '$image, $angle, $background_color, $ignore_transparent',
];

$stubs['imagewebp'] = [
    'signatureArguments' => '\GdImage $image, $file = null, int $quality = -1',
    'arguments' => '$image, $file, $quality',
];

return $stubs;
