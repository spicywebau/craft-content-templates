<?php

namespace spicyweb\contenttemplates\migrations;

use Craft;
use craft\db\Query;
use craft\migrations\BaseContentRefactorMigration;

/**
 * m240331_023352_content_refactor migration.
 */
class m240331_023352_content_refactor extends BaseContentRefactorMigration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        foreach (Craft::$app->getEntries()->getAllSections() as $section) {
            foreach ($section->getEntryTypes() as $entryType) {
                $this->updateElements(
                    (new Query())->from('{{%contenttemplates}}')->where(['typeId' => $entryType->id]),
                    $entryType->getFieldLayout(),
                );
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m240331_023352_content_refactor cannot be reverted.\n";
        return false;
    }
}
