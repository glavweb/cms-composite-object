<?php

namespace Glavweb\CmsCompositeObject\Helper;

/**
 * Class AbstractFixtureHelper
 *
 * @package Glavweb\CmsCompositeObject
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
abstract class AbstractFixtureHelper
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
     * @param string $fieldValue
     * @return string
     */
    protected function addBasePath($fieldValue)
    {
        return $this->basePath . '/' . $fieldValue;
    }

    /**
     * @param string $uri
     * @return bool
     */
    protected function isExternalUri($uri)
    {
        $components = parse_url($uri);

        return isset($components['host']) && isset($components['scheme']);
    }

    /**
     * @param array $class
     * @param $fieldName
     * @return array
     */
    protected function getFieldDefinitionByName(array $class, $fieldName)
    {
        $fields = $class['fields'];

        foreach ($fields as $fieldDefinition) {
            if ($fieldDefinition['name'] == $fieldName) {
                return $fieldDefinition;
            }
        }

        throw new \RuntimeException(sprintf('The field definition "%s" is not defined.', $fieldName));
    }
}