<?php
/**
 * Copyright © EAdesign by Eco Active S.R.L.,All rights reserved.
 * See LICENSE for license details.
 */
namespace Eadesigndev\FullBreadcrumbs\Block;

use Magento\Catalog\Helper\Data;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Registry;
use Magento\Framework\Api\AttributeValue;
use Eadesigndev\FullBreadcrumbs\Helper\Data as BreadcrumbsData;
use Magento\Store\Model\Store;

class FullBreadcrumbs extends \Magento\Framework\View\Element\Template
{
    /**
     * Catalog data
     *
     * @var Data
     */
    private $catalogData = null;
    private $registry;
    private $categoryCollection;
    private $breadcrumbsData;
    public $bad_categories;
    public $enabled;

    /**
     * @param Context $context
     * @param Data $catalogData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $catalogData,
        Registry $registry,
        BreadcrumbsData $breadcrumbsData,
        CollectionFactory $categoryCollection,
        array $data = []
    ) {
        $this->catalogData = $catalogData;
        $this->registry = $registry;
        $this->breadcrumbsData = $breadcrumbsData;
        $this->categoryCollection = $categoryCollection;
        parent::__construct($context, $data);
    }

    public function getBadCategories()
    {
        $bad_categories = $this->breadcrumbsData->hasConfig('ea_fullbreadcrumbs/fullbreadcrumbs/bad_categories');
        return explode(',', str_replace(' ', '', $bad_categories));
    }

    public function isEnable()
    {
        return $this->breadcrumbsData->hasConfig('ea_fullbreadcrumbs/fullbreadcrumbs/enabled');
    }

    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getCategoryProductIds($product)
    {
        /** @var  $categoryIds  AttributeValue */
        $categoryIds = $product->getCategoryIds();
        return $categoryIds;
    }

    public function getFilteredCollection($categoryIds)
    {
		//new code START
		$path = $this->catalogData->getBreadcrumbPath();
		$product = $this->getProduct();
        $categoryCollection = clone $product->getCategoryCollection();
        $categoryCollection->clear();
        $categoryCollection->addAttributeToSort('level', $categoryCollection::SORT_ORDER_DESC)->addAttributeToFilter('path', array('like' => "1/" . $this->_storeManager->getStore()->getRootCategoryId() . "/%"));
        $categoryCollection->setPageSize(1);
        $breadcrumbCategories = $categoryCollection->getFirstItem()->getParentCategories();
		
		return $breadcrumbCategories;
        //new code END
       /* $collection = $this->categoryCollection->create();
        $filtered_colection = $collection
            ->addFieldToSelect('*')
            ->addFieldToFilter(
                'entity_id',
                ['in' => $categoryIds]
            )
            ->setOrder('level', 'ASC')
            ->load();
        return $filtered_colection;*/
    }

    public function getCategories($filtered_colection, $badCategories)
    {
        //$separator = ' <span class="breadcrumbsseparator"></span> ';
		$separator = '';
        $categories = '';
        foreach ($filtered_colection as $categoriesData) {
            if (!in_array($categoriesData->getId(), $badCategories)) {
                $categories .= '<li class="item"><a href="' . $categoriesData->getUrl() . '">';
                $categories .= $categoriesData->getData('name') . '</a></li>' . $separator;
            }
        }
        return $categories;
    }

    public function getProductBreadcrumbs()
    {
        if ($this->isEnable()) {
            //$separator = ' <span class="breadcrumbsseparator"></span> ';
			$separator = '';
            $product = $this->getProduct();
            $categoryIds = $this->getCategoryProductIds($product);

            $filtered_colection = $this->getFilteredCollection($categoryIds);

            $badCategories = $this->getBadCategories();

            $categories = $this->getCategories($filtered_colection, $badCategories);

            $home_url = '<ul class="items"><li class="item home"><a href="' . $this->_storeManager->getStore()->getBaseUrl() . '">Home</a></li>';
            return $home_url . $separator . $categories . '<li class="item product">' . $product->getName() . '</li></ul>';
        }
    }
}
