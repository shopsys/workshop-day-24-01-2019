<?php

namespace Shopsys\ShopBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Shopsys\ShopBundle\Model\Pricing\Vat\VatFacade;
use Symfony\Bridge\Monolog\Logger;

class ImportProductsCronModule implements SimpleCronModuleInterface
{
    private const DATA_URL = 'https://bit.ly/2DoOIAc';
    private const LOCALE = 'en';
    private const DOMAIN_ID = 1;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    protected $productFacade;

    /**
     * @var \Shopsys\ShopBundle\Model\Product\ProductDataFactory
     */
    private $productDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
     */
    private $pricingGroupSettingFacade;

    /**
     * @var \Shopsys\ShopBundle\Model\Pricing\Vat\VatFacade
     */
    private $vatFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade
     */
    private $brandFacade;

    /**
     * @param \Shopsys\ShopBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\ShopBundle\Model\Product\ProductDataFactory $productDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\ShopBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     */
    public function __construct(
        ProductFacade $productFacade,
        ProductDataFactory $productDataFactory,
        PricingGroupSettingFacade $pricingGroupSettingFacade,
        VatFacade $vatFacade,
        BrandFacade $brandFacade
    ) {
        $this->productFacade = $productFacade;
        $this->productDataFactory = $productDataFactory;
        $this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
        $this->vatFacade = $vatFacade;
        $this->brandFacade = $brandFacade;
    }

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * This method is called to run the CRON module.
     */
    public function run()
    {
        $this->logger->info('Downloading data...');
        $dataJson = file_get_contents(self::DATA_URL);
        $this->logger->info('Decoding data...');
        $externalProductsData = json_decode($dataJson, true);
        $this->processData($externalProductsData);
    }

    /**
     * @param array $externalProductsData
     */
    private function processData(array $externalProductsData)
    {
        foreach ($externalProductsData as $externalProductData) {
            $extId = $externalProductData['id'];
            $product = $this->productFacade->findByExternalId($extId);
            try {
                if ($product === null) {
                    $productData = $this->productDataFactory->create();
                    $this->mapExternalDataToProductData($externalProductData, $productData);
                    $this->productFacade->create($productData);
                    $this->logger->info(sprintf('Product with ext ID "%s" created', $extId));
                } else {
                    $productData = $this->productDataFactory->createFromProduct($product);
                    $this->mapExternalDataToProductData($externalProductData, $productData);
                    $this->productFacade->edit($product->getId(), $productData);
                    $this->logger->info(sprintf('Product with ext ID "%s" edited', $extId));
                }
            } catch (VatNotFoundException $ex) {
                $this->logger->warning(sprintf('Skipping product with ext ID "%s" (%s)', $extId, $ex->getMessage()));
            }
        }
    }

    /**
     * @param array $externalData
     * @param \Shopsys\ShopBundle\Model\Product\ProductData $productData
     */
    private function mapExternalDataToProductData(array $externalData, ProductData $productData)
    {
        $productData->extId = $externalData['id'];
        $productData->name[self::LOCALE] = $externalData['name'];
        $defaultPricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId(self::DOMAIN_ID);
        $productData->manualInputPricesByPricingGroupId[$defaultPricingGroup->getId()] = $externalData['price_without_vat'];
        $productData->vat = $this->vatFacade->getVatByPercent($externalData['vat_percent']);
        $productData->ean = $externalData['ean'];
        $productData->brand = $this->brandFacade->getById($externalData['brand_id']);
        $productData->descriptions[self::DOMAIN_ID] = $externalData['description'];
        $productData->stockQuantity = $externalData['stock_quantity'];
        $productData->usingStock = true;
    }
}
