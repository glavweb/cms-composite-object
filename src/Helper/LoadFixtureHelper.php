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
     * @var string
     */
    private $rootDir;

    /**
     * AbstractFixtureHelper constructor.
     *
     * @param string $rootDir
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

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
     * @param string $file
     * @return string
     */
    private function prepareImageData($file)
    {
        if ($this->isExternalUri($file)) {
            $imageContent = $this->getGetContentByCurl($file);

        } else {
            $file = $this->addRootDir($file);
            $imageContent = file_get_contents($file);
        }

        return $this->convertToBase64($imageContent);
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
     * @param string $fileContent
     * @return string
     */
    private function convertToBase64($fileContent)
    {
        return base64_encode($fileContent);
    }

    /**
     * @param string $value
     * @return string
     */
    private function addRootDir($value)
    {
        return $this->rootDir . '/' . $value;
    }

    /**
     * @param string $file
     * @return mixed
     */
    private function getGetContentByCurl($file)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $file);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $fileContent = curl_exec($curl);
        curl_close($curl);

        return $fileContent;
    }
}