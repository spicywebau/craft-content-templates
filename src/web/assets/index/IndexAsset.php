<?php

namespace spicyweb\contenttemplates\web\assets\index;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Content template element index asset bundle class.
 *
 * @package spicyweb\contenttemplates\web\assets\index
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 1.0.0
 */
class IndexAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'dist';

    /**
     * @inheritdoc
     */
    public $depends = [
        CpAsset::class,
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'scripts/index.js',
    ];

    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view): void
    {
        $view->registerTranslations('content-templates', [
            'New {entryType} content template',
            'New content template',
            'New content template of the {entryType} type',
            'New content template, choose an entry type',
        ]);

        parent::registerAssetFiles($view);
    }
}
