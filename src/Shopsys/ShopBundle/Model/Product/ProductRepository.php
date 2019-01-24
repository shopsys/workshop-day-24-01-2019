<?php

namespace Shopsys\ShopBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository as BaseProductRepository;

class ProductRepository extends BaseProductRepository
{
    /**
     * @param int $extId
     * @return \Shopsys\ShopBundle\Model\Product\Product|null
     */
    public function findByExternalId(int $extId)
    {
        return $this->getProductRepository()->findOneBy(['extId' => $extId]);
    }

    /**
     * @param array $productsIds
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\ShopBundle\Model\Product\Product[]
     */
    public function getVisibleByIds(array $productsIds, $domainId, PricingGroup $pricingGroup)
    {
        if (count($productsIds) === 0) {
            return [];
        }

        $qb = $this->getAllVisibleQueryBuilder($domainId, $pricingGroup);

        $qb->andWhere('p.id IN (:productsIds)');
        $qb->setParameter('productsIds', $productsIds);

        return $qb->getQuery()->execute();
    }
}
