<?php

namespace spicyweb\contenttemplates\assets\index;

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
 * Content template element index asset class.
 *
 * @package spicyweb\contenttemplates\assets\index
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 1.0.0
 */
class Index extends AssetBundle
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
            'scripts/index.js',
        ];

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view): void
    {
        $view->registerTranslations('content-templates', [
            'New content template',
        ]);

        parent::registerAssetFiles($view);
    }
}
