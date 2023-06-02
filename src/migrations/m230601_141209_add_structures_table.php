<?php

namespace spicyweb\contenttemplates\migrations;

use Craft;
use craft\db\Migration;

/**
 * m230601_141209_add_structures_table migration.
 */
class m230601_141209_add_structures_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTable('{{%contenttemplatesstructures}}', [
            'typeId' => $this->integer()->notNull(),
            'structureId' => $this->integer()->notNull(),
            'PRIMARY KEY([[typeId]])',
        ]);
        $this->createIndex(null, '{{%contenttemplatesstructures}}', ['typeId'], false);
        $this->addForeignKey(null, '{{%contenttemplatesstructures}}', ['typeId'], '{{%entrytypes}}', ['id'], 'CASCADE', null);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m230601_141209_add_structures_table cannot be reverted.\n";
        return false;
    }
}
