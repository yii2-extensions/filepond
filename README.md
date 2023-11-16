<p align="center">
    <a href="https://github.com/yii2-extensions/filepond" target="_blank">
        <img src="https://www.yiiframework.com/image/yii_logo_light.svg" height="100px;">
    </a>
    <h1 align="center">FilePond.</h1>
    <br>
</p>

<p align="center">
    <a href="https://www.php.net/releases/8.1/en.php" target="_blank">
        <img src="https://img.shields.io/badge/PHP-%3E%3D8.1-787CB5" alt="php-version">
    </a>
    <a href="https://github.com/yiisoft/yii2/tree/2.2" target="_blank">
        <img src="https://img.shields.io/badge/Yii2%20version-2.2-blue" alt="yii2-version">
    </a>
    <a href="https://github.com/yii2-extensions/filepond/actions/workflows/build.yml" target="_blank">
        <img src="https://github.com/yii2-extensions/filepond/actions/workflows/build.yml/badge.svg" alt="PHPUnit">
    </a>
    <a href="https://codecov.io/gh/yii2-extensions/filepond" target="_blank">
        <img src="https://codecov.io/gh/yii2-extensions/filepond/branch/main/graph/badge.svg?token=MF0XUGVLYC" alt="Codecov">
    </a>
    <a href="https://dashboard.stryker-mutator.io/reports/github.com/yii2-extensions/filepond/main" target="_blank">
        <img src="https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyii2-extensions%2Ffilepond%2Fmain" alt="Infection">
    </a>
    <a href="https://github.com/yii2-extensions/filepond/actions/workflows/static.yml" target="_blank">
        <img src="https://github.com/yii2-extensions/gii/actions/workflows/static.yml/badge.svg" alt="PHPStan">
    </a>
    <a href="https://github.com/yii2-extensions/filepond/actions/workflows/static.yml" target="_blank">
        <img src="https://img.shields.io/badge/PHPStan%20level-5-blue" alt="PHPStan level">
    </a>    
    <a href="https://github.styleci.io/repos/698621511?branch=main" target="_blank">
        <img src="https://github.styleci.io/repos/698621511/shield?branch=main" alt="Code style">
    </a>        
</p>

## Installation

The preferred way to install this extension is through [composer](https://getcomposer.org/download/).

Either run

```
php composer.phar require --dev --prefer-dist yii2-extensions/filepond
```

or add

```
"yii2-extensions/filepond": "dev-main"
```

to the require-dev section of your `composer.json` file.

## Usage

### View 

```php
<?=
    $form
        ->field($formModel, 'image_file')
        ->widget(
            FilePond::class,
            [
                'labelIdle' => Yii::t(
                    'yii.blog', 'Drag & Drop your files or <span class="filepond--label-action">Browse</span>',
                ),
                'loadFileDefault' => $imageFile,
                'imagePreviewHeight' => 170,
                'imageCropAspectRatio' => '1:1',
            ],
        )
?>
```

### Controller or Model

```php
$imageFile = FileProcessing::saveWithReturningFile(
    $categoryForm->image_file,
    Yii::getAlias('@uploads'),
    "category{$category->id}",
    false
);        
```

## Testing

[Check the documentation testing](/docs/testing.md) to learn about testing.

## Our social networks

[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/Terabytesoftw)

## License

The MIT License. Please see [License File](LICENSE.md) for more information.
