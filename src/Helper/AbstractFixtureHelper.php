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