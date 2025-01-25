<?php

namespace Laith\Magentosharecart\Block;

use Magento\Framework\View\Element\Template;
use Laith\Magentosharecart\Helper\Data;

class ShareCart extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Check if Share Cart is enabled
     *
     * @return bool
     */
    public function isShareCartEnabled()
    {
        return $this->helper->isShareCartEnabled();
    }
}
