<?php

/*
 * This file is part of the GLAVWEB.cms CmsCompositeObject package.
 *
 * (c) Andrey Nilov <nilov@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CmsCompositeObject\Manager;

use Glavweb\CmsRestClient\CmsRestClient;
use Glavweb\MarkupFixture\Helper\MarkupFixtureHelper;

/**
 * Class CompositeObjectManager
 *
 * @package Glavweb\CmsCompositeObject
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class CompositeObjectManager
{
    /**
     * @var CmsRestClient
     */
    private $cmsRestClient;

    /**
     * @var bool
     */
    private $markupMode;

    /**
     * @var array
     */
    private $fixtureObjects;

    /**
     * @var MarkupFixtureHelper
     */
    private $markupFixtureHelper;

    /**
     * CompositeObjectManager constructor.
     *
     * @param CmsRestClient       $cmsRestClient
     * @param bool                $markupMode
     * @param array               $fixtureObjects
     * @param MarkupFixtureHelper $markupFixtureHelper
     */
    public function __construct(CmsRestClient $cmsRestClient, MarkupFixtureHelper $markupFixtureHelper, $markupMode = false, $fixtureObjects = [])
    {
        $this->cmsRestClient       = $cmsRestClient;
        $this->markupMode          = $markupMode;
        $this->fixtureObjects      = $fixtureObjects;
        $this->markupFixtureHelper = $markupFixtureHelper;
    }

    /**
     * @param string $className
     * @return array
     */
    public function getObjectsByClassName($className)
    {
        if ($this->markupMode) {
            if (!$this->fixtureObjects[$className]['class']) {
                throw new \RuntimeException(sprintf('The fixture object for class name "%s" not found', $className));
            }

            $fixture = $this->fixtureObjects[$className];

            return $this->markupFixtureHelper->prepareFixtureForMarkup($fixture);
        }

        $cmsRestClient = $this->cmsRestClient;

        $objectsResponse = $response = $cmsRestClient->get('composite-object/objects', [
            'query' => [
                '_sort[position]' => 'asc',
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
