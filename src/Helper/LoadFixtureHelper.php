<?php

namespace Glavweb\CmsCompositeObject\Helper;

/**
 * Class LoadFixtureHelper
 *
 * @package Glavweb\CmsCompositeObject
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class LoadFixtureHelper extends AbstractFixtureHelper
{
    /**
     * @param array $fixtures
     * @return array
     */
    public function prepareFixturesForLoad(array $fixtures)
    {
        foreach ($fixtures as $fixtureName => $fixture) {
            $class     = $fixture['class'];
            $instances = isset($fixture['instances']) ? $fixture['instances'] : [];

            foreach ($instances as $instanceKey => $instance) {
                foreach ($instance as $fieldName => $fieldValue) {
                    $fieldDefinition = $this->getFieldDefinitionByName($class, $fieldName);
                    $fieldType = $fieldDefinition['type'];

                    if ($fieldType == 'image') {
                        $fixtures[$fixtureName]['instances'][$instanceKey][$fieldName] = $this->prepareImageData($fieldValue);

                    } elseif ($fieldType == 'image_collection') {
                        $fixtures[$fixtureName]['instances'][$instanceKey][$fieldName] = $this->prepareImageCollectionData($fieldValue);
                    }
                }

                $fixtures[$fixtureName]['instances'] = array_reverse($fixtures[$fixtureName]['instances']);
            }
        }

        return $fixtures;
    }

    /**
     * @param string $value
     * @return string
     */
    private function prepareImageData($value)
    {
        if (!$this->isExternalUri($value)) {
            $value = $this->addBasePath($value);
        }

        return $this->fileToBase($value);
    }

    /**
     * @param array $collection
     * @return array
     */
    private function prepareImageCollectionData($collection)
    {
        $prepared = [];
        foreach ($collection as $image) {
            $prepared[] = $this->prepareImageData($image);
        }

        return $prepared;
    }

    /**
     * @param string $file
     * @return string
     */
    private function fileToBase($file)
    {
        $imageData = file_get_contents($file);

        return base64_encode($imageData);
    }
}