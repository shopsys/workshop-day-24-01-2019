<?php

namespace Shopsys\ShopBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacade as BaseProductOnCurrentDomainFacade;

class ProductOnCurrentDomainFacade extends BaseProductOnCurrentDomainFacade
{
    /**
     * @param array $productsIds
     * @return \Shopsys\ShopBundle\Model\Product\Product[]
     */
    public function getVisibleProductsByIds(array $productsIds)
    {
        return $this->productRepository->getVisibleByIds(
            $productsIds,
            $this->domain->getId(),
            $this->currentCustomer->getPricingGroup()
        );
    }
}
