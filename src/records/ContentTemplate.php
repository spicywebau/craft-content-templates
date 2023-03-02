<?php

namespace spicyweb\contenttemplates\records;

use craft\db\ActiveRecord;
use craft\records\Element;
use craft\records\EntryType;
use yii\db\ActiveQueryInterface;

/**
 * Content template record class.
 *
 * @package spicyweb\contenttemplates\records
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 1.0.0
 */
class ContentTemplate extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%contenttemplates}}';
    }

    /**
     * Returns the content template's element.
     *
     * @return ActiveQueryInterface
     */
    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }

    /**
     * Returns the content template's entry type.
     *
     * @return ActiveQueryInterface
     */
    public function getType(): ActiveQueryInterface
    {
        return $this->hasOne(EntryType::class, ['id' => 'typeId']);
    }
}
