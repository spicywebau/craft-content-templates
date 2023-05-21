<?php
/*
This class is based on part of `\craft\elements\Entry` from Craft CMS 4.3.10, by Pixel & Tonic, Inc.
https://github.com/craftcms/cms/blob/4.3.10/src/elements/Entry.php
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

namespace spicyweb\contenttemplates\elements;

use benf\neo\Field as NeoField;
use benf\neo\Plugin as Neo;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\db\Query;
use craft\db\Table;
use craft\elements\Asset;
use craft\elements\Entry;
use craft\elements\User;
use craft\fieldlayoutelements\entries\EntryTitleField;
use craft\fields\Matrix as MatrixField;
use craft\helpers\Cp;
use craft\helpers\UrlHelper;
use craft\models\EntryType;
use craft\models\FieldLayout;
use craft\models\Section;
use spicyweb\contenttemplates\elements\db\ContentTemplateQuery;
use spicyweb\contenttemplates\Plugin;
use verbb\supertable\fields\SuperTableField;
use verbb\supertable\SuperTable;
use yii\db\Expression;
use yii\web\Response;

/**
 * Content template element class.
 *
 * @package spicyweb\contenttemplates\elements
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 1.0.0
 */
class ContentTemplate extends Element
{
    /**
     * @var ?int The ID of the entry type this content template is for.
     */
    public ?int $typeId = null;

    /**
     * @var int|null The content template's preview asset ID.
     */
    public ?int $previewId = null;

    /**
     * @var ?string The description of this content template.
     */
    public ?string $description = null;

    /**
     * @var ?Section
     */
    private ?Section $_section = null;

    /**
     * @var ?EntryType
     */
    private ?EntryType $_entryType = null;

