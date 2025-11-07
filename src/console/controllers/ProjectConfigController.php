<?php

namespace spicyweb\contenttemplates\console\controllers;

use Craft;
use craft\console\Controller;
use spicyweb\contenttemplates\elements\ContentTemplate;
use spicyweb\contenttemplates\Plugin;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Actions for managing Content Templates project config data.
 *
 * @package spicyweb\contenttemplates\console\controllers
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 2.1.0
 */
class ProjectConfigController extends Controller
{
    public function actionCleanupDb(): int
    {
        if (!Plugin::$plugin->getSettings()->useProjectConfig) {
            $this->stdout('Nothing to do.' . PHP_EOL);
            return ExitCode::OK;
        }

        $this->stdout('Deleting content templates not found in the project config.' . PHP_EOL);
        $elementsService = Craft::$app->getElements();
        $projectConfig = Craft::$app->getProjectConfig();
        $templates = $projectConfig->get('contentTemplates.templates') ?? [];
        $templateUuids = array_keys($templates);
        $templatesToDelete = ContentTemplate::find()
            ->status(null)
            ->andWhere(['not', ['elements.uid' => $templateUuids]])
            ->all();
        $errors = 0;

        foreach ($templatesToDelete as $template) {
            $this->stdout(sprintf('Deleting content template %s (ID %s) ... ', $template->title, $template->id));

            if ($elementsService->deleteElement($template)) {
                $this->stdout('done.' . PHP_EOL);
            } else {
                $this->stderr('failed.' . PHP_EOL, Console::FG_RED);
                $errors++;
            }
        }

        if ($errors) {
            $this->stderr('Errors occurred during cleanup.' . PHP_EOL, Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout('Done.' . PHP_EOL);
        return ExitCode::OK;
    }

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
            $this->stdout('If you have just changed the `useProjectConfig` plugin setting to true, you will need to run `php craft content-templates/project-config/cleanup-db` on other environments.' . PHP_EOL);
        } elseif (!$useProjectConfig && $hasProjectConfig) {
            $projectConfig->remove('contentTemplates');
        }

        $this->stdout('Done.' . PHP_EOL);

        return ExitCode::OK;
    }
}
