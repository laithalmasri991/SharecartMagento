<?php
namespace Laith\ShareCart\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLE = 'share_cart/general/enable';

    public function isShareCartEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
