<?php

namespace Mageplaza\Affiliate\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Mageplaza\Affiliate\Model\AccountFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface as DateTimeFormatter;


class Data extends AbstractHelper
{
    public const FIXED_AMOUNT = 'fixed';
    protected $resultRedirect;

    protected $scopeConfig;
    protected PriceHelper $priceHelper;
    protected MessageManager $messageManager;
    protected CustomerSession $customerSession;
    protected DateTimeFormatter $dateTimeFormatter;
    protected TimezoneInterface $timezone;

    protected AccountFactory $accountFactory;

    public function __construct(
        Context              $context,
        ScopeConfigInterface $scopeConfig,
        AccountFactory       $accountFactory,
        PriceHelper          $priceHelper,
        CustomerSession      $customerSession,
        DateTimeFormatter    $dateTimeFormatter,
        MessageManager       $messageManager,
        TimezoneInterface    $timezone,
        ResultFactory        $resultFactory)
    {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->priceHelper = $priceHelper;
        $this->accountFactory = $accountFactory;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->timezone = $timezone;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->resultRedirect = $resultFactory->create(ResultFactory::TYPE_REDIRECT);
    }

    public function isAffiliateEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'affiliate_configuration/general/enable',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getRegisterStaticBlock(): string
    {
        return $this->scopeConfig->getValue(
            'affiliate_configuration/general/select_static_block',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCodeLength(): float
    {
        return (float)$this->scopeConfig->getValue(
            'affiliate_configuration/general/code_length',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getUrlKey(): string
    {
        return $this->scopeConfig->getValue(
            'affiliate_configuration/general/url_key',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getApplyDiscount(): string
    {
        return $this->scopeConfig->getValue(
            'affiliate_configuration/affiliate_rule/apply_discount',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getDiscountValue()
    {
        return $this->scopeConfig->getValue(
            'affiliate_configuration/affiliate_rule/discount_value',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCommissionType(): string
    {
        return $this->scopeConfig->getValue(
            'affiliate_configuration/affiliate_rule/commission_type',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getReferLink($code): string
    {
        $baseUrl = 'http://magento2.loc/affiliate/refer/index/';
        $urlKey = $this->getUrlKey();
        return $baseUrl . $urlKey . '/' . $code;
    }

    public function getCommissionValue(): float
    {
        return (float)$this->scopeConfig->getValue(
            'affiliate_configuration/affiliate_rule/commission_value',
            ScopeInterface::SCOPE_STORE
        );
    }


    public function generateCode(): string
    {
        $codeLength = $this->getCodeLength();
        $code = substr(str_shuffle(str_repeat($x = 'abcdefghijklmnopqrstuvwxyz0123456789', ceil($codeLength / strlen($x)))), 1, $codeLength);
        $account = $this->accountFactory->create()->load($code, 'code');
        if ($account->getId()) {
            $this->generateCode($codeLength);
        }
        return $code;
    }

    public function priceFormat($price): string
    {
        return $this->priceHelper->currency($price, true, false);
    }


    public function formatDateTime($date): string
    {
        return $this->dateTimeFormatter->formatObject(
            $this->timezone->date($date),
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            null,
            null
        );
    }

    public function isLogin(): bool
    {
        return $this->customerSession->isLoggedIn();
    }


    public function cancelReferLink(): void
    {
        setcookie($this->getUrlKey(), '', time() - 3600, '/');
    }

}