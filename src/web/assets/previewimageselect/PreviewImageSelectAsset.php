<?php

namespace spicyweb\contenttemplates\web\assets\previewimageselect;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Preview Image Select asset bundle.
 *
 * @package spicyweb\contenttemplates\web\assets\previewimageselect
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 1.0.0
 */
class PreviewImageSelectAsset extends AssetBundle
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
    public $css = [
        'styles/previewimageselect.css',
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'scripts/previewimageselect.js',
    ];

    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view): void
    {
        $view->registerTranslations('content-templates', [
            'Add',
            'None set',
            'Remove',
            'Replace',
        ]);

        parent::registerAssetFiles($view);
    }
}
