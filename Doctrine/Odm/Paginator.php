<?php

/*
 * This file is part of the TalakaAPIPlatformMongoDBBundle package.
 *
 * (c) Andrew Meshchanchuk <andrew.meshchanchuk@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Talaka\APIPlatform\MongoDBBundle\Doctrine\Odm;

use Dunglas\ApiBundle\Model\PaginatorInterface;
use Doctrine\ODM\MongoDB\Cursor;

/**
 * Decorates the Doctrine ODM paginator.
 *
 * @author Andrew Meshchanchuk <andrew.meshchanchuk@gmail.com>
 */
class Paginator implements \Countable, \IteratorAggregate, PaginatorInterface
{
    /**
     * @var Cursor
     */
    private $cursor;
    /**
     * @var int
     */
    private $firstResult;
    /**
     * @var int
     */
    private $maxResults;
    /**
     * @var int
     */
    private $totalItems;

    public function __construct(Cursor $cursor)
    {
        $this->cursor = $cursor;

        $info = $cursor->info();

        $this->firstResult = $info['skip'];
        $this->maxResults = $info['limit'];
        $this->totalItems = $cursor->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPage()
    {
        return floor($this->firstResult / $this->maxResults) + 1.;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastPage()
    {
        return ceil($this->totalItems / $this->maxResults) ?: 1.;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsPerPage()
    {
        return (float) $this->maxResults;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalItems()
    {
        return (float) $this->totalItems;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->cursor->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->getIterator());
    }
}
