<?php

namespace Mageplaza\Affiliate\Controller\Test;

use Magento\Framework\App\Action\Action;
use Mageplaza\Affiliate\Helper\Data as HelperData;

class Index extends Action
{
    protected HelperData $helperData;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        HelperData                            $helperData
    )
    {
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    public function execute()
    {
        dd($Code = $_COOKIE[$this->helperData->getUrlKey()] ?? null);
    }
}