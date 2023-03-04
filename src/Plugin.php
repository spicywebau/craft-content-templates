<?php

namespace spicyweb\contenttemplates;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\controllers\ElementsController;
use craft\elements\Entry;
use craft\events\DefineElementEditorHtmlEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use Illuminate\Support\Collection;
use spicyweb\contenttemplates\controllers\CpController;
use spicyweb\contenttemplates\services\ProjectConfig;
use spicyweb\contenttemplates\web\assets\modal\ModalAsset;
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
        $this->_registerProjectConfigApply();

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            $this->_registerModal();
            $this->_registerUrlRules();
        }
    }

    /**
     * Listens for content template updates in the project config to apply them to the database.
     */
    private function _registerProjectConfigApply(): void
    {
        Craft::$app->getProjectConfig()
            ->onAdd('contentTemplates.{uid}', [$this->projectConfig, 'handleChangedContentTemplate'])
            ->onUpdate('contentTemplates.{uid}', [$this->projectConfig, 'handleChangedContentTemplate'])
            ->onRemove('contentTemplates.{uid}', [$this->projectConfig, 'handleDeletedContentTemplate']);
    }

    /**
     * Listens for element editor content generation, and registers the content template selection modal if the element
     * is an entry with no existing custom field content.
     */
    private function _registerModal(): void
    {
        Event::on(
            ElementsController::class,
            ElementsController::EVENT_DEFINE_EDITOR_CONTENT,
            function(DefineElementEditorHtmlEvent $event) {
                $element = $event->element;

                // We only support entries
                if (!$element instanceof Entry) {
                    return;
                }

                $showModal = Collection::make($element->getFieldLayout()->getCustomFields())
                    ->filter(fn($field) => !$element->isFieldEmpty($field->handle))
                    ->isEmpty();

                if ($showModal) {
                    Craft::$app->getView()->registerAssetBundle(ModalAsset::class);
                }
            }
        );
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
