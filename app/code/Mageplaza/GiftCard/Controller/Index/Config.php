<?php

namespace Mageplaza\GiftCard\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\GiftCard\Helper\Data as GiftCardHelper;

class Config extends Action
{
    protected $resultPageFactory;
    protected $helper;

    public function __construct(Context $context, PageFactory $resultPageFactory, GiftCardHelper $helper)
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
//        if ($this->helper->isGiftCardEnabled()) {
//
//            echo "Chức năng Gift Card đã được bật";
//
//            if (!$this->helper->allowUsedGiftCardAtCheckout()) {
//                // Không cho phép sử dụng Gift Card ở trang checkout
//                echo "<br>Đã tắt sử dụng Gift Card ở trang checkout";
//            } else {
//                echo "<br>Đã bật sử dụng Gift Card ở trang checkout";
//            }
//
//            if ($this->helper->allowRedeemGiftCard()) {
//                // Cho phép khách hàng chuyển số tiền từ Gift Card vào số dư tài khoản của họ
//                echo "<br>Đã bật chức năng chuyển số tiền từ Gift Card vào số dư tài khoản của khách hàng";
//            } else {
//                echo "<br>Đã tắt chức năng chuyển số tiền từ Gift Card vào số dư tài khoản của khách hàng";
//            }
//        } else {
//            echo "Chức năng Gift Card đã được tắt";
//            die();
//        }
        return $this->resultPageFactory->create();
    }
}
