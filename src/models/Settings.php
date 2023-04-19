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
     * @var string|array|null The asset sources content template previews can be selected from.
     */
    public string|array|null $previewSources = '*';
}
