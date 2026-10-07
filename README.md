<!-- markdownlint-disable MD041 -->
<p align="center">
    <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://www.yiiframework.com/image/design/logo/yii3_full_for_dark.svg">
        <source media="(prefers-color-scheme: light)" srcset="https://www.yiiframework.com/image/design/logo/yii3_full_for_light.svg">
        <img src="https://www.yiiframework.com/image/design/logo/yii3_full_for_light.svg" alt="Yii Framework" width="80%">
    </picture>
    <h1 align="center">FilePond</h1>
    <br>
</p>
<!-- markdownlint-enable MD041 -->

<p align="center">
    <a href="https://github.com/yii2-extensions/filepond/actions/workflows/build.yml" target="_blank">
        <img src="https://img.shields.io/github/actions/workflow/status/yii2-extensions/filepond/build.yml?style=for-the-badge&label=PHPUnit&logo=github" alt="PHPUnit">
    </a>
    <a href="https://dashboard.stryker-mutator.io/reports/github.com/yii2-extensions/filepond/main" target="_blank">
        <img src="https://img.shields.io/endpoint?style=for-the-badge&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyii2-extensions%2Ffilepond%2Fmain" alt="Mutation Testing">
    </a>
    <a href="https://github.com/yii2-extensions/filepond/actions/workflows/static.yml" target="_blank">
        <img src="https://img.shields.io/github/actions/workflow/status/yii2-extensions/filepond/static.yml?style=for-the-badge&label=PHPStan&logo=github" alt="PHPStan">
    </a>
    <a href="https://github.com/yii2-extensions/filepond/actions/workflows/security.yml" target="_blank">
        <img src="https://img.shields.io/github/actions/workflow/status/yii2-extensions/filepond/security.yml?style=for-the-badge&label=Security&logo=github" alt="Security">
    </a>
</p>

<p align="center">
    <strong>FilePond file uploads for Yii2 forms</strong><br>
    <em>Cropper.js image editing, previews, client and server validation, no jQuery</em>
</p>

![FilePond widget with the Cropper.js editor](docs/images/filepond.png)

## Features

- **One widget, eleven plugins.** Every `allow*` flag registers the FilePond plugin and publishes its asset bundle;
  nothing else is loaded.
- **Image editing.** Cropper.js 2 in a native dialog with aspect ratio presets, zoom, and reset. The crop is stored
  as FilePond metadata, so the Image Transform plugin, the preview, and the server see the same rectangle.
- **Server-side helpers.** `EncodedFile` decodes File Encode payloads, `FileSaver` stores them with sanitized
  names, `ImageCropper` applies the crop with GD, and `EncodedFileValidator` checks the real MIME type and size.
- **Modern delivery.** Frontend packages come from `node_modules` through [`php-forge/foxy`](https://github.com/php-forge/foxy),
  locally or from a CDN pinned to the installed versions.
- **Localized.** Labels ship in nine languages and register automatically through the extension bootstrap.

## Quick start

### Installation

Requires PHP 8.3 or newer and Yii2 `2.0.54` or `22.x`.

```bash
composer config allow-plugins.php-forge/foxy true
composer require php-forge/foxy:^0.3 yii2-extensions/filepond
```

Yii2 `2.0.x` applications must point the `@npm` alias to `@app/node_modules`; see the
[installation guide](docs/installation.md).

### Basic usage

```php
use yii2\extensions\filepond\FilePond;

echo $form->field($model, 'avatar')->widget(
    FilePond::class,
    [
        'acceptedFileTypes' => ['image/*'],
        'allowImageEdit' => true,
        'allowImageTransform' => true,
        'imageCropAspectRatio' => '1:1',
        'maxFileSize' => '2MB',
    ],
);
```

```php
use yii2\extensions\filepond\file\{EncodedFile, FileSaver};
use yii2\extensions\filepond\validator\EncodedFileValidator;

// Model rules.
[['avatar'], EncodedFileValidator::class, 'mimeTypes' => ['image/*'], 'maxSize' => 2 * 1024 * 1024];

// Controller.
foreach (EncodedFile::fromInput($model->avatar) as $file) {
    (new FileSaver('@webroot/uploads'))->save($file, "user-{$model->id}");
}
```

## Documentation

For detailed configuration options and advanced usage.

- 📚 [Installation Guide](docs/installation.md)
- ⚙️ [Configuration Reference](docs/configuration.md)
- 💡 [Usage Examples](docs/examples.md)
- 🧪 [Testing Guide](docs/testing.md)

## Package information

[![PHP](https://img.shields.io/badge/%3E%3D8.3-777BB4.svg?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/releases/8.3/en.php)
[![Yii 2.0.x](https://img.shields.io/badge/2.0.54+-0073AA.svg?style=for-the-badge&logo=yii&logoColor=white)](https://github.com/yiisoft/yii2/tree/master)
[![Yii 22.0.x](https://img.shields.io/badge/22.0.x-0073AA.svg?style=for-the-badge&logo=yii&logoColor=white)](https://github.com/yiisoft/yii2/tree/22.0)
[![Latest Stable Version](https://img.shields.io/packagist/v/yii2-extensions/filepond.svg?style=for-the-badge&logo=packagist&logoColor=white&label=Stable)](https://packagist.org/packages/yii2-extensions/filepond)
[![Total Downloads](https://img.shields.io/packagist/dt/yii2-extensions/filepond.svg?style=for-the-badge&logo=composer&logoColor=white&label=Downloads)](https://packagist.org/packages/yii2-extensions/filepond)

## Project status

[![Codecov](https://img.shields.io/codecov/c/github/yii2-extensions/filepond.svg?style=for-the-badge&logo=codecov&logoColor=white&label=Coverage)](https://codecov.io/github/yii2-extensions/filepond)
[![PHPStan Level Max](https://img.shields.io/badge/PHPStan-Level%20Max-4F5D95.svg?style=for-the-badge&logo=github&logoColor=white)](https://github.com/yii2-extensions/filepond/actions/workflows/static.yml)
[![Quality](https://img.shields.io/github/actions/workflow/status/yii2-extensions/filepond/quality.yml?style=for-the-badge&label=Quality&logo=github)](https://github.com/yii2-extensions/filepond/actions/workflows/quality.yml)
[![StyleCI](https://img.shields.io/badge/StyleCI-Passed-44CC11.svg?style=for-the-badge&logo=github&logoColor=white)](https://github.styleci.io/repos/719070630?branch=main)

## Our social networks

[![Follow on X](https://img.shields.io/badge/-Follow%20on%20X-1DA1F2.svg?style=for-the-badge&logo=x&logoColor=white&labelColor=000000)](https://x.com/Terabytesoftw)

## License

[![License](https://img.shields.io/badge/License-MIT-brightgreen.svg?style=for-the-badge&logo=opensourceinitiative&logoColor=white&labelColor=555555)](LICENSE)
