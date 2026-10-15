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

File uploads with image previews, a Cropper.js editor, and server-side validation and storage helpers.
The widget initializes FilePond without jQuery and loads only enabled plugin assets.

![FilePond widget with the Cropper.js editor](docs/images/filepond.png)

## Features

<picture>
    <source media="(min-width: 768px)" srcset="./docs/svgs/features.svg">
    <img src="./docs/svgs/features-mobile.svg" alt="Feature Overview" style="width: 100%;">
</picture>

## Quick start

### Installation

Requires the `fileinfo` extension. Foxy 0.4's native manager installs the frontend assets without Node.js or npm;
it requires PHP `zlib` and Composer 2.10.2+.

Set the manager in the application's root `composer.json` before installing:

```json
{
  "config": {
    "foxy": {
      "manager": "native"
    }
  }
}
```

```bash
composer config allow-plugins.php-forge/foxy true
composer require php-forge/foxy:^0.4 yii2-extensions/filepond:^0.1
```

Foxy installs FilePond, its plugins, and Cropper.js in `node_modules` beside the application's `package.json`.
Commit the generated `foxy.lock` with the application's Composer files. Point Yii's `@npm` alias at that directory;
`@app/node_modules` is correct only when the application `basePath` is the Composer project root. See
[installation](docs/installation.md) for alias configuration, existing npm projects, and asset delivery.

### Basic usage

Place this in a Yii view, select a raster image, and edit it with a fixed square selection:

```php
use yii2\extensions\filepond\FilePond;

echo FilePond::widget(
    [
        'name' => 'avatar',
        'acceptedFileTypes' => ['image/jpeg', 'image/png', 'image/webp'],
        'allowImageEdit' => true,
        'allowImageTransform' => true,
        'imageCropAspectRatio' => '1:1',
        'cropper' => ['aspectRatios' => ['1:1']],
        'maxFileSize' => '2MB',
        'config' => ['imageEditInstantEdit' => true],
    ],
);
```

The editor opens when an image is added and offers only the `1:1` ratio preset. Image Transform applies the
confirmed crop before form submission.
Previewing a crop alone does not change the uploaded bytes. To receive, validate, and store the image, follow the
[complete upload workflow](docs/uploads.md).

## Documentation

- 📚 [Installation Guide](docs/installation.md): native Composer setup, asset paths, translations, and assets.
- ⚙️ [Configuration Guide](docs/configuration.md): option precedence, plugins, and editor behavior.
- 📤 [Upload Workflow](docs/uploads.md): active form, server validation, cropping, and storage.
- 💡 [Usage Examples](docs/examples.md): runnable widgets and helper demonstrations.
- 🧪 [Testing Guide](docs/testing.md): development checks.

The [documentation site](https://yii2-framework.github.io/docs/extensions/filepond/) renders the examples as live
demos. Its [API reference](https://yii2-framework.github.io/docs/extensions/filepond/reference/) is generated from
the PHPDoc in `src/`.

Server validation inspects decoded bytes rather than trusting the browser's MIME type. List raster MIME types
explicitly when serving uploads from your origin: `image/*` also accepts SVG. Encoded uploads increase request
size; configure the application's request limits as explained in the upload guide.

## AI assistance

AI agents: read <https://yii2-framework.github.io/docs/llms.txt>, the index of the documentation site, and fetch the
Markdown version of a page by appending `index.md` to its URL, for example
<https://yii2-framework.github.io/docs/extensions/filepond/installation/index.md>. For the Yii conventions this
package builds on, install the
[`yii2-framework` agent skill](https://github.com/yii2-framework/docs/blob/main/skills/yii2-framework/SKILL.md) with
`npx skills add yii2-framework/docs --skill yii2-framework`.

- 🤖 [yii2-filepond-uploads skill](docs/agents.md): installation for Claude Code, Codex, and OpenCode.

If the hosted sources are unavailable, use the repository guides and locally available skills.

## Package information

[![PHP](https://img.shields.io/badge/%3E%3D8.3-777BB4.svg?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/releases/8.3/en.php)
[![Yii 2.0.x](https://img.shields.io/badge/2.0.55+-0073AA.svg?style=for-the-badge&logo=yii&logoColor=white)](https://github.com/yiisoft/yii2/tree/master)
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

[![License](https://img.shields.io/badge/License-BSD--3--Clause-brightgreen.svg?style=for-the-badge&logo=opensourceinitiative&logoColor=white&labelColor=555555)](LICENSE)
