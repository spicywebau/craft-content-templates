<?php

namespace spicyweb\contenttemplates\migrations;

use Craft;
use craft\db\Migration;

/**
 * m230419_055108_add_preview_id_column migration.
 */
class m230419_055108_add_preview_id_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->addColumn('{{%contenttemplates}}', 'previewId', $this->integer()->after('typeId'));
        $this->addForeignKey(null, '{{%contenttemplates}}', ['previewId'], '{{%assets}}', ['id'], 'CASCADE', null);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m230419_055108_add_preview_id_column cannot be reverted.\n";
        return false;
    }
}
