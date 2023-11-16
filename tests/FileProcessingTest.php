<?php

declare(strict_types=1);

namespace PHPForge\FilePond\Tests\Helper;

use JsonException;
use PHPForge\Support\Assert;
use PHPUnit\Framework\TestCase;
use Yii\FilePond\FileProcessing;

use function json_encode;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class FileProcessingTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Assert::removeFilesFromDirectory(__DIR__ . '/Support/runtime/');
    }

    /**
     * @throws JsonException
     */
    public function testSave(): void
    {
        FileProcessing::save(
            [
                0 => json_encode(
                    [
                        'id' => 'opqgdavos',
                        'name' => 'test.txt',
                        'type' => 'text/plain',
                        'size' => 7,
                        'metadata' => [],
                        'data' => 'VGVzdE1lCg==',
                    ],
                    JSON_THROW_ON_ERROR,
                ),
            ],
            __DIR__ . '/Support/runtime/',
        );

        $this->assertFileExists(__DIR__ . '/Support/runtime/test.txt');
    }

    /**
     * @throws JsonException
     */
    public function testSaveWithEmptyData(): void
    {
        FileProcessing::save(
            [
                0 => json_encode([], JSON_THROW_ON_ERROR),
            ],
            __DIR__ . '/Support/runtime',
        );

        $this->assertFileDoesNotExist(__DIR__ . '/Support/runtime/test.txt');
    }

    /**
     * @throws JsonException
     */
    public function testSaveWithReturningFile(): void
    {
        $files = FileProcessing::saveWithReturningFile(
            [
                0 => json_encode(
                    [
                        'id' => 'opqgdavos',
                        'name' => 'test.txt',
                        'type' => 'text/plain',
                        'size' => 7,
                        'metadata' => [],
                        'data' => 'VGVzdE1lCg==',
                    ],
                    JSON_THROW_ON_ERROR,
                ),
            ],
            __DIR__ . '/Support/runtime/',
            'category',
            false,
        );

        $this->assertFileExists(__DIR__ . '/Support/runtime/category.txt');
        $this->assertSame('category.txt', $files);
    }

    /**
     * @throws JsonException
     */
    public function testSaveWithReturningFiles(): void
    {
        $files = FileProcessing::saveWithReturningFiles(
            [
                0 => json_encode(
                    [
                        'id' => 'opqgdavos',
                        'name' => 'test.txt',
                        'type' => 'text/plain',
                        'size' => 7,
                        'metadata' => [],
                        'data' => 'VGVzdE1lCg==',
                    ],
                    JSON_THROW_ON_ERROR,
                ),
                1 => json_encode(
                    [
                        'id' => 'opqgdavo2',
                        'name' => 'test1.txt',
                        'type' => 'text/plain',
                        'size' => 7,
                        'metadata' => [],
                        'data' => 'VGVzdE1lCg==',
                    ],
                    JSON_THROW_ON_ERROR,
                ),
            ],
            __DIR__ . '/Support/runtime/',
        );

        $this->assertFileExists(__DIR__ . '/Support/runtime/test.txt');
        $this->assertFileExists(__DIR__ . '/Support/runtime/test1.txt');
        $this->assertSame(
            [
                __DIR__ . '/Support/runtime/test.txt',
                __DIR__ . '/Support/runtime/test1.txt',
            ],
            $files,
        );
    }

    /**
     * @throws JsonException
     */
    public function testSaveWithReturningFilesEmptyData(): void
    {
        $files = FileProcessing::saveWithReturningFiles(
            [
                0 => json_encode([], JSON_THROW_ON_ERROR),
            ],
            __DIR__ . '/Support/runtime/',
        );

        $this->assertFileDoesNotExist(__DIR__ . '/Support/runtime/test.txt');
        $this->assertSame([], $files);
    }

    public function testSaveWithReturningFilesWithoutPath(): void
    {
        $files = FileProcessing::saveWithReturningFiles(
            [
                0 => json_encode(
                    [
                        'id' => 'opqgdavos',
                        'name' => 'test.txt',
                        'type' => 'text/plain',
                        'size' => 7,
                        'metadata' => [],
                        'data' => 'VGVzdE1lCg==',
                    ],
                    JSON_THROW_ON_ERROR,
                ),
                1 => json_encode(
                    [
                        'id' => 'opqgdavo2',
                        'name' => 'test1.txt',
                        'type' => 'text/plain',
                        'size' => 7,
                        'metadata' => [],
                        'data' => 'VGVzdE1lCg==',
                    ],
                    JSON_THROW_ON_ERROR,
                ),
            ],
            __DIR__ . '/Support/runtime/',
            withPath: false,
        );

        $this->assertFileExists(__DIR__ . '/Support/runtime/test.txt');
        $this->assertFileExists(__DIR__ . '/Support/runtime/test1.txt');
        $this->assertSame(['test.txt', 'test1.txt'], $files);
    }
}
