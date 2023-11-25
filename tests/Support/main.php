<?php

declare(strict_types=1);

/**
 * @var \yii\web\View $this
 * @var string $widget
 */
$this->beginPage();
$this->head();
$this->beginBody();
echo $widget;
$this->endBody();
$this->endPage();
