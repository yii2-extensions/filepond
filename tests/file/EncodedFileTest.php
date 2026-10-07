<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\tests\file;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use yii2\extensions\filepond\exception\Message;
use yii2\extensions\filepond\file\EncodedFile;

use function base64_encode;
use function json_encode;
use function str_repeat;

/**
 * Unit tests for {@see EncodedFile} payload decoding and content inspection.
 */
#[Group('file')]
final class EncodedFileTest extends TestCase
{
    private const string PNG = "\x89PNG\r\n\x1a\n" . "\0\0\0\rIHDR\0\0\0\x01\0\0\0\x01\x08\x06\0\0\0\x1f\x15\xc4\x89";

    public function testCropRectangleIsNullWhenMissingOrInvalid(): void
    {
        $base = ['name' => 'a.png', 'data' => base64_encode(self::PNG)];

        self::assertNull(
            EncodedFile::fromArray($base)->getCropRectangle(),
            "Missing metadata yields 'null'.",
        );
        self::assertNull(
            EncodedFile::fromArray($base + ['metadata' => ['crop' => ['zoom' => 1]]])->getCropRectangle(),
            "Missing rect yields 'null'.",
        );
        self::assertNull(
            EncodedFile::fromArray($base + ['metadata' => ['crop' => ['rect' => 'x']]])->getCropRectangle(),
            "Non-array rect yields 'null'.",
        );
        self::assertNull(
            EncodedFile::fromArray(
                $base + ['metadata' => ['crop' => ['rect' => ['x' => '1', 'y' => 0, 'width' => 2, 'height' => 2]]]],
            )->getCropRectangle(),
            "Non-numeric coordinates yield 'null'.",
        );
        self::assertNull(
            EncodedFile::fromArray(
                $base + ['metadata' => ['crop' => ['rect' => ['x' => 0, 'y' => 0, 'width' => 0, 'height' => 2]]]],
            )->getCropRectangle(),
            "Empty rectangles yield 'null'.",
        );
        self::assertNull(
            EncodedFile::fromArray(
                $base + ['metadata' => ['crop' => ['rect' => ['x' => 0, 'y' => 0, 'width' => 2, 'height' => 0]]]],
            )->getCropRectangle(),
            "Flat rectangles yield 'null'.",
        );
    }

    public function testCropRectangleRoundsFloats(): void
    {
        $file = EncodedFile::fromArray(
            [
                'name' => 'a.png',
                'data' => base64_encode(self::PNG),
                'metadata' => ['crop' => ['rect' => ['x' => 1.4, 'y' => 2.6, 'width' => 10.5, 'height' => 7]]],
            ],
        );

        self::assertSame(
            ['x' => 1, 'y' => 3, 'width' => 11, 'height' => 7],
            $file->getCropRectangle(),
            'Rounded.',
        );
    }

    public function testFromArrayDecodesPayload(): void
    {
        $file = EncodedFile::fromArray(
            [
                'name' => 'Photo.JPG',
                'type' => 'image/jpeg',
                'size' => 3,
                'data' => base64_encode('abc'),
                'metadata' => ['poster' => '/p.jpg'],
            ],
        );

        self::assertSame(
            'Photo.JPG',
            $file->name,
            'Name must be kept verbatim.',
        );
        self::assertSame(
            'image/jpeg',
            $file->type,
            'Client type must be kept.',
        );
        self::assertSame(
            'abc',
            $file->getData(),
            'Content must be base64 decoded.',
        );
        self::assertSame(
            3,
            $file->getSize(),
            'Size must come from the decoded bytes.',
        );
        self::assertSame(
            'jpg',
            $file->getExtension(),
            'Extension must be lowercase.',
        );
        self::assertSame(
            ['poster' => '/p.jpg'],
            $file->metadata,
            'Metadata must be kept.',
        );
    }

    public function testFromArrayDefaultsOptionalKeys(): void
    {
        $file = EncodedFile::fromArray(['name' => 'notes', 'data' => base64_encode('text')]);

        self::assertSame(
            '',
            $file->type,
            'Type defaults to an empty string.',
        );
        self::assertSame(
            [],
            $file->metadata,
            'Metadata defaults to an empty array.',
        );
        self::assertSame(
            '',
            $file->getExtension(),
            'Missing extension yields an empty string.',
        );
    }

    public function testFromInputHandlesEmptyValues(): void
    {
        self::assertSame(
            [],
            EncodedFile::fromInput(null),
            "'null' yields no files.",
        );
        self::assertSame(
            [],
            EncodedFile::fromInput(''),
            'Empty string yields no files.',
        );
        self::assertSame(
            [],
            EncodedFile::fromInput([]),
            'Empty array yields no files.',
        );
        self::assertSame(
            [],
            EncodedFile::fromInput(['', null]),
            'Empty entries are skipped.',
        );
    }

