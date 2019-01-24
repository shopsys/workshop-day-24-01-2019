<?php

namespace Shopsys\ShopBundle\Model\Pricing\Vat;

use Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatNotFoundException;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade as BaseVatFacade;

class VatFacade extends BaseVatFacade
{
    /**
     * @param $vatPercent
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function getVatByPercent($vatPercent)
    {
        $vat = $this->vatRepository->getVatByPercent($vatPercent);
        if ($vat === null) {
            throw new VatNotFoundException();
        }

        return $vat;
    }
}
