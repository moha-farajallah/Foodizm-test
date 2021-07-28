<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Block\Html;

/**
 * Html pager block
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Pager extends \Magento\Theme\Block\Html\Pager
{
    /**
     * Name of the query string variable used for filtering by number of stars
     */
    const FILTER_VAR_NAME = 'filter';

    /**
     * Name of the query string variable used for ordering collection
     */
    const ORDER_VAR_NAME = 'order';

    /**
     * Name of the query string variable used for filtering by reviewer type
     */
    const REVIEWER_TYPE_VAR_NAME = 'reviewer-type';

    /**
     * Query string values for ordering collection by number of stars
     */
    const ALL_STARS_FILTER = 'all-stars';
    const ONE_STAR_FILTER = 'one-star';
    const TWO_STARS_FILTER = 'two-stars';
    const THREE_STARS_FILTER = 'three-stars';
    const FOUR_STARS_FILTER = 'four-stars';
    const FIVE_STARS_FILTER = 'five-stars';

    /**
     * Query string values for sorting collection
     */
    const MOST_HELPFUL_SORTER = 'most-helpful';
    const MOST_RECENT_SORTER = 'most-recent';

    /**
     * Query string values for filtering collection by reviewer type
     */
    const ALL_REVIEWERS_FILTER = 'all-reviewers';
    const VERIFIED_PURCHASE_FILTER = 'verified_purchase';

    /**
     * Current template name
     *
     * @var string
     */
    protected $_template = 'Intenso_Review::html/pager.phtml';

    /**
     * The list of available pager orders
     *
     * @var array
     */
    protected $availableOrders;

    /**
     * The list of available pager orders
     *
     * @var array
     */
    protected $availableFilters;

    /**
     * The list of available reviewer types
     *
     * @var array
     */
    protected $availableReviewerTypes;

    /**
     * Set pager data
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setData('show_amounts', true);
        $this->setData('use_container', true);
        $this->availableOrders = [
            self::MOST_HELPFUL_SORTER => __('Most helpful first'),
            self::MOST_RECENT_SORTER => __('Most recent first'),
        ];
        $this->availableFilters = [
            self::ALL_STARS_FILTER => __('All stars'),
            self::ONE_STAR_FILTER => __('One star'),
            self::TWO_STARS_FILTER => __('Two stars'),
            self::THREE_STARS_FILTER => __('Three stars'),
            self::FOUR_STARS_FILTER => __('Four stars'),
            self::FIVE_STARS_FILTER => __('Five stars'),
        ];
        $this->availableReviewerTypes = [
            self::ALL_REVIEWERS_FILTER => __('All reviewers'),
            self::VERIFIED_PURCHASE_FILTER => __('Verified purchase only'),
        ];
    }

    /**
     * Set collection for pagination
     *
     * @param  \Magento\Framework\Data\Collection $collection
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection->setCurPage($this->getCurrentPage());
        // set filter by number of stars
        if ($filter = $this->getFilter()) {
            switch ($filter) {
                case self::FIVE_STARS_FILTER:
                    $this->_collection->setFiveStarsFilter();
                    break;
                case self::FOUR_STARS_FILTER:
                    $this->_collection->setFourStarsFilter();
                    break;
                case self::THREE_STARS_FILTER:
                    $this->_collection->setThreeStarsFilter();
                    break;
                case self::TWO_STARS_FILTER:
                    $this->_collection->setTwoStarsFilter();
                    break;
                case self::ONE_STAR_FILTER:
                    $this->_collection->setOneStarFilter();
                    break;
                default:
                    break;
            }
        }
        // set type of reviewer
        if ($this->getReviewerType() == self::VERIFIED_PURCHASE_FILTER) {
            $this->_collection->setVerifiedPurchaseFilter();
        }
        // set order
        if ($this->getOrder() == self::MOST_RECENT_SORTER) {
            $this->_collection->setDateOrder('DESC');
        } else {
            $this->_collection->setHelpfulOrder('DESC');
        }
        // set limit
        if ((int)$this->getLimit()) {
            $this->_collection->setPageSize($this->getLimit());
        }

        $this->_setFrameInitialized(false);

        return $this;
    }

    /**
     * Retrieve pager orders
     *
     * @return array
     */
    public function getAvailableOrders()
    {
        return $this->availableOrders;
    }

    /**
     * Retrieve pager filters
     *
     * @return array
     */
    public function getAvailableFilters()
    {
        return $this->availableFilters;
    }

    /**
     * Retrieve reviewer type
     *
     * @return array
     */
    public function getReviewerTypes()
    {
        return $this->availableReviewerTypes;
    }

    /**
     * @param string $order
     * @return string
     */
    public function getOrderUrl($order)
    {
        return $this->getPagerUrl([$this->getOrderVarName() => $order]);
    }

    /**
     * @param string $filter
     * @return string
     */
    public function getFilterUrl($filter)
    {
        return $this->getPagerUrl([$this->getFilterVarName() => $filter]);
    }

    /**
     * @param string $type
     * @return string
     */
    public function getReviewerTypeUrl($type)
    {
        return $this->getPagerUrl([$this->getReviewerTypeVarName() => $type]);
    }

    /**
     * Retrieve var name for pager order data
     *
     * @return string
     */
    public function getOrderVarName()
    {
        return self::ORDER_VAR_NAME;
    }

    /**
     * Retrieve var name for pager filter data
     *
     * @return string
     */
    public function getFilterVarName()
    {
        return self::FILTER_VAR_NAME;
    }

    /**
     * Retrieve var name for reviewer type data
     *
     * @return string
     */
    public function getReviewerTypeVarName()
    {
        return self::REVIEWER_TYPE_VAR_NAME;
    }

    /**
     * @param string $order
     * @return bool
     */
    public function isOrderCurrent($order)
    {
        return $order == $this->getOrder();
    }

    /**
     * @param string $filter
     * @return bool
     */
    public function isFilterCurrent($filter)
    {
        return $filter == $this->getFilter();
    }

    /**
     * @param string $filter
     * @return bool
     */
    public function isReviewerTypeCurrent($filter)
    {
        return $filter == $this->getReviewerType();
    }

    /**
     * Return current page order
     *
     * @return string
     */
    public function getOrder()
    {
        $orders = $this->getAvailableOrders();
        if ($order = $this->getRequest()->getParam($this->getOrderVarName())) {
            if (isset($orders[$order])) {
                return $order;
            }
        }

        $orders = array_keys($orders);
        return $orders[0];
    }

    /**
     * Return current page filter
     *
     * @return string
     */
    public function getFilter()
    {
        $filters = $this->getAvailableFilters();
        if ($filter = $this->getRequest()->getParam($this->getFilterVarName())) {
            if (isset($filters[$filter])) {
                return $filter;
            }
        }

        $filters = array_keys($filters);
        return $filters[0];
    }

    /**
     * Return current page filter
     *
     * @return string
     */
    public function getReviewerType()
    {
        $filters = $this->getReviewerTypes();
        if ($filter = $this->getRequest()->getParam($this->getReviewerTypeVarName())) {
            if (isset($filters[$filter])) {
                return $filter;
            }
        }

        $filters = array_keys($filters);
        return $filters[0];
    }
}
