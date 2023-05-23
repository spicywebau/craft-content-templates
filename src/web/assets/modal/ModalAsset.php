<?php

namespace spicyweb\contenttemplates\web\assets\modal;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Content template modal asset bundle class.
 *
 * @package spicyweb\contenttemplates\assets\modal
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 1.0.0
 */
class ModalAsset extends AssetBundle
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
        'styles/modal.css',
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'scripts/modal.js',
    ];

    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view): void
    {
        $view->registerTranslations('content-templates', [
            'An unknown error occurred.',
            'Blank',
            'Choose a template',
            'Start off with a clean slate.',
        ]);

        parent::registerAssetFiles($view);
    }
}
