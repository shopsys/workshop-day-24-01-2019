<?php

namespace Shopsys\ShopBundle\Model\Product\LastVisitedProduct;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class LastVisitedProductFacade
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param int $productId
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function updateLastVisitedProductsIds(int $productId, Response $response)
    {
        $lastVisitedProductsIdsString = $this->requestStack->getMasterRequest()->cookies->get(
            'lastVisitedProducts',
            ''
        );

        if ($lastVisitedProductsIdsString !== '') {
            $lastVisitedProductsIds = explode(',', $lastVisitedProductsIdsString);
            $lastVisitedProductsIds = array_map('intval', $lastVisitedProductsIds);
        } else {
            $lastVisitedProductsIds = [];
        }

        $indexOfProductIdIfAlreadyVisited = array_search($productId, $lastVisitedProductsIds, true);
        if ($indexOfProductIdIfAlreadyVisited !== false) {
            unset($lastVisitedProductsIds[$indexOfProductIdIfAlreadyVisited]);
        }

        array_unshift($lastVisitedProductsIds, $productId);

        $cookie = new Cookie(
            'lastVisitedProducts',
            implode(',', $lastVisitedProductsIds)
        );

        $response->headers->setCookie($cookie);
    }
}
