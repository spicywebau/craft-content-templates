<?php

namespace spicyweb\contenttemplates\assets\modal;

use benf\neo\elements\Block;
use benf\neo\Field;
use benf\neo\models\BlockType;
use benf\neo\models\BlockTypeGroup;
use benf\neo\Plugin as Neo;
use Craft;
use craft\helpers\Json;
use craft\models\FieldLayout;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Content template modal asset class.
 *
 * @package spicyweb\contenttemplates\assets\modal
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 1.0.0
 */
class Modal extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->sourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'dist';

        $this->depends = [
            CpAsset::class,
        ];
        $this->js = [
            'scripts/modal.js',
        ];

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view): void
    {
        $view->registerTranslations('content-templates', [
        ]);

        parent::registerAssetFiles($view);
    }
}
