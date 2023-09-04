<?php

namespace Mageplaza\GiftCard\Controller\Adminhtml\CRUD;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Magento\Framework\UrlInterface;

class Index extends Action
{
    protected GiftCardFactory $_giftCardFactory;
    protected PageFactory $_pageFactory;
    protected UrlInterface $urlBuilder;

    public function __construct(
        Context         $context,
        GiftCardFactory $giftCardFactory,
        PageFactory     $pageFactory,
        UrlInterface    $urlBuilder)
    {
        $this->_giftCardFactory = $giftCardFactory;
        $this->_pageFactory = $pageFactory;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context);
    }

    public function getAdminUrl(): void
    {
        $url = $this->urlBuilder->getUrl('adminhtml/crud/');
    }

    public function execute()
    {
        $giftCard = $this->_giftCardFactory->create();
        $allGiftCard = $giftCard->getCollection();
        $action = $this->getRequest()->getParam('action');
        $id = $this->getRequest()->getParam('id');
        if ($action) {
            if ($action === 'delete') {
                $this->delete($id);
            } elseif ($action === 'edit') {
                $this->edit($id);
            } else {
                $this->add($giftCard);
            }
            $this->_redirect('*/*/index');
        }

        echo "<h1 style='text-align: center'>Gift Card</h1>";
        echo "<table border='1' style='margin: 0 auto;'>
                    <thead style='padding:10px;'>
                        <th colspan='7'><a style='text-align: center;color:black;' href='" . $this->getAdminUrl() . "?action=add'>Add new</a></th>
                    </thead>
                        <thead style='padding:10px;'>
                            <th>Id</th>
                            <th>Code</th>
                            <th>Balance</th>
                            <th>Amount used</th>
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
                        <a href='" . $this->getAdminUrl() . "?action=edit&id=" . $giftcard->getId() . "'>Edit</a>
                        <a href='" . $this->getAdminUrl() . "?action=delete&id=" . $giftcard->getId() . "'>Delete</a>
                    </td>
                    </tr>";
        }
        echo "</tbody></table>";
        return $this->_pageFactory->create();
    }

    public function add($giftCard)
    {
        $data = [
            'code' => substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10),
            'balance' => random_int(100, 1000),
            'amount_used' => 0,
            'created_from' => random_int(0, 1) ? 'admin' : random_int(100000000, 999999999),
        ];
        try {
            $giftCard->addData($data)->save();
            $this->messageManager->addSuccess(__('Gift Card has been saved.'));
            return $this->_redirect('*/*/index');
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Gift Card could not be saved.'));
            echo $e->getMessage();
        }
    }

    public function delete($id)
    {
        $giftCard = $this->_giftCardFactory->create()->load($id);
        if ($giftCard->getId()) {
            try {
                $giftCard->delete();
                $this->messageManager->addSuccess(__('Gift Card has been deleted.'));
                return $this->_redirect('*/*/index');
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Gift Card could not be deleted.'));
                return $this->_redirect('*/*/index');
            }
        }
    }

    public function edit($id)
    {
        $giftCard = $this->_giftCardFactory->create()->load($id);
        $data = [
            'code' => substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10),
            'balance' => random_int(100, 1000),
            'amount_used' => 0,
            'created_from' => random_int(0, 1) ? 'admin' : random_int(100000000, 999999999),
        ];
        if ($giftCard->getId()) {
            try {
                $giftCard->addData($data)->save();
                return $this->_redirect('*/*/index');
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Gift Card could not be updated.'));
            }
        }
    }
}