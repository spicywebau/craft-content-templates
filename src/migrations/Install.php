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
            'previewId' => $this->integer(),
            'description' => $this->string(),
            'PRIMARY KEY([[id]])',
        ]);
        $this->createIndex(null, '{{%contenttemplates}}', ['typeId'], false);
        $this->addForeignKey(null, '{{%contenttemplates}}', ['id'], '{{%elements}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%contenttemplates}}', ['typeId'], '{{%entrytypes}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%contenttemplates}}', ['previewId'], '{{%assets}}', ['id'], 'CASCADE', null);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%contenttemplates}}');

        return true;
    }
}
