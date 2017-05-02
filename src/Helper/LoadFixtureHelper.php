<?php

/*
 * This file is part of the GLAVWEB.cms CmsCompositeObject package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CmsCompositeObject\Helper;

/**
 * Class LoadFixtureHelper
 *
 * @package Glavweb\CmsCompositeObject
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class LoadFixtureHelper
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var bool
     */
    private $asBase64;

    /**
     * AbstractFixtureHelper constructor.
     *
     * @param string $rootDir
     * @param bool $asBase64
     */
    public function __construct(string $rootDir, bool $asBase64 = true)
    {
        $this->rootDir  = $rootDir;
        $this->asBase64 = $asBase64;
    }

    /**
     * @param array $fixture
     * @return array
     */
    public function getFixtureClassesOnly(array $fixture): array
    {
        $fixtureClassesOnly = [];
        foreach ($fixture as $fixtureNamespace => $fixtureItem) {
            $classData = $fixtureItem['class'];

            $fixtureClassesOnly[$fixtureNamespace] = [
                'class' => $classData
            ];
        }

        return $fixtureClassesOnly;
    }

    /**
     * @param array $fixtures
     * @return array
     */
    public function prepareFixturesForLoad(array $fixtures): array
    {
        foreach ($fixtures as $fixtureName => $fixture) {
            $class     = $fixture['class'];
            $instances = isset($fixture['instances']) ? $fixture['instances'] : [];

            foreach ($instances as $instanceKey => $instance) {
                $fixtures[$fixtureName]['instances'][$instanceKey] = $this->prepareFixtureInstanceForLoad($instance, $class);

                $fixtures[$fixtureName]['instances'] = array_reverse($fixtures[$fixtureName]['instances']);
            }
        }

        return $fixtures;
    }

    /**
     * @param string $file
     * @return string
     */
    private function prepareImageData(string $file): string
    {
        if ($this->asBase64) {
            if ($this->isExternalUri($file)) {
                $imageContent = $this->getGetContentByCurl($file);

            } else {
                $file = $this->addRootDir($file);
                $imageContent = file_get_contents($file);
            }

            return $this->convertToBase64($imageContent);
        }

        if (!$this->isExternalUri($file)) {
            $file = $this->addRootDir($file);
        }

        return $file;
    }

    /**
     * @param array $collection
     * @return array
     */
    private function prepareImageCollectionData(array $collection): array
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
    private function convertToBase64(string $fileContent): string
    {
        return base64_encode($fileContent);
    }

    /**
     * @param string $value
     * @return string
     */
    private function addRootDir(string $value): string
    {
        return $this->rootDir . '/' . $value;
    }

    /**
     * @param string $file
     * @return mixed
     */
    private function getGetContentByCurl(string $file)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $file);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $fileContent = curl_exec($curl);
        curl_close($curl);

        return $fileContent;
    }

    /**
     * @param string $uri
     * @return bool
     */
    private function isExternalUri(string $uri): bool
    {
        $components = parse_url($uri);

        return isset($components['host']) && isset($components['scheme']);
    }

    /**
     * @param array $class
     * @param string $fieldName
     * @return array
     */
    private function getFieldDefinitionByName(array $class, string $fieldName): array
    {
        $fields = $class['fields'];

        foreach ($fields as $fieldDefinition) {
            if ($fieldDefinition['name'] == $fieldName) {
                return $fieldDefinition;
            }
        }

        throw new \RuntimeException(sprintf('The field definition "%s" is not defined.', $fieldName));
    }

    /**
     * @param array $instance
     * @param array $class
     * @return array
     */
    public function prepareFixtureInstanceForLoad(array $instance, array $class): array
    {
        foreach ($instance as $fieldName => $fieldValue) {
            $fieldDefinition = $this->getFieldDefinitionByName($class, $fieldName);
            $fieldType = $fieldDefinition['type'];

            if ($fieldType == 'image') {
                $instance[$fieldName] = $this->prepareImageData($fieldValue);

            } elseif ($fieldType == 'image_collection') {
                $instance[$fieldName] = $this->prepareImageCollectionData($fieldValue);
            }
        }

        return $instance;
    }
}