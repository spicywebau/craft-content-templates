<?php

namespace spicyweb\contenttemplates\services;

use Craft;
use craft\helpers\App;
use craft\helpers\FileHelper;
use spicyweb\contenttemplates\Plugin;
use yii\base\Component;

/**
 * Content Templates preview images service class.
 *
 * @package spicyweb\contenttemplates\services
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 1.0.0
 */
class PreviewImages extends Component
{
    /**
     * Gets the URL of the preview image with the given filename, if it exists.
     *
     * @param string $filename The image filename, relative to the plugin's `previewSource` setting.
     * @param array|null $transform The width and height to scale/crop the image to.
     * @return string|null
     */
    public function getPreviewImageUrl(string $filename, ?array $transform = null): ?string
    {
        $previewSource = Plugin::$plugin->getSettings()->previewSource;
        $generalConfig = Craft::$app->getConfig()->getGeneral();
        $resourceBasePath = rtrim(App::parseEnv($generalConfig->resourceBasePath), DIRECTORY_SEPARATOR);
        $resourceBaseUrl = rtrim(App::parseEnv($generalConfig->resourceBaseUrl), DIRECTORY_SEPARATOR);
        FileHelper::createDirectory($resourceBasePath . DIRECTORY_SEPARATOR . 'content-templates');
        $imagePath = rtrim(App::parseEnv($previewSource), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($filename, DIRECTORY_SEPARATOR);
        $extension = FileHelper::getExtensionByMimeType(FileHelper::getMimeType($imagePath));
        $size = $transform !== null ? "{$transform['width']}x{$transform['height']}" : 'full';
        $relativeImageDest = 'content-templates' . DIRECTORY_SEPARATOR . hash('sha256', $imagePath) . "-$size.$extension";
        $imageDestPath = $resourceBasePath . DIRECTORY_SEPARATOR . $relativeImageDest;
        $imageDestUrl = $resourceBaseUrl . DIRECTORY_SEPARATOR . $relativeImageDest;

        if (!file_exists($imageDestPath)) {
            try {
                $image = Craft::$app->getImages()->loadImage($imagePath);

                if ($transform !== null) {
                    $image->scaleAndCrop($transform['width'], $transform['height']);
                }

                $image->saveAs($imageDestPath);
            } catch (\Exception $e) {
                return null;
            }
        }

        return $imageDestUrl;
    }
}
