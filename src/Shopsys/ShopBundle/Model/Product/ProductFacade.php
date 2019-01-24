<?php

namespace Shopsys\ShopBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductFacade as BaseProductFacade;

class ProductFacade extends BaseProductFacade
{
    /**
     * @param int $extId
     * @return \Shopsys\ShopBundle\Model\Product\Product|null
     */
    public function findByExternalId(int $extId)
    {
        return $this->productRepository->findByExternalId($extId);
    }
}