    /**
     * @var Asset|null
     */
    private ?Asset $_preview = null;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('content-templates', 'Content Template');
    }

    /**
     * @inheritdoc
     */
    public static function lowerDisplayName(): string
    {
        return Craft::t('content-templates', 'content template');
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('content-templates', 'Content Templates');
    }

    /**
     * @inheritdoc
     */
    public static function pluralLowerDisplayName(): string
    {
        return Craft::t('content-templates', 'content templates');
    }

    /**
     * @inheritdoc
     */
    public static function refHandle(): ?string
    {
        return 'contenttemplate';
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function find(): ContentTemplateQuery
    {
        return new ContentTemplateQuery(static::class);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context): array
    {
        $sources = [];

        foreach (Craft::$app->getSections()->getAllEntryTypes() as $entryType) {
            $section = $entryType->getSection();
            $sources[] = [
                'key' => 'entryType:' . $entryType->uid,
                'label' => Craft::t('site', $section->name . ' - ' . $entryType->name),
                'sites' => $section->getSiteIds(),
                'data' => [
                    'handle' => $section->handle . '/' . $entryType->handle,
                ],
                'criteria' => [
                    'typeId' => $entryType->id,
                ],
                'defaultSort' => ['dateUpdated', 'desc'],
            ];
        }

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            [
                'label' => Craft::t('content-templates', 'Date Updated'),
                'orderBy' => function(int $dir) {
                    if ($dir === SORT_ASC) {
                        if (Craft::$app->getDb()->getIsMysql()) {
                            return new Expression('[[elements.dateUpdated]] IS NOT NULL DESC, [[elements.dateUpdated]] ASC');
                        } else {
                            return new Expression('[[elements.dateUpdated]] ASC NULLS LAST');
                        }
                    }
                    if (Craft::$app->getDb()->getIsMysql()) {
                        return new Expression('[[elements.dateUpdated]] IS NULL DESC, [[elements.dateUpdated]] DESC');
                    } else {
                        return new Expression('[[elements.dateUpdated]] DESC NULLS FIRST');
                    }
                },
                'attribute' => 'dateUpdated',
                'defaultDir' => 'desc',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['typeId'], 'number', 'integerOnly' => true];
        $rules[] = [['description'], 'string'];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function prepareEditScreen(Response $response, string $containerId): void
    {
        $entryType = $this->getEntryType();
        $section = $this->getSection();
        $response->crumbs([
            [
                'label' => self::pluralDisplayName(),
                'url' => UrlHelper::cpUrl('content-templates'),
            ],
            [
                'label' => Craft::t('site', '{section} - {entryType}', [
                    'section' => $section->name,
                    'entryType' => $entryType->name,
                ]),
                'url' => UrlHelper::cpUrl("content-templates/$section->handle/$entryType->handle"),
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew): void
    {
        $projectConfig = Craft::$app->getProjectConfig();

        // If this element has saved as a result of applying project config changes, opt out of infinite recursion
        if ($projectConfig->getIsApplyingExternalChanges()) {
            return;
        }

        $request = Craft::$app->getRequest();
        $previewId = $request->getBodyParam('previewId') ?: null;
        $this->previewId = $previewId ? reset($previewId) : null;
        $this->description = $request->getBodyParam('description');
        $config = $this->getConfig();

        if ($this->getIsDraft()) {
            Plugin::$plugin->projectConfig->save($this->uid, $config);
        } else {
            $projectConfig->set("contentTemplates.$this->uid", $config);
        }
    }

    /**
     * Gets this content template's preview asset, if one is set.
     *
     * @return Asset|null
     */
    public function getPreview(): ?Asset
    {
        if ($this->_preview === null && $this->previewId !== null) {
            $this->_preview = Craft::$app->getAssets()->getAssetById($this->previewId);
        }

        return $this->_preview;
    }

    /**
     * Returns this content template's config.
     *
     * @return array
     */
    public function getConfig(): array
    {
        $request = Craft::$app->getRequest();
        $preview = $this->getPreview();

        if ($preview) {
            $previewData = [
                'volume' => $preview->getVolume()->uid,
                'folderPath' => $preview->getFolder()->path,
                'filename' => $preview->getFilename(),
            ];
        } else {
            $previewData = null;
        }

        return [
            'title' => $this->title,
            'type' => $this->getEntryType()->uid,
            'preview' => $previewData,
            'content' => $this->_serializedFieldValuesWithoutBlockIds(),
            'description' => method_exists($request, 'getBodyParam')
                ? $this->description ?? Craft::$app->getRequest()->getBodyParam('description')
                : $this->description,
        ];
    }

    /**
     * Removes Neo / Matrix / Super Table block IDs from field values.
     */
    private function _serializedFieldValuesWithoutBlockIds(
        ?array $serializedValues = null,
        ?int $fieldLayoutId = null
    ): array {
        if ($serializedValues === null) {
            $serializedValues = $this->getSerializedFieldValues();
        }

        if ($fieldLayoutId === null) {
            $fieldLayoutId = $this->getEntryType()->fieldLayoutId;
        }

        $elementsService = Craft::$app->getElements();
        $fieldsService = Craft::$app->getFields();
        $fields = [];
        $removeIdsFrom = [
            NeoField::class,
            MatrixField::class,
            SuperTableField::class,
        ];

        if ($fieldLayoutId !== null) {
            foreach ($fieldsService->getLayoutById($fieldLayoutId)?->getCustomFieldElements() ?? [] as $customFieldElement) {
                $field = $customFieldElement->getField();
                $fields[$field->handle] = $field;
            }
        }

        foreach (array_keys($serializedValues) as $fieldHandle) {
            if (!isset($fields[$fieldHandle])) {
                // Assume property rather than field
                continue;
            }

            $field = $fields[$fieldHandle];
            $fieldClass = get_class($field);

            if (!in_array($fieldClass, $removeIdsFrom)) {
                continue;
            }

            $i = 1;

            foreach ($serializedValues[$fieldHandle] as $oldBlockId => $blockValue) {
                $blockLayoutId = match ($fieldClass) {
                    NeoField::class => (new Query())
                        ->select(['fieldLayoutId'])
                        ->from('{{%neoblocktypes}}')
                        ->where([
                            'fieldId' => $field->id,
                            'handle' => $blockValue['type'],
                        ])
                        ->scalar(),
                    MatrixField::class => (new Query())
                        ->select(['fieldLayoutId'])
                        ->from(Table::MATRIXBLOCKTYPES)
                        ->where([
                            'fieldId' => $field->id,
                            'handle' => $blockValue['type'],
                        ])
                        ->scalar(),
                    SuperTableField::class => SuperTable::$plugin->getService()
                        ->getBlockTypeById($blockValue['type'])
                        ->fieldLayoutId,
                };
                $blockValue['fields'] = $this->_serializedFieldValuesWithoutBlockIds(
                    $blockValue['fields'],
                    $blockLayoutId,
                );
                $serializedValues[$fieldHandle]['new' . $i++] = $blockValue;
                unset($serializedValues[$fieldHandle][$oldBlockId]);
            }
        }

        return $serializedValues;
    }

    /**
     * @inheritdoc
     */
    public function afterDelete(): void
    {
        $projectConfig = Craft::$app->getProjectConfig();

        if (!$projectConfig->getIsApplyingExternalChanges()) {
            $projectConfig->remove("contentTemplates.$this->uid");
        }

        parent::afterDelete();
    }

    public function getSection(): ?Section
    {
        if ($this->_section === null && $this->typeId !== null) {
            $this->_section = Craft::$app->getSections()->getSectionById($this->getEntryType()->sectionId);
        }

        return $this->_section;
    }

    public function getEntryType(): ?EntryType
    {
        if ($this->_entryType === null && $this->typeId !== null) {
            $this->_entryType = Craft::$app->getSections()->getEntryTypeById($this->typeId);
            // Set the section while we're here
            $this->getSection();
        }

        return $this->_entryType;
    }

    /**
     * @inheritdoc
     */
    public function canView(User $user): bool
    {
        return $this->_can('view', $user);
    }

    /**
     * @inheritdoc
     */
    public function canSave(User $user): bool
    {
        return $this->_can('save', $user);
    }

    /**
     * @inheritdoc
     */
    public function canDelete(User $user): bool
    {
        // Fall back to the save permission for single sections, which would otherwise always return false
        return $this->getSection()->type !== Section::TYPE_SINGLE
            ? $this->_can('delete', $user)
            : $this->_can('save', $user);
    }

    /**
     * Common code for checking user permissions.
     *
     * @param string $action
     * @param User $user
     * @return bool Whether $user can do $action
     */
    private function _can(string $action, User $user): bool
    {
        $can = 'can' . ucfirst($action);
        return parent::{$can}($user) ? true : $this->_mockEntryForPermissionChecks()->{$can}($user);
    }

    /**
     * Creates an entry with this content template's entry type, for checking user permissions.
     *
     * @return Entry
     */
    private function _mockEntryForPermissionChecks(): Entry
    {
        $entryType = $this->getEntryType();
        $mockEntry = new Entry();
        $mockEntry->sectionId = $entryType->sectionId;
        $mockEntry->setTypeId($entryType->id);

        return $mockEntry;
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayout(): ?FieldLayout
    {
        if (($fieldLayout = parent::getFieldLayout()) !== null) {
            return $this->_fieldLayoutWithoutEntryTitleField($fieldLayout);
        }
        try {
            $entryType = $this->getEntryType();
        } catch (InvalidConfigException) {
            // The entry type was probably deleted
            return null;
        }

        return $this->_fieldLayoutWithoutEntryTitleField($entryType->getFieldLayout());
    }

    /**
     * Hacky stuff to remove the EntryTitleField
     */
    private function _fieldLayoutWithoutEntryTitleField(FieldLayout $fieldLayout): FieldLayout
    {
        foreach ($fieldLayout->getTabs() as $tab) {
            $tab->setElements(array_filter($tab->getElements(), fn($element) => !$element instanceof EntryTitleField));
        }

        return $fieldLayout;
    }

    /**
     * @inheritdoc
     */
    protected function cpEditUrl(): ?string
    {
        $entryType = $this->getEntryType();
        $section = $entryType->getSection();
        $path = sprintf('content-templates/%s/%s/%s', $section->handle, $entryType->handle, $this->getCanonicalId());

        return UrlHelper::cpUrl($path);
    }

    /**
     * @inheritdoc
     */
    public function metaFieldsHtml(bool $static): string
    {
        $preview = $this->getPreview();
        $fields = [];
        $fields[] = Cp::textFieldHtml([
            'label' => Craft::t('app', 'Title'),
            'id' => 'title',
            'name' => 'title',
            'autocorrect' => false,
            'autocapitalize' => false,
            'value' => $this->title,
            'disabled' => $static,
            'errors' => $this->getErrors('title'),
        ]);
        $fields[] =  Cp::elementSelectFieldHtml([
            'label' => Craft::t('content-templates', 'Preview'),
            'id' => 'previewId',
            'name' => 'previewId',
            'elementType' => Asset::class,
            'selectionLabel' => Craft::t('app', 'Choose'),
            'sources' => '*',
            'viewMode' => 'large',
            'criteria' => [
                'kind' => 'image',
            ],
            'limit' => 1,
            'value' => $preview ? [$preview->id] : null,
            'elements' => $preview ? [$preview] : [],
            'disabled' => $static,
            'describedBy' => 'previewId-label',
            'errors' => $this->getErrors('previewId'),
        ]);
        $fields[] = Cp::textareaFieldHtml([
            'label' => Craft::t('content-templates', 'Description'),
            'id' => 'description',
            'name' => 'description',
            'value' => $this->description,
            'disabled' => $static,
            'errors' => $this->getErrors('description'),
            'inputAttributes' => [
                'aria' => [
                    'label' => Craft::t('content-templates', 'The description to use for this content template.'),
                ],
            ],
        ]);
        $fields[] = parent::metaFieldsHtml($static);

        return implode("\n", $fields);
    }
}
