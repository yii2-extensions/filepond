<?php

declare(strict_types=1);

namespace yii2\extensions\filepond\tests\file;

use GdImage;
use Generator;
use PHPUnit\Framework\Attributes\{Group, RequiresOperatingSystem};
use RuntimeException;
use Yii;
use yii\base\InvalidArgumentException;
use yii2\extensions\filepond\exception\Message;
use yii2\extensions\filepond\file\{EncodedFile, FileSaver};
use yii2\extensions\filepond\tests\support\stub\MockerFunctions;
use yii2\extensions\filepond\tests\support\TestCase;

use function base64_encode;
use function basename;
use function file_get_contents;
use function file_put_contents;
use function fileperms;
use function imagecreatetruecolor;
use function imagejpeg;
use function is_dir;
use function mkdir;
use function ob_get_clean;
use function ob_start;
use function umask;

use const DIRECTORY_SEPARATOR;

/**
 * Unit tests for {@see FileSaver} directory handling and file naming.
 */
#[Group('file')]
final class FileSaverTest extends TestCase
{
    private const string GIF = "GIF89a\x01\x00\x01\x00\x00\x00\x00;";
    private const string PNG = "\x89PNG\r\n\x1a\n" . "\0\0\0\rIHDR\0\0\0\x01\0\0\0\x01\x08\x06\0\0\0\x1f\x15\xc4\x89";

    public function testSaveAllSuffixesCustomBaseName(): void
    {
        $this->mockWebApplication();

        $saver = new FileSaver(self::RUNTIME_PATH . '/uploads');

        $files = [self::file('a.jpg', self::jpeg()), self::file('b.png', self::PNG), self::file('c.gif', self::GIF)];

        $generator = static function () use ($files): Generator {
            yield 'first' => $files[0];
            yield 'second' => $files[1];
            yield 'third' => $files[2];
        };

        $named = $saver->saveAll($generator(), 'gallery');
        $original = $saver->saveAll($files);

        self::assertSame(
            ['gallery.jpg', 'gallery-1.png', 'gallery-2.gif'],
            array_map(basename(...), $named),
            'Second and later files must receive an index.',
        );
        self::assertSame(
            ['a.jpg', 'b.png', 'c.gif'],
            array_map(basename(...), $original),
            'Client names kept.',
        );
    }

    public function testSaveAllSuffixesDuplicatePaths(): void
    {
        $this->mockWebApplication();

        $paths = (new FileSaver(self::RUNTIME_PATH . '/uploads/duplicates'))->saveAll(
            [self::file('photo.txt', 'first'), self::file('photo.txt', 'second'), self::file('photo-1.txt', 'third')],
        );

        self::assertSame(
            ['photo.txt', 'photo-1.txt', 'photo-1-1.txt'],
            array_map(basename(...), $paths),
            'Colliding paths must receive a free suffix.',
        );
        self::assertSame(
            ['first', 'second', 'third'],
            array_map(file_get_contents(...), $paths),
            'No file in the batch may be overwritten.',
        );
    }

    #[RequiresOperatingSystem('Linux')]
    public function testSaveAppliesDirectoryAndFileModes(): void
    {
        $this->mockWebApplication();

        $directory = self::RUNTIME_PATH . '/uploads/modes';

        $path = (new FileSaver($directory))->save(self::file('a.txt', 'a'));
        $custom = (new FileSaver($directory . '/custom', 0o700, 0o600))->save(self::file('b.txt', 'b'));

        self::assertSame(
            0o775 & ~umask(),
            fileperms($directory) & 0o777,
            'Default directory mode expected.',
        );
        self::assertSame(
            0o644,
            fileperms($path) & 0o777,
            'Default file mode expected.',
        );
        self::assertSame(
            0o700 & ~umask(),
            fileperms($directory . '/custom') & 0o777,
            'Custom directory mode.',
        );
        self::assertSame(
            0o600,
            fileperms($custom) & 0o777,
            'Custom file mode expected.',
        );
    }

    public function testSaveCreatesDirectoryFromAliasAndWritesContent(): void
    {
        $this->mockWebApplication();

        Yii::setAlias('@uploads', self::RUNTIME_PATH . '/uploads');

        $path = (new FileSaver('@uploads/avatars'))->save(self::file('My Photo (1).PNG', self::PNG));

        self::assertSame(
            self::RUNTIME_PATH . '/uploads/avatars' . DIRECTORY_SEPARATOR . 'My-Photo-1.png',
            $path,
            'Path must be sanitized.',
        );
        self::assertSame(
            self::PNG,
            file_get_contents($path),
            'Content must be written.',
        );
    }

    public function testSaveDerivesExtensionFromContentForSpoofedClientExtension(): void
    {
        $this->mockWebApplication();

        $saver = new FileSaver(self::RUNTIME_PATH . '/uploads');

        $polyglot = self::GIF . '<?php echo 1; ?>';

        $named = $saver->save(self::file('avatar.php', $polyglot), 'user-5');
        $derived = $saver->save(self::file('shell.php.jpg', $polyglot));

        self::assertSame(
            'user-5.gif',
            basename($named),
            'Extension must come from the detected MIME type.',
        );
        self::assertSame(
            'shell-php.gif',
            basename($derived),
            'Base name must not carry a second extension.',
        );
    }

