<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\tests\validator;

use PHPUnit\Framework\Attributes\Group;
use yii2\extensions\filepond\tests\support\stub\TestForm;
use yii2\extensions\filepond\tests\support\TestCase;
use yii2\extensions\filepond\validator\EncodedFileValidator;

use function base64_encode;
use function json_encode;

/**
 * Unit tests for {@see EncodedFileValidator} server-side payload validation.
 */
#[Group('validator')]
final class EncodedFileValidatorTest extends TestCase
{
    private const string PNG = "\x89PNG\r\n\x1a\n" . "\0\0\0\rIHDR\0\0\0\x01\0\0\0\x01\x08\x06\0\0\0\x1f\x15\xc4\x89";

    public function testAcceptsFilesAtSizeAndCountBoundaries(): void
    {
        $this->mockWebApplication();

        $validator = new EncodedFileValidator(['maxFiles' => 2, 'maxSize' => 5, 'minSize' => 5]);

        $payload = [self::payload('a.txt', 'hello'), self::payload('b.txt', 'world')];

        self::assertTrue(
            $validator->validate($payload),
            'Limits must be inclusive.',
        );
    }

    public function testAcceptsValidPayload(): void
    {
        $this->mockWebApplication();

        $validator = new EncodedFileValidator(
            ['extensions' => 'PNG, jpg', 'mimeTypes' => ['image/*'], 'maxSize' => 1024, 'minSize' => 1],
        );

        self::assertTrue(
            $validator->validate(self::payload('logo.PNG', self::PNG)),
            'Matching payload must pass.',
        );
    }

    public function testCustomMessagesArePreserved(): void
    {
        $this->mockWebApplication();

        $validator = new EncodedFileValidator(
            [
                'message' => 'bad payload',
                'tooBig' => 'big {file}',
                'tooMany' => 'many {limit}',
                'tooSmall' => 'small {file}',
                'uploadRequired' => 'required',
                'wrongExtension' => 'ext {file} {extensions}',
                'wrongMimeType' => 'mime {file} {mimeTypes}',
            ],
        );

        self::assertSame(
            'bad payload',
            $validator->message,
            'Payload message must be kept.',
        );
        self::assertSame(
            'big {file}',
            $validator->tooBig,
            'Too big message must be kept.',
        );
        self::assertSame(
            'many {limit}',
            $validator->tooMany,
            'Too many message must be kept.',
        );
        self::assertSame(
            'small {file}',
            $validator->tooSmall,
            'Too small message must be kept.',
        );
        self::assertSame(
            'required',
            $validator->uploadRequired,
            'Upload required message must be kept.',
        );
        self::assertSame(
            'ext {file} {extensions}',
            $validator->wrongExtension,
            'Extension message must be kept.',
        );
        self::assertSame(
            'mime {file} {mimeTypes}',
            $validator->wrongMimeType,
            'MIME message must be kept.',
        );
    }

    public function testCustomMessagesReceiveFileParameters(): void
    {
        $this->mockWebApplication();

        $extension = new EncodedFileValidator(['extensions' => ['jpg'], 'wrongExtension' => '{file}: {extensions}']);
        $mime = new EncodedFileValidator(['mimeTypes' => ['image/*'], 'wrongMimeType' => '{file}: {mimeTypes}']);
        $big = new EncodedFileValidator(['maxSize' => 1, 'tooBig' => '{file} > {formattedLimit} ({limit})']);
        $small = new EncodedFileValidator(['minSize' => 9, 'tooSmall' => '{file} < {formattedLimit} ({limit})']);

        $extension->validate(self::payload('logo.png', self::PNG), $extensionError);
        $mime->validate(self::payload('a.txt', 'hello'), $mimeError);
        $big->validate(self::payload('a.txt', 'hello'), $bigError);
        $small->validate(self::payload('a.txt', 'hello'), $smallError);

        self::assertSame(
            'logo.png: jpg',
            $extensionError,
            'Extension message must receive file and list.',
        );
        self::assertSame(
            'a.txt: image/*',
            $mimeError,
            'MIME message must receive file and list.',
        );
        self::assertSame(
            'a.txt > 1 B (1)',
            $bigError,
            'Too big message must receive file and limits.',
        );
        self::assertSame(
            'a.txt < 9 B (9)',
            $smallError,
            'Too small message must receive file and limits.',
        );
    }

    public function testInitNormalizesValidatorScopes(): void
    {
        $this->mockWebApplication();

        $validator = new EncodedFileValidator(['attributes' => 'avatar', 'on' => 'create', 'except' => 'update']);

        self::assertSame(
            ['avatar'],
            $validator->attributes,
            'Attributes must be normalized by the parent.',
        );
        self::assertSame(
            ['create'],
            $validator->on,
            'Scenarios must be normalized by the parent.',
        );
        self::assertSame(
            ['update'],
            $validator->except,
            'Excluded scenarios must be normalized by the parent.',
        );
    }

    public function testIsEmptyTreatsBlankEntriesAsEmpty(): void
    {
        $this->mockWebApplication();

        $validator = new EncodedFileValidator();

        self::assertTrue(
            $validator->isEmpty(null),
            "'null' is empty.",
        );
        self::assertTrue(
            $validator->isEmpty(''),
            'Empty string is empty.',
        );
        self::assertTrue(
            $validator->isEmpty(['', null]),
            'Blank entries are empty.',
        );
        self::assertFalse(
            $validator->isEmpty('{}'),
            'Non-empty strings are not empty.',
        );
        self::assertFalse(
            $validator->isEmpty(['', '{}']),
            'Lists with a payload are not empty.',
        );
    }

