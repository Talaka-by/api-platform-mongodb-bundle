<?php

/*
 * This file is part of the TalakaAPIPlatformMongoDBBundle package.
 *
 * (c) Andrew Meshchanchuk <andrew.meshchanchuk@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Talaka\APIPlatform\MongoDBBundle\Doctrine\Odm\Filter;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Dunglas\ApiBundle\Api\Filter\FilterInterface as BaseFilterInterface;
use Dunglas\ApiBundle\Api\ResourceInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Doctrine ODM filter interface.
 *
 * @author Andrew Meshchanchuk <andrew.meshchanchuk@gmail.com>
 */
interface FilterInterface extends BaseFilterInterface
{
    /**
     * Applies the filter.
     *
     * @param ResourceInterface $resource
     * @param QueryBuilder      $queryBuilder
     * @param Request           $request
     */
    public function apply(ResourceInterface $resource, QueryBuilder $queryBuilder, Request $request);
}
