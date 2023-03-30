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
     * Handles deleting a content template.
     *
     * @param ConfigEvent $event
     * @throws \Throwable
     */
    public function handleDeletedContentTemplate(ConfigEvent $event): void
    {
        // If the changes aren't external, the element is already deleted
        if (Craft::$app->getProjectConfig()->getIsApplyingExternalChanges()) {
            $uid = $event->tokenMatches[0];
            $id = Db::idByUid(Table::ELEMENTS, $uid);
            Craft::$app->getElements()->deleteElementById($id);
        }
    }

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
            $id = Db::idByUid(Table::ELEMENTS, $uid);
            $record = ContentTemplateRecord::findOne(['id' => $id]);

            if ($record === null) {
                $record = new ContentTemplateRecord();
            } elseif ($projectConfig->getIsApplyingExternalChanges()) {
                // If we're applying external changes, we'll need to resave the element with the new content
                $elementsService = Craft::$app->getElements();
                $contentTemplate = $elementsService->getElementById($id);
                $contentTemplate->setFieldValues($data['content']);
                $elementsService->saveElement($contentTemplate);
            }

            $record->id = $id;
            $record->typeId = Db::idByUid(Table::ENTRYTYPES, $data['type']);
            $record->description = $data['description'] ?? null;
            $record->save();
        });
    }
}
