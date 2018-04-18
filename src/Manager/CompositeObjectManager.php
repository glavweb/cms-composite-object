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
     * @var MarkupFixtureHelper
     */
    private $markupFixtureHelper;

    /**
     * @var bool
     */
    private $markupMode;

    /**
     * @var array
     */
    private $fixtureObjects;

    /**
     * CompositeObjectManager constructor.
     *
     * @param CmsRestClient       $cmsRestClient
     * @param MarkupFixtureHelper $markupFixtureHelper
     * @param bool                $markupMode
     * @param array               $fixtureObjects
     */
    public function __construct(CmsRestClient $cmsRestClient, MarkupFixtureHelper $markupFixtureHelper, bool $markupMode = false, array $fixtureObjects = [])
    {
        $this->cmsRestClient       = $cmsRestClient;
        $this->markupMode          = $markupMode;
        $this->markupFixtureHelper = $markupFixtureHelper;
        $this->fixtureObjects      = $fixtureObjects;
    }

    /**
     * @param string $className
     * @param array $filter
     * @param array $sort
     * @param int|null $limit
     * @param int|null $skip
     * @param array $projection
     * @return array
     */
    public function getObjects(string $className, array $filter = [], $sort = [], int $limit = null, int $skip = null, array $projection = []): array
    {
        if ($this->markupMode) {
            if (!$this->fixtureObjects[$className]['class']) {
                throw new \RuntimeException(sprintf('The fixture object for class name "%s" not found', $className));
            }

            $fixture = $this->fixtureObjects[$className];

            return $this->markupFixtureHelper->prepareFixtureForMarkup($fixture);
        }

        $cmsRestClient = $this->cmsRestClient;

        $objectsResponse = $response = $cmsRestClient->get('composite-objects/' . $className, [
            'query' => [
                'filter'     => json_encode($filter),
                'sort'       => json_encode($sort),
                'limit'      => $limit,
                'skip'       => $skip,
                'projection' => json_encode($projection)
            ]
        ]);
        $objectList = (array)\GuzzleHttp\json_decode($objectsResponse->getBody(), true);

        return $objectList;
    }

    /**
     * @param string $className
     * @param array  $projection
     * @param int    $id
     * @return array
     */
    public function getObject(string $className, int $id, array $projection = []): array
    {
        $cmsRestClient = $this->cmsRestClient;

        $objectsResponse = $response = $cmsRestClient->get('composite-objects/' . $className . '/' . $id, [
            'query' => [
                'projection' => $projection
            ]
        ]);
        $objectList = (array)\GuzzleHttp\json_decode($objectsResponse->getBody(), true);

        return $objectList;
    }

    /**
     * Editable block
     *
     * @param int|string $id
     * @return string
     */
    public function editable($id): string
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