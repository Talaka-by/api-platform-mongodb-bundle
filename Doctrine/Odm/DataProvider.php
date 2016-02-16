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

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\MongoDB\Cursor;
use Talaka\APIPlatform\MongoDBBundle\Doctrine\Odm\Filter\FilterInterface;
use Dunglas\ApiBundle\Model\DataProviderInterface;
use Dunglas\ApiBundle\Api\ResourceInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Data provider for the Doctrine ODM.
 *
 * @author Andrew Meshchanchuk <andrew.meshchanchuk@gmail.com>
 */
class DataProvider implements DataProviderInterface
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;
    /**
     * @var string|null
     */
    private $order;
    /**
     * @var string
     */
    private $pageParameter;
    /**
     * @var int
     */
    private $itemsPerPage;
    /**
     * @var bool
     */
    private $enableClientRequestItemsPerPage;
    /**
     * @var string
     */
    private $itemsPerPageParameter;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param string|null     $order
     * @param string          $pageParameter
     * @param int             $itemsPerPage
     * @param bool            $enableClientRequestItemsPerPage
     * @param string          $itemsPerPageParameter
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        $order,
        $pageParameter,
        $itemsPerPage,
        $enableClientRequestItemsPerPage,
        $itemsPerPageParameter
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->order = $order;
        $this->pageParameter = $pageParameter;
        $this->itemsPerPage = $itemsPerPage;
        $this->enableClientRequestItemsPerPage = $enableClientRequestItemsPerPage;
        $this->itemsPerPageParameter = $itemsPerPageParameter;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem(ResourceInterface $resource, $id, $fetchData = false)
    {
        $entityClass = $resource->getEntityClass();
        $manager = $this->managerRegistry->getManagerForClass($entityClass);

        if ($fetchData || !method_exists($manager, 'getReference')) {
            return $manager->find($entityClass, $id);
        }

        return $manager->getReference($entityClass, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection(ResourceInterface $resource, Request $request)
    {
        $entityClass = $resource->getEntityClass();

        $manager = $this->managerRegistry->getManagerForClass($resource->getEntityClass());
        /** @var \Doctrine\ODM\MongoDB\DocumentRepository $repository */
        $repository = $manager->getRepository($entityClass);

        $page = (int) $request->get($this->pageParameter, 1);

        $itemsPerPage = $this->itemsPerPage;
        if ($this->enableClientRequestItemsPerPage && $requestedItemsPerPage = $request->get($this->itemsPerPageParameter)) {
            $itemsPerPage = (int) $requestedItemsPerPage;
        }

        /** @var \Doctrine\ODM\MongoDB\Query\Builder $queryBuilder */
        $queryBuilder = $repository
            ->createQueryBuilder()
            ->skip(($page - 1) * $itemsPerPage)
            ->limit($itemsPerPage)
        ;

        foreach ($resource->getFilters() as $filter) {
            if ($filter instanceof FilterInterface) {
                $filter->apply($resource, $queryBuilder, $request);
            }
        }

        $classMetaData = $manager->getClassMetadata($entityClass);
        $identifiers = $classMetaData->getIdentifier();

        if (null !== $this->order && 1 === count($identifiers)) {
            $identifier = $identifiers[0];
            $queryBuilder->sort($identifier, $this->order);
        }

        $cursor = $queryBuilder->getQuery()->execute();

        return $this->getPaginator($cursor);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ResourceInterface $resource)
    {
        return null !== $this->managerRegistry->getManagerForClass($resource->getEntityClass());
    }

    /**
     * Gets the paginator.
     *
     * @param Cursor $cursor
     *
     * @return Paginator
     */
    protected function getPaginator(Cursor $cursor)
    {
        return new Paginator($cursor);
    }
}
