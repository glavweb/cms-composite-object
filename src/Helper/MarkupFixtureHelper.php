<?php

namespace Glavweb\CmsCompositeObject\Helper;

/**
 * Class FixtureMarkupHelper
 *
 * @package Glavweb\CmsCompositeObject
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class MarkupFixtureHelper extends AbstractFixtureHelper
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * AbstractFixtureHelper constructor.
     *
     * @param string $basePath
     */
    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * @param array $fixture
     * @return array
     */
    public function prepareFixtureForMarkup(array $fixture)
    {
        $class     = $fixture['class'];
        $instances = isset($fixture['instances']) ? $fixture['instances'] : [];

        $prepared = [];
        foreach ($instances as $key => $instance) {
            foreach ($instance as $fieldName => $fieldValue) {
                $fieldDefinition = $this->getFieldDefinitionByName($class, $fieldName);

                $fieldType = $fieldDefinition['type'];
                switch ($fieldType) {
                    case 'image' :
                        $instance[$fieldName] = $this->imageMarkupData($fieldValue);
                        break;

                    case 'image_collection' :
                        $instance[$fieldName] = $this->imageCollectionMarkupData($fieldValue);
                        break;

                    case 'video' :
                        $instance[$fieldName] = $this->videoMarkupData($fieldValue);
                        break;

                    case 'video_collection' :
                        $instance[$fieldName] = $this->videoCollectionMarkupData($fieldValue);
                        break;
                }
            }
            $instance['id'] = uniqid();

            $prepared[] = $instance;
        }

        return $prepared;
    }

    /**
     * @param string $image
     * @return array
     */
    private function imageMarkupData($image)
    {
        $imagePath = $image;
        if (!$this->isExternalUri($image)) {
            $imagePath = $this->addBasePath($image);
        }

        return [
            'id'                 => uniqid(),
            'name'               => 'Name for image',
            'description'        => 'description for image',
            'thumbnail'          => $imagePath,
            'thumbnail_path'     => $imagePath,
            'content_path'       => $image,
            'content_type'       => 'image/jpeg',
            'content_size'       => 105336,
            'width'              => null,
            'height'             => null,
            'provider_reference' => null
        ];
    }

    /**
     * @param string $video
     * @return array
     */
    private function videoMarkupData($video)
    {
        $providerReference = $this->getYouTubeProviderReferenceByUrl($video);
        $thumbnail = $this->addBasePath('dummy/dummy_video.jpg');

        return [
            'id'                 => uniqid(),
            'name'               => 'Name for video',
            'description'        => 'description for video',
            'thumbnail'          => $thumbnail,
            'thumbnail_path'     => $thumbnail,
            'content_type'       => 'video/x-flv',
            'content_size'       => null,
            'width'              => 480,
            'height'             => 270,
            'provider_reference' => $providerReference
        ];
    }

    /**
     * @param array $imageCollection
     * @return array
     */
    private function imageCollectionMarkupData(array $imageCollection)
    {
        $prepared = [];
        foreach ($imageCollection as $image) {
            $prepared[] = $this->imageMarkupData($image);
        }

        return $prepared;
    }

    /**
     * @param array $videoCollection
     * @return array
     */
    private function videoCollectionMarkupData(array $videoCollection)
    {
        $prepared = [];
        foreach ($videoCollection as $video) {
            $prepared[] = $this->videoMarkupData($video);
        }

        return $prepared;
    }

    /**
     * @param string $url
     * @return string
     */
    private function getYouTubeProviderReferenceByUrl($url)
    {
        if (preg_match("/(?<=v(\=|\/))([-a-zA-Z0-9_]+)|(?<=youtu\.be\/)([-a-zA-Z0-9_]+)/", $url, $matches)) {
            return $matches[0];
        }

        return null;
    }

    /**
     * @param string $fieldValue
     * @return string
     */
    private function addBasePath($fieldValue)
    {
        return $this->basePath . '/' . $fieldValue;
    }
}