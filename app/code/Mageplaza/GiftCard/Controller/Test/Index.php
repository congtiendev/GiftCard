<?php

namespace Mageplaza\GiftCard\Controller\Test;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\GiftCard\Model\GiftCardFactory;

class Index extends Action
{
    protected $_giftCardFactory;
    protected $_pageFactory;
    public const URL = "http://magento2.loc/giftcard/test/";

    public function __construct(Context $context, GiftCardFactory $giftCardFactory, PageFactory $pageFactory)
    {
        $this->_giftCardFactory = $giftCardFactory;
        $this->_pageFactory = $pageFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        $giftCard = $this->_giftCardFactory->create();
        $allGiftCard = $giftCard->getCollection();
        echo "<h1 style='text-align: center'>Gift Card</h1>";
        echo "<table border='1' style='margin: 0 auto;'>
                    <thead style='padding:10px;'>
                        <th colspan='7'><a style='text-align: center;color:black;' href='" .
            self::URL . "create'>Add new</a></th>
                    </thead>
                        <thead style='padding:10px;'>
                            <th>Id</th>
                            <th>Code</th>
                            <th>Balance</th>
                            <th>Amout used</th>
                            <th>Created from</th>
                            <th>Created at</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                        ";

        foreach ($allGiftCard as $giftcard) {
            echo "<tr style='padding:10px;'> 
                    <td>" . $giftcard->getData('giftcard_id') . "</td> 
                    <td>" . $giftcard->getData('code') . "</td>
                    <td>" . $giftcard->getData('balance') . "</td>
                    <td>" . $giftcard->getData('amount_used') . "</td>
                    <td>" . $giftcard->getData('created_from') . "</td>
                    <td>" . $giftcard->getData('created_at') . "</td> 
                    <td>
                        <a href='" . self::URL . "update?giftcard_id=" . $giftcard->getData('giftcard_id') . "'>Update</a>
                        <a href='" . self::URL . "delete?giftcard_id=" . $giftcard->getData('giftcard_id') . "'>Delete</a>
                        <a href='" . self::URL . "detail?giftcard_id=" . $giftcard->getData('giftcard_id') . "'>Detail</a
                    </td>
                    </tr>";
        }
        echo "</tbody></table>";
        
        return $this->_pageFactory->create(); // Tao ra 1 trang web moi
    }
}