    public function testSaveDerivesExtensionFromContentWhenNameHasNone(): void
    {
        $this->mockWebApplication();

        $saver = new FileSaver(self::RUNTIME_PATH . '/uploads');

        $png = $saver->save(self::file('avatar', self::PNG));
        $text = $saver->save(self::file('notes', 'plain text'), 'notes');

        self::assertSame(
            'avatar.png',
            basename($png),
            'PNG extension must come from the detected MIME type.',
        );
        self::assertSame(
            'notes.txt',
            basename($text),
            'Text extension must come from the known MIME map.',
        );
    }

    public function testSaveFallsBackToPlaceholderNameAndMimeExtension(): void
    {
        $this->mockWebApplication();

        $path = (new FileSaver(self::RUNTIME_PATH . '/uploads'))->save(self::file('...', "\x00\x01\x02"));

        self::assertSame(
            'file.bin',
            basename($path),
            'Empty names must fall back to `file` plus the MIME ext.',
        );
    }

    public function testSaveFallsBackToYiiMimeMapOrNoExtension(): void
    {
        $this->mockWebApplication();

        $saver = new FileSaver(self::RUNTIME_PATH . '/uploads');

        $json = $saver->save(self::file('data', '{"a":1}'));
        $gzip = $saver->save(self::file('archive', "\x1f\x8b\x08\x00\x00\x00\x00\x00\x00\x03"));

        self::assertSame(
            'data.json',
            basename($json),
            'Unknown MIME types must use the Yii MIME map.',
        );
        self::assertSame(
            'archive',
            basename($gzip),
            'Unmapped MIME types must yield no extension.',
        );
    }

    public function testSaveKeepsClientExtensionRegisteredForContent(): void
    {
        $this->mockWebApplication();

        $path = (new FileSaver(self::RUNTIME_PATH . '/uploads'))->save(self::file('photo.JPEG', self::jpeg()));

        self::assertSame(
            'photo.jpeg',
            basename($path),
            'Registered client extension must be kept in lowercase.',
        );
    }

    public function testSaveMapsJpegAndPngExtensionsFromContent(): void
    {
        $this->mockWebApplication();

        $saver = new FileSaver(self::RUNTIME_PATH . '/uploads');

        $jpeg = $saver->save(self::file('photo', self::jpeg()));
        $png = $saver->save(self::file('logo', self::PNG));

        self::assertSame(
            'photo.jpg',
            basename($jpeg),
            'JPEG must map to `jpg`.',
        );
        self::assertSame(
            'logo.png',
            basename($png),
            'PNG must map to `png` through the Yii MIME map.',
        );
    }

    public function testSaveUsesCustomBaseName(): void
    {
        $this->mockWebApplication();

        $path = (new FileSaver(self::RUNTIME_PATH . '/uploads'))->save(self::file('a.jpg', self::jpeg()), 'user/42 avatar');

        self::assertSame(
            'user-42-avatar.jpg',
            basename($path),
            'Custom name must be sanitized and keep the ext.',
        );
    }

    public function testThrowInvalidArgumentExceptionForUnknownAlias(): void
    {
        $this->mockWebApplication();

        $this->expectException(InvalidArgumentException::class);

        (new FileSaver('@unknown/uploads'))->save(self::file('a.txt', 'a'));
    }

    public function testThrowRuntimeExceptionWhenDirectoryCannotBeCreated(): void
    {
        $this->mockWebApplication();

        $blocker = self::RUNTIME_PATH . '/blocker';

        if (!is_dir(self::RUNTIME_PATH)) {
            mkdir(self::RUNTIME_PATH, 0o777, true);
        }

        file_put_contents($blocker, 'not a directory');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            Message::DIRECTORY_NOT_WRITABLE->getMessage("{$blocker}/uploads"),
        );

        (new FileSaver("{$blocker}/uploads"))->save(self::file('a.txt', 'a'));
    }

    public function testThrowRuntimeExceptionWhenDirectoryIsNotWritable(): void
    {
        $this->mockWebApplication();

        MockerFunctions::override('is_writable', false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            Message::DIRECTORY_NOT_WRITABLE->getMessage(self::RUNTIME_PATH . '/uploads'),
        );

        (new FileSaver(self::RUNTIME_PATH . '/uploads'))->save(self::file('a.txt', 'a'));
    }

    public function testThrowRuntimeExceptionWhenFileCannotBeWritten(): void
    {
        $this->mockWebApplication();

        MockerFunctions::override('file_put_contents', false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            Message::FILE_WRITE_FAILED->getMessage(self::RUNTIME_PATH . '/uploads' . DIRECTORY_SEPARATOR . 'a.txt'),
        );

        (new FileSaver(self::RUNTIME_PATH . '/uploads'))->save(self::file('a.txt', 'plain text'));
    }

    private static function file(string $name, string $content): EncodedFile
    {
        return EncodedFile::fromArray(['name' => $name, 'data' => base64_encode($content)]);
    }

    private static function jpeg(): string
    {
        $image = imagecreatetruecolor(2, 2);

        self::assertInstanceOf(
            GdImage::class,
            $image,
            'Fixture image must be created.',
        );

        ob_start();
        imagejpeg($image);

        return (string) ob_get_clean();
    }
}
