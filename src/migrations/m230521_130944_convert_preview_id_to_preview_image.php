<?php

namespace spicyweb\contenttemplates\migrations;

use Craft;
use craft\db\Migration;

/**
 * m230521_130944_convert_preview_id_to_preview_image migration.
 */
class m230521_130944_convert_preview_id_to_preview_image extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->addColumn('{{%contenttemplates}}', 'previewImage', $this->string()->after('previewId'));
        $this->dropForeignKeyIfExists('{{%contenttemplates}}', ['previewId']);
        $this->dropColumn('{{%contenttemplates}}', 'previewId');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m230521_130944_convert_preview_id_to_preview_image cannot be reverted.\n";
        return false;
    }
}
