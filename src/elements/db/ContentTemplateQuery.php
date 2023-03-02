<?php

namespace spicyweb\contenttemplates\elements\db;

use craft\elements\db\ElementQuery;

/**
 * Content template element query class.
 *
 * @package spicyweb\contenttemplates\elements\db
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 1.0.0
 */
class ContentTemplateQuery extends ElementQuery
{
    /**
     * @var array|int|null The entry type ID(s) for this query.
     */
    public array|int|null $typeId = null;

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('contenttemplates');

        $this->query->select([
            'contenttemplates.typeId',
        ]);

        if ($this->typeId) {
            $this->subQuery->andWhere(['contenttemplates.typeId' => $this->typeId]);
        }

        return parent::beforePrepare();
    }
}
