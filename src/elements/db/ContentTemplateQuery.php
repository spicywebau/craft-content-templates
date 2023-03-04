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
     * @var int[]|int|null The entry type ID(s) for this query.
     */
    public array|int|null $typeId = null;

    /**
     * Filters the query results based on the entry type IDs.
     *
     * @param int[]|int|null $value The entry type ID(s).
     * @return self
     */
    public function typeId(array|int|null $value): self
    {
        $this->typeId = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('contenttemplates');

        $this->query->select([
            'contenttemplates.typeId',
            'contenttemplates.description',
        ]);

        if ($this->typeId) {
            $this->subQuery->andWhere(['contenttemplates.typeId' => $this->typeId]);
        }

        return parent::beforePrepare();
    }
}
