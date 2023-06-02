<?php

namespace spicyweb\contenttemplates\migrations;

use craft\db\Migration;

/**
 * Class Install
 *
 * @package spicyweb\contenttemplates\migrations
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 1.0.0
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%contenttemplates}}', [
            'id' => $this->integer()->notNull(),
            'typeId' => $this->integer()->notNull(),
            'previewImage' => $this->string(),
            'description' => $this->string(),
            'PRIMARY KEY([[id]])',
        ]);
        $this->createIndex(null, '{{%contenttemplates}}', ['typeId'], false);
        $this->addForeignKey(null, '{{%contenttemplates}}', ['id'], '{{%elements}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%contenttemplates}}', ['typeId'], '{{%entrytypes}}', ['id'], 'CASCADE', null);

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
    public function safeDown()
    {
        $this->dropTableIfExists('{{%contenttemplates}}');
        $this->dropTableIfExists('{{%contenttemplatesstructures}}');

        return true;
    }
}
