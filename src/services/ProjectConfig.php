<?php

namespace spicyweb\contenttemplates\services;

use Craft;
use craft\db\Table;
use craft\events\ConfigEvent;
use craft\helpers\Db;
use craft\helpers\ProjectConfig as ProjectConfigHelper;
use spicyweb\contenttemplates\records\ContentTemplate as ContentTemplateRecord;
use yii\base\Component;

/**
 * Content Templates project config service class.
 *
 * @package spicyweb\contenttemplates\services
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 1.0.0
 */
class ProjectConfig extends Component
{
    /**
     * Handles a content template change.
     *
     * @param ConfigEvent $event
     * @throws \Throwable
     */
    public function handleChangedContentTemplate(ConfigEvent $event): void
    {
        // Make sure the fields have been synced
        ProjectConfigHelper::ensureAllFieldsProcessed();
        $this->save($event->tokenMatches[0], $event->newValue);
    }

    public function save(string $uid, array $data): void
    {
        Craft::$app->getDb()->transaction(function() use ($uid, $data) {
            $projectConfig = Craft::$app->getProjectConfig();

            // If we're not applying external changes, we don't want to resave the content
            if ($projectConfig->getIsApplyingExternalChanges()) {
                // TODO
            }

            $id = Db::idByUid(Table::ELEMENTS, $uid);
            $record = ContentTemplateRecord::findOne(['id' => $id]);

            if ($record === null) {
                $record = new ContentTemplateRecord();
            }

            $record->id = $id;
            $record->typeId = Db::idByUid(Table::ENTRYTYPES, $data['type']);
            $record->save();
        });
    }
}
