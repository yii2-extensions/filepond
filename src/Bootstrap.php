<?php

declare(strict_types=1);

namespace yii2\extensions\filepond;

use yii\base\{Application, BootstrapInterface};
use yii\i18n\PhpMessageSource;

use function dirname;

/**
 * Registers the `yii.filepond` message source when the application does not configure it.
 *
 * Composer runs this class automatically through the `extra.bootstrap` entry of the package, so the widget labels are
 * translated without additional configuration.
 */
final class Bootstrap implements BootstrapInterface
{
    /**
     * @param Application $app Application being bootstrapped.
     */
    public function bootstrap($app): void
    {
        $i18n = $app->getI18n();

        if (isset($i18n->translations[FilePond::TRANSLATION_CATEGORY])) {
            return;
        }

        $i18n->translations[FilePond::TRANSLATION_CATEGORY] = [
            'class' => PhpMessageSource::class,
            'basePath' => dirname(__DIR__) . '/messages',
            'sourceLanguage' => 'en-US',
        ];
    }
}
