<?php

/*
 * This file is part of the GLAVWEB.cms CmsCompositeObject package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CmsCompositeObject\Service;

use Glavweb\CmsRestClient\CmsRestClient;

/**
 * Class CompositeObjectService
 *
 * @package Glavweb\CmsCompositeObject
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class CompositeObjectService
{
    /**
     * @var CmsRestClient
     */
    private $cmsRestClient;

    /**
     * CompositeObjectService constructor.
     */
    public function __construct(CmsRestClient $cmsRestClient)
    {
        $this->cmsRestClient = $cmsRestClient;
    }

    /**
     * @param string $className
     * @return array
     */
    public function getObjectsByClassName($className)
    {
        $cmsRestClient = $this->cmsRestClient;

        $objectsResponse = $response = $cmsRestClient->get('composite-object/objects', [
            'query' => [
                '_sort[position]' => 'desc',
                'className'       => $className
            ]
        ]);
        $objectList = \GuzzleHttp\json_decode($objectsResponse->getBody(), true);

        return $objectList;
    }

    /**
     * Editable block
     *
     * @param int $id
     * @return string
     */
    public function editable($id)
    {
        $attributes = [];
        $attributes['data-content-object']    = 'true';
        $attributes['data-content-object-id'] = $id;

        $attrParts = array();
        foreach ($attributes as $attrName => $attrValue) {
            $attrParts[] = sprintf('%s = "%s"', $attrName, $attrValue);
        }

        return ' ' . implode(' ', $attrParts);
    }
}