<?php
/*
This class is based on `\craft\controllers\EntriesController` from Craft CMS 4.3.10, by Pixel & Tonic, Inc.
https://github.com/craftcms/cms/blob/4.3.10/src/controllers/EntriesController.php
Craft CMS is released under the terms of the Craft License, a copy of which is included below.
https://github.com/craftcms/cms/blob/4.3.10/LICENSE.md

Copyright © Pixel & Tonic

Permission is hereby granted to any person obtaining a copy of this software
(the “Software”) to use, copy, modify, merge, publish and/or distribute copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

1. **Don’t plagiarize.** The above copyright notice and this license shall be
   included in all copies or substantial portions of the Software.

2. **Don’t use the same license on more than one project.** Each licensed copy
   of the Software shall be actively installed in no more than one production
   environment at a time.

3. **Don’t mess with the licensing features.** Software features related to
   licensing shall not be altered or circumvented in any way, including (but
   not limited to) license validation, payment prompts, feature restrictions,
   and update eligibility.

4. **Pay up.** Payment shall be made immediately upon receipt of any notice,
   prompt, reminder, or other message indicating that a payment is owed.

5. **Follow the law.** All use of the Software shall not violate any applicable
   law or regulation, nor infringe the rights of any other person or entity.

Failure to comply with the foregoing conditions will automatically and
immediately result in termination of the permission granted hereby. This
license does not include any right to receive updates to the Software or
technical support. Licensees bear all risk related to the quality and
performance of the Software and any modifications made or obtained to it,
including liability for actual and consequential harm, such as loss or
corruption of data, and any necessary service, repair, or correction.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES, OR OTHER
LIABILITY, INCLUDING SPECIAL, INCIDENTAL AND CONSEQUENTIAL DAMAGES, WHETHER IN
AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace spicyweb\contenttemplates\controllers;

use Craft;
use craft\base\Element;
use craft\db\Table;
use craft\helpers\ArrayHelper;
use craft\helpers\Cp as CpHelper;
use craft\helpers\Db;
use craft\helpers\ElementHelper;
use craft\helpers\UrlHelper;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use craft\web\Controller;
use Illuminate\Support\Collection;
use spicyweb\contenttemplates\elements\ContentTemplate;
use spicyweb\contenttemplates\web\assets\index\IndexAsset;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * Controller class for accessing Content Templates pages in the control panel.
 *
 * @package spicyweb\contenttemplates\controllers
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 1.0.0
 */
class CpController extends Controller
{
    /**
     * @inheritdoc
     */
    public $defaultAction = 'index';

    /**
     * @inheritdoc
     */
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    /**
     * Loads the content templates index page.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        $this->getView()->registerAssetBundle(IndexAsset::class);
        return $this->renderTemplate('content-templates/_index', [
            'settings' => $this->_getIndexSettings(),
        ]);
    }

    /**
     * Creates a new unpublished draft and redirects to its edit page.
     *
     * @param string|null $entryType The entry type’s handle
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws ServerErrorHttpException
     */
    public function actionCreate(?string $entryType = null): ?Response
    {
        if ($entryType) {
            $entryTypeHandle = $entryType;
        } else {
            $entryTypeHandle = $this->request->getRequiredBodyParam('entryType');
        }

        $entryType = Craft::$app->getEntries()->getEntryTypeByHandle($entryTypeHandle);
        if (!$entryType) {
            throw new BadRequestHttpException("Invalid entry type handle: $entryTypeHandle");
        }

        $sitesService = Craft::$app->getSites();
        $siteId = $this->request->getBodyParam('siteId');

        if ($siteId) {
            $site = $sitesService->getSiteById($siteId);
            if (!$site) {
                throw new BadRequestHttpException("Invalid site ID: $siteId");
            }
        } else {
            $site = CpHelper::requestedSite();
            if (!$site) {
                throw new ForbiddenHttpException('User not authorized to edit content in any sites.');
            }
        }

        $user = static::currentUser();

        // Create & populate the draft
        $contentTemplate = Craft::createObject(ContentTemplate::class);
        $contentTemplate->siteId = $site->id;
        $contentTemplate->typeId = $entryType->id;

        // Status
        if (($status = $this->request->getQueryParam('status')) !== null) {
            $enabled = $status === 'enabled';
        } else {
            $enabled = true;
        }

        if (Craft::$app->getIsMultiSite() && count($contentTemplate->getSupportedSites()) > 1) {
            $contentTemplate->enabled = true;
            $contentTemplate->setEnabledForSite($enabled);
        } else {
            $contentTemplate->enabled = $enabled;
            $contentTemplate->setEnabledForSite(true);
        }

        // Title
        $contentTemplate->title = $this->request->getQueryParam('title');

        // Custom fields
        foreach ($entryType->getFieldLayout()->getCustomFields() as $field) {
            if (($value = $this->request->getQueryParam($field->handle)) !== null) {
                $contentTemplate->setFieldValue($field->handle, $value);
            }
        }

        // Save it
        $contentTemplate->setScenario(Element::SCENARIO_ESSENTIALS);
        $success = Craft::$app->getDrafts()->saveElementAsDraft($contentTemplate, Craft::$app->getUser()->getId(), null, null, false);

        if (!$success) {
            return $this->asModelFailure($contentTemplate, Craft::t('app', 'Couldn’t create {type}.', [
                'type' => ContentTemplate::lowerDisplayName(),
            ]), 'contentTemplate');
        }

        $editUrl = $contentTemplate->getCpEditUrl();

        $response = $this->asModelSuccess($contentTemplate, Craft::t('app', '{type} created.', [
            'type' => ContentTemplate::displayName(),
        ]), 'contentTemplate', array_filter([
            'cpEditUrl' => $this->request->isCpRequest ? $editUrl : null,
        ]));

        if (!$this->request->getAcceptsJson()) {
            $response->redirect(UrlHelper::urlWithParams($editUrl, [
                'fresh' => 1,
            ]));
        }

        return $response;
    }

    /**
     * Applies a content template's content to an entry.
     *
     * @return Response
     */
    public function actionApply(): Response
    {
        $request = Craft::$app->getRequest();
        $elementsService = Craft::$app->getElements();
        $elementId = $request->getRequiredBodyParam('elementId');
        $contentTemplateId = $request->getRequiredBodyParam('contentTemplateId');
        $siteHandle = $request->getQueryParam('site', null);

        // set the current requested site
        if ($siteHandle !== null) {
            $site = Craft::$app->getSites()->getSiteByHandle($siteHandle);
        }

        // query the element based on the current requested site, or the default as fallback
        $element = $elementsService->getElementById($elementId, null, $site->id ?? null);

        // failsafe if we don't find the specified element
        if ($element === null) {
            return $this->asFailure('Failed to find the specified element: ' . $elementId);
        }

        $contentTemplate = $elementsService->getElementById($contentTemplateId);
        $tempDuplicateTemplate = $elementsService->duplicateElement($contentTemplate);
        $element->setFieldValues($tempDuplicateTemplate->getSerializedFieldValues());
        $element->slug = "entry-$elementId";
        $success = $elementsService->saveElement($element, !$element->getIsDraft());
        $elementsService->deleteElement($tempDuplicateTemplate);

        if (!$success) {
            return $this->asFailure('Failed to apply content template to ' . $element::lowerDisplayName());
        }

        return $this->asSuccess(data: [
            'redirect' => UrlHelper::urlWithParams($element->getCpEditUrl(), [
                'fresh' => 1,
            ]),
        ]);
    }

    public function actionSaveConfigOrder(): Response
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $request = Craft::$app->getRequest();
        $typeUid = $request->getRequiredBodyParam('type');
        $elementIds = $request->getRequiredBodyParam('elementIds');
        $indexedElementUids = Db::uidsByIds(Table::ELEMENTS, $elementIds);
        $elementUids = array_map(fn($id) => $indexedElementUids[$id], $elementIds);

        try {
            Craft::$app->getProjectConfig()->set("contentTemplates.orders.$typeUid", $elementUids);
            return $this->asSuccess();
        } catch (\Exception $e) {
            return $this->asFailure($e->getMessage());
        }
    }

    private function _getIndexSettings(): array
    {
        return [
            'entryTypes' => Collection::make(Craft::$app->getEntries()->getAllEntryTypes())
                ->map(fn($entryType) => [
                    'handle' => $entryType->handle,
                    'sites' => null, // TODO
                    'id' => $entryType->id,
                    'name' => Craft::t('site', $entryType->name),
                    'uid' => $entryType->uid,
                ])
                ->all(),
        ];
    }
}
