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
     * @var string[] Folder path(s) content template preview images can be selected from.
     */
    public array $previewSource = [];
}