    public function testFromInputHandlesStringsAndArrays(): void
    {
        $json = json_encode(['name' => 'a.txt', 'data' => base64_encode('a')], JSON_THROW_ON_ERROR);

        $single = EncodedFile::fromInput($json);
        $multiple = EncodedFile::fromInput([$json, '', ['name' => 'b.txt', 'data' => base64_encode('b')]]);

        self::assertCount(
            1,
            $single,
            'A JSON string yields one file.',
        );
        self::assertSame(
            'a.txt',
            $single[0]->name,
            'Single file must be decoded.',
        );
        self::assertCount(
            2,
            $multiple,
            'Lists yield one file per non-empty entry.',
        );
        self::assertSame(
            'b',
            $multiple[1]->getData(),
            'Decoded payloads must be accepted.',
        );
    }

    public function testMimeTypeIsDetectedFromContent(): void
    {
        $png = EncodedFile::fromArray(['name' => 'a.txt', 'type' => 'text/plain', 'data' => base64_encode(self::PNG)]);
        $text = EncodedFile::fromArray(['name' => 'a.png', 'data' => base64_encode(str_repeat('hello ', 10))]);

        self::assertSame(
            'image/png',
            $png->getMimeType(),
            'Detection must ignore the client type.',
        );
        self::assertSame(
            'text/plain',
            $text->getMimeType(),
            'Detection must ignore the extension.',
        );
    }

    public function testThrowInvalidArgumentExceptionForInvalidBase64(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            Message::ENCODED_FILE_INVALID_BASE64->getMessage(),
        );

        EncodedFile::fromArray(['name' => 'a.txt', 'data' => '***']);
    }

    public function testThrowInvalidArgumentExceptionForInvalidDataType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            Message::ENCODED_FILE_INVALID_KEY->getMessage('data', 'string'),
        );

        EncodedFile::fromArray(['name' => 'a.txt', 'data' => ['x']]);
    }

    public function testThrowInvalidArgumentExceptionForInvalidJson(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            Message::ENCODED_FILE_INVALID_JSON->getMessage('Syntax error'),
        );

        EncodedFile::fromJson('{');
    }

    public function testThrowInvalidArgumentExceptionForInvalidMetadataType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            Message::ENCODED_FILE_INVALID_KEY->getMessage('metadata', 'array'),
        );

        EncodedFile::fromArray(['name' => 'a.txt', 'data' => base64_encode('a'), 'metadata' => 'x']);
    }

    public function testThrowInvalidArgumentExceptionForInvalidNameType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            Message::ENCODED_FILE_INVALID_KEY->getMessage('name', 'string'),
        );

        EncodedFile::fromArray(['data' => base64_encode('a')]);
    }

    public function testThrowInvalidArgumentExceptionForInvalidTypeType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            Message::ENCODED_FILE_INVALID_KEY->getMessage('type', 'string'),
        );

        EncodedFile::fromArray(['name' => 'a.txt', 'type' => 1, 'data' => base64_encode('a')]);
    }

    public function testThrowInvalidArgumentExceptionForJsonScalar(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            Message::ENCODED_FILE_INVALID_PAYLOAD->getMessage('int'),
        );

        EncodedFile::fromJson('42');
    }

    public function testThrowInvalidArgumentExceptionForScalarInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            Message::ENCODED_FILE_INVALID_PAYLOAD->getMessage('int'),
        );

        EncodedFile::fromInput(42);
    }

    public function testThrowInvalidArgumentExceptionForScalarListEntry(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            Message::ENCODED_FILE_INVALID_PAYLOAD->getMessage('bool'),
        );

        EncodedFile::fromInput([true]);
    }

    public function testWithDataReplacesContentAndKeepsMetadata(): void
    {
        $file = EncodedFile::fromArray(
            ['name' => 'a.png', 'type' => 'image/png', 'data' => base64_encode('a'), 'metadata' => ['k' => 'v']],
        );

        $same = $file->withData('bb');
        $renamed = $file->withData('ccc', 'image/webp', 'a.webp');

        self::assertSame(
            'bb',
            $same->getData(),
            'Content must be replaced.',
        );
        self::assertSame(
            'a.png',
            $same->name,
            'Name must be kept by default.',
        );
        self::assertSame(
            'image/png',
            $same->type,
            'Type must be kept by default.',
        );
        self::assertSame(
            ['k' => 'v'],
            $same->metadata,
            'Metadata must be kept.',
        );
        self::assertSame(
            'a.webp',
            $renamed->name,
            'Name must be replaceable.',
        );
        self::assertSame(
            'image/webp',
            $renamed->type,
            'Type must be replaceable.',
        );
        self::assertSame(
            'a',
            $file->getData(),
            'Original must stay immutable.',
        );
    }
}
