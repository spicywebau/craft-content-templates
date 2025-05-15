<?php

namespace spicyweb\contenttemplates\models;

use craft\base\Model;

/**
 * Class Settings
 *
 * @package spicyweb\contenttemplates\models
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 1.0.0
 */
class Settings extends Model
{
    /**
     * @var string The folder path that content template preview images can be selected from. Defaults to `@webroot`.
     */
    public string $previewSource = '@webroot';

    /**
     * @var bool Whether to store content templates in Craft's project config.
     */
    public bool $useProjectConfig = true;

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['previewSource'], 'required'];

        return $rules;
    }
}
