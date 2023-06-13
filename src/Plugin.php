<?php

namespace spicyweb\contenttemplates;

use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\controllers\ElementsController;
use craft\elements\Entry;
use craft\events\DefineElementEditorHtmlEvent;
use craft\events\RebuildConfigEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\fields\Assets;
use craft\helpers\Json;
use craft\services\ProjectConfig;
use craft\web\UrlManager;
use Illuminate\Support\Collection;
use spicyweb\contenttemplates\controllers\CpController;
use spicyweb\contenttemplates\elements\ContentTemplate;
use spicyweb\contenttemplates\models\Settings;
use spicyweb\contenttemplates\services\PreviewImages;
use spicyweb\contenttemplates\services\ProjectConfig as PluginProjectConfig;
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
    public string $schemaVersion = '1.0.0';

    /**
     * @inheritdoc
     */
    public bool $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public static function config(): array
    {
        return [
            'components' => [
                'previewImages' => PreviewImages::class,
                'projectConfig' => PluginProjectConfig::class,
            ],
        ];
    }

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
        $this->hasCpSection = Craft::$app->getUser()->getIsAdmin() && Craft::$app->getConfig()->getGeneral()->allowAdminChanges;
        $this->_registerProjectConfigApply();
        $this->_registerProjectConfigRebuild();

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            $this->_registerModal();
            $this->_registerUrlRules();
        }
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('content-templates/plugin-settings', [
            'settings' => $this->getSettings(),
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function cpNavIconPath(): ?string
    {
        return $this->getBasePath() . DIRECTORY_SEPARATOR . 'icon.svg';
    }

    /**
     * Listens for content template updates in the project config to apply them to the database.
     */
    private function _registerProjectConfigApply(): void
    {
        Craft::$app->getProjectConfig()
            ->onUpdate('contentTemplates.orders.{uid}', [$this->projectConfig, 'handleChangedContentTemplateOrder'])
            ->onAdd('contentTemplates.templates.{uid}', [$this->projectConfig, 'handleChangedContentTemplate'])
            ->onUpdate('contentTemplates.templates.{uid}', [$this->projectConfig, 'handleChangedContentTemplate'])
            ->onRemove('contentTemplates.templates.{uid}', [$this->projectConfig, 'handleDeletedContentTemplate']);
    }

    /**
     * Registers an event listener for a project config rebuild, and provides content template data from the database.
     */
    private function _registerProjectConfigRebuild(): void
    {
        Event::on(ProjectConfig::class, ProjectConfig::EVENT_REBUILD, function(RebuildConfigEvent $event) {
            $contentTemplateConfig = [];
            $contentTemplateOrdersConfig = [];

            foreach (ContentTemplate::find()->withStructure(true)->all() as $contentTemplate) {
                $config = $contentTemplate->getConfig();
                $contentTemplateConfig[$contentTemplate->uid] = $config;
                $contentTemplateOrdersConfig[$config['type']][$config['sortOrder']] = $contentTemplate->uid;
            }

            $event->config['contentTemplates'] = [
                'templates' => $contentTemplateConfig,
                'orders' => $contentTemplateOrdersConfig,
            ];
        });
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

                $hasNoContent = Collection::make($element->getFieldLayout()->getCustomFields())
                    ->filter(fn($field) => !$element->isFieldEmpty($field->handle))
                    ->isEmpty();

                // If it already has content, we don't want to overwrite it
                if (!$hasNoContent) {
                    return;
                }

                $contentTemplates = ContentTemplate::find()
                    ->typeId($element->typeId)
                    ->collect();

                if (!$contentTemplates->isEmpty()) {
                    $modalSettings = [
                        'elementId' => $element->id,
                        'contentTemplates' => $contentTemplates->map(fn($contentTemplate) => [
                            'id' => $contentTemplate->id,
                            'title' => $contentTemplate->title,
                            'preview' => $contentTemplate->getPreviewImageUrl([
                                'width' => 232,
                                'height' => 232,
                            ]),
                            'description' => $contentTemplate->description,
                        ])->all(),
                    ];
                    $encodedModalSettings = Json::encode($modalSettings, JSON_UNESCAPED_UNICODE);
                    $view = Craft::$app->getView();
                    $view->registerAssetBundle(ModalAsset::class);
                    $view->registerJs("new ContentTemplates.Modal($encodedModalSettings)");
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
            $event->rules['content-templates/<sectionHandle:{handle}>/<entryTypeHandle:{handle}>/<elementId:\d+>'] = 'elements/edit';
        });
    }
}
