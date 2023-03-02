<?php

namespace spicyweb\contenttemplates;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use spicyweb\contenttemplates\controllers\Cp as CpController;
use spicyweb\contenttemplates\services\ProjectConfig;
use yii\base\Event;

/**
 * Content Templates main plugin class.
 *
 * @package spicyweb\contenttemplates
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 1.0.0
 */
class Plugin extends BasePlugin
{
    /**
     * @var Plugin|null
     */
    public static ?Plugin $plugin = null;

    /**
     * @inheritdoc
     */
    public $controllerMap = [
        'cp' => CpController::class,
    ];

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;
        $this->setComponents([
            'projectConfig' => ProjectConfig::class,
        ]);
        $this->hasCpSection = Craft::$app->getUser()->getIsAdmin() && Craft::$app->getConfig()->getGeneral()->allowAdminChanges;

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            $this->_registerUrlRules();
        }
    }

    /**
     * Registers URL rules for accessing the Content Templates pages in the Craft control panel.
     */
    private function _registerUrlRules(): void
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules['content-templates'] = 'content-templates/cp/index';
            $event->rules['content-templates/<sectionHandle:{handle}>'] = 'content-templates/cp/index';
            $event->rules['content-templates/<sectionHandle:{handle}>/<entryTypeHandle:{handle}>'] = 'content-templates/cp/index';
            $event->rules['content-templates/<sectionHandle:{handle}>/<entryTypeHandle:{handle}>/<elementId:\d+><slug:(?:-[^\/]*)?>'] = 'elements/edit';
        });
    }
}