    public function testNormalizesExtensionAndMimeTypeLists(): void
    {
        $this->mockWebApplication();

        $validator = new EncodedFileValidator(['extensions' => [' Jpg ', 'PNG'], 'mimeTypes' => 'IMAGE/PNG | text/*']);

        self::assertSame(
            ['jpg', 'png'],
            $validator->extensions,
            'Extensions must be trimmed and lowercased.',
        );
        self::assertSame(
            ['image/png', 'text/*'],
            $validator->mimeTypes,
            'MIME types must be split and lowercased.',
        );
        self::assertTrue(
            $validator->validate(self::payload('logo.PNG', self::PNG)),
            'Normalized lists must match.',
        );
    }

    public function testRejectsDisallowedExtension(): void
    {
        $this->mockWebApplication();

        $validator = new EncodedFileValidator(['extensions' => ['jpg']]);

        self::assertFalse(
            $validator->validate(self::payload('logo.png', self::PNG), $error),
            'Extension rejected.',
        );
        self::assertSame(
            'Only files with these extensions are allowed: jpg.',
            $error,
            'Extension message.',
        );
    }

    public function testRejectsDisallowedMimeTypeDetectedFromContent(): void
    {
        $this->mockWebApplication();

        $validator = new EncodedFileValidator(['mimeTypes' => 'application/pdf']);

        self::assertFalse(
            $validator->validate(self::payload('logo.pdf', self::PNG), $error),
            'MIME rejected.',
        );
        self::assertSame(
            'Only files with these MIME types are allowed: application/pdf.',
            $error,
            'MIME message.',
        );
    }

    public function testRejectsEmptyListAsMissingUpload(): void
    {
        $this->mockWebApplication();

        $validator = new EncodedFileValidator();

        self::assertFalse(
            $validator->validate(['', null], $error),
            'Blank list must be rejected.',
        );
        self::assertSame(
            'Please upload a file.',
            $error,
            'Upload required message expected.',
        );
    }

    public function testRejectsFileAboveMaxSize(): void
    {
        $this->mockWebApplication();

        $validator = new EncodedFileValidator(['maxSize' => 4]);

        self::assertFalse(
            $validator->validate(self::payload('a.txt', 'hello'), $error),
            'Large file rejected.',
        );
        self::assertSame(
            'The file "a.txt" is too big. Its size cannot exceed 4 B.',
            $error,
            'Too big message.',
        );
    }

    public function testRejectsFileBelowMinSize(): void
    {
        $this->mockWebApplication();

        $validator = new EncodedFileValidator(['minSize' => 10]);

        self::assertFalse(
            $validator->validate(self::payload('a.txt', 'hello'), $error),
            'Small file rejected.',
        );
        self::assertSame(
            'The file "a.txt" is too small. Its size cannot be smaller than 10 B.',
            $error,
            'Too small message.',
        );
    }

    public function testRejectsInvalidPayload(): void
    {
        $this->mockWebApplication();

        $validator = new EncodedFileValidator();

        self::assertFalse(
            $validator->validate('{', $error),
            'Malformed JSON must be rejected.',
        );
        self::assertSame(
            'The uploaded file is not a valid FilePond payload.',
            $error,
            'Payload message expected.',
        );
    }

    public function testRejectsMoreFilesThanAllowed(): void
    {
        $this->mockWebApplication();

        $validator = new EncodedFileValidator(['maxFiles' => 1]);

        $payload = [self::payload('a.txt', 'a'), self::payload('b.txt', 'b')];

        self::assertFalse(
            $validator->validate($payload, $error),
            'Too many files must be rejected.',
        );
        self::assertSame(
            'You can upload at most 1 file.',
            $error,
            'Too many message expected.',
        );
    }

    public function testUnlimitedFilesWhenMaxFilesIsZero(): void
    {
        $this->mockWebApplication();

        $validator = new EncodedFileValidator(['maxFiles' => 0]);

        $payload = [self::payload('a.txt', 'a'), self::payload('b.txt', 'b')];

        self::assertTrue(
            $validator->validate($payload),
            'Zero must disable the file count limit.',
        );
    }

    public function testValidateAttributeAddsErrorToModel(): void
    {
        $this->mockWebApplication();

        $model = new TestForm();

        $model->avatar = self::payload('avatar.png', self::PNG);

        $validator = new EncodedFileValidator(['extensions' => ['jpg']]);

        $validator->validateAttribute($model, 'avatar');

        self::assertSame(
            ['Only files with these extensions are allowed: jpg.'],
            $model->getErrors('avatar'),
            'Error must be attached to the attribute.',
        );
    }

    public function testValidateAttributeSkipsEmptyValue(): void
    {
        $this->mockWebApplication();

        $model = new TestForm();

        $model->avatar = [''];

        $validator = new EncodedFileValidator(['attributes' => ['avatar']]);

        $validator->validateAttributes($model);

        self::assertFalse(
            $model->hasErrors(),
            'Empty values must be skipped by default.',
        );
    }

    private static function payload(string $name, string $content): string
    {
        return json_encode(['name' => $name, 'data' => base64_encode($content)], JSON_THROW_ON_ERROR);
    }
}
