<?php

namespace Trive\Staging\Controller\Index;

/**
 * Class Index
 * @package Trive\Staging\Controller\Index
 */
class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\Api\SearchCriteriaInterface
     */
    protected $searchCriteria;

    /**
     * @var \Magento\Staging\Api\UpdateRepositoryInterface
     */
    protected $updateRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface
     */
    protected $productData;

    /**
     * @var \Magento\CatalogStaging\Api\ProductStagingInterface
     */
    protected $productStaging;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Staging\Model\VersionManager
     */
    protected $versionManager;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteriaInterface
     * @param \Magento\Staging\Api\UpdateRepositoryInterface $updateRepositoryInterface
     * @param \Magento\Staging\Api\Data\UpdateInterfaceFactory $updateFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Catalog\Api\Data\ProductInterfaceFactory $productInterface
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
     * @param \Magento\CatalogStaging\Api\ProductStagingInterface $productStagingInterface
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Staging\Model\VersionManagerFactory $versionManagerFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteriaInterface,
        \Magento\Staging\Api\UpdateRepositoryInterface $updateRepositoryInterface,
        \Magento\Staging\Api\Data\UpdateInterfaceFactory $updateFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productInterface,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\CatalogStaging\Api\ProductStagingInterface $productStagingInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Staging\Model\VersionManagerFactory $versionManagerFactory
    ){
        $this->searchCriteria = $searchCriteriaInterface;
        $this->updateRepository = $updateRepositoryInterface;
        $this->updateFactory = $updateFactory;
        $this->localeDate = $localeDate;
        $this->productData = $productInterface;
        $this->productRepository = $productRepositoryInterface;
        $this->productStaging = $productStagingInterface;
        $this->storeManager = $storeManagerInterface;
        $this->versionManager = $versionManagerFactory->create();
        parent::__construct($context);
    }

    public function execute()
    {
        //        $this->storeManager->setCurrentStore('admin');

        /** @var \Magento\Staging\Api\Data\UpdateInterface $schedule */
        $schedule = $this->updateFactory->create();
        $schedule->setName("239487");

        $timestampStart = $this->localeDate->scopeTimeStamp() + 3600;
        $date = new \DateTime('@' . $timestampStart, new \DateTimeZone('UTC'));
        $schedule->setStartTime($date->format('Y-m-d H:i:s'));

        $endTimeVld = true;
        if ($endTimeVld) {
         $timestampEnd = $timestampStart + (60 * 60 * 24);
         $date = new \DateTime('@' . $timestampEnd, new \DateTimeZone('UTC'));
         $schedule->setEndTime($date->format('Y-m-d H:i:s'));
        }

        $stagingRepo = $this->updateRepository->save($schedule);
        $this->versionManager->setCurrentVersionId($stagingRepo->getId());

        $repository = $this->productRepository;
        $product = $repository->get('239487');
        $name = $product->getName();
        $product->setName($name . " - New");
        $price = $product->getPrice();
        $product->setSpecialPrice($price - 10);

        $this->productStaging->schedule($product, $stagingRepo->getId());

        //@var Magento\Staging\Api\Data\UpdateSearchResultInterface  $list */
        $list = $this->updateRepository->getList($this->searchCriteria);
        foreach ($list->getItems() as $item) {
            var_dump($item->getData());
        }

//        $delete = $this->updateRepository->get('1494926713');
//        $this->updateRepository->delete($delete);

    }

}