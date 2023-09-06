<?php

namespace Mageplaza\Affiliate\Block\Customer\Affiliate;

use Magento\Cms\Block\Block;
use Magento\Framework\View\Element\Template;
use Mageplaza\Affiliate\Model\AccountFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Mageplaza\Affiliate\Helper\Data as AffiliateHelper;
use Mageplaza\Affiliate\Model\ResourceModel\History\CollectionFactory as HistoryCollectionFactory;

class History extends Template
{
    protected StoreManagerInterface $storeManager;
    protected AccountFactory $accountFactory;
    protected CustomerSession $customerSession;
    protected RedirectInterface $redirect;
    protected AffiliateHelper $affiliateHelper;
    protected HistoryCollectionFactory $historyCollectionFactory;
    protected $_template = 'Mageplaza_Affiliate::customer/affiliate/history.phtml';

    public function __construct(
        HistoryCollectionFactory $historyCollectionFactory,
        StoreManagerInterface    $storeManager,
        AffiliateHelper          $affiliateHelper,
        RedirectInterface        $redirect,
        CustomerSession          $customerSession,
        AccountFactory           $accountFactory,
        Context                  $context,
        array                    $data = []
    )
    {
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->storeManager = $storeManager;
        $this->affiliateHelper = $affiliateHelper;
        $this->redirect = $redirect;
        $this->customerSession = $customerSession;
        $this->accountFactory = $accountFactory;
        parent::__construct($context, $data);
        $this->isEnable();
        $this->redirect();
        $this->getAccount();
        $this->getBalance();
        $this->getStaticBlock();
        $this->getReferLink();
    }


    public function isEnable(): bool
    {
        return $this->affiliateHelper->isAffiliateEnabled();
    }

    public function redirect(): void
    {
        if (!$this->isEnable()) {
            $this->redirect->redirect($this->_request, 'customer/account');
        }
    }

    public function getStaticBlock(): string
    {
        return $this->affiliateHelper->getRegisterStaticBlock();
    }

    /**
     * @throws LocalizedException
     */
    public function renderStaticBlock(): string
    {
        return $this->getLayout()->createBlock(Block::class)->setBlockId($this->getStaticBlock())->setCacheLifetime(null)->toHtml();
    }

    public function getAccount(): \Mageplaza\Affiliate\Model\Account
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        return $this->accountFactory->create()->load($customerId, 'customer_id');
    }

    public function getBalance(): string
    {
        return $this->affiliateHelper->priceFormat($this->getAccount()->getBalance());
    }

    public function getRegisterAction(): string
    {
        return $this->getUrl('affiliate/account/register');
    }


    public function getReferLink(): string
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $route = 'affiliate/refer/index/';
        $urlKey = $this->affiliateHelper->getUrlKey();
        $code = $this->getAccount()->getCode();
        return $baseUrl . $route . $urlKey . '/' . $code;
    }


    public function cancelRefer(): string
    {
        return $this->getUrl('affiliate/refer/cancel');
    }

}