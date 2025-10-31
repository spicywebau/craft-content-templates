<?php

namespace spicyweb\contenttemplates\console\controllers;

use Craft;
use craft\console\Controller;
use spicyweb\contenttemplates\Plugin;
use yii\console\ExitCode;

/**
 * Actions for managing Content Templates project config data.
 *
 * @package spicyweb\contenttemplates\console\controllers
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 2.1.0
 */
class ProjectConfigController extends Controller
{
    /**
     * Rebuilds Content Templates project config data depending on the `useProjectConfig` plugin setting.
     *
     * @return int
     */
    public function actionRebuild(): int
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $useProjectConfig = Plugin::$plugin->getSettings()->useProjectConfig;
        $hasProjectConfig = $projectConfig->get('contentTemplates');

        if ($useProjectConfig && !$hasProjectConfig) {
            $projectConfig->set('contentTemplates', Plugin::$plugin->projectConfig->getFromDb());
        } elseif (!$useProjectConfig && $hasProjectConfig) {
            $projectConfig->remove('contentTemplates');
        }

        $this->stdout('Done.' . PHP_EOL);

        return ExitCode::OK;
    }
}
