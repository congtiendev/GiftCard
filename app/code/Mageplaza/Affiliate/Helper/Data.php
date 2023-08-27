<?php

namespace Mageplaza\Affiliate\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Mageplaza\Affiliate\Model\AccountFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface as DateTimeFormatter;


class Data extends AbstractHelper
{
    protected $scopeConfig;
    protected PriceHelper $priceHelper;
    protected DateTimeFormatter $dateTimeFormatter;
    protected TimezoneInterface $timezone;

    protected AccountFactory $accountFactory;

    public function __construct(
        Context              $context,
        ScopeConfigInterface $scopeConfig,
        AccountFactory       $accountFactory,
        PriceHelper          $priceHelper,
        DateTimeFormatter    $dateTimeFormatter,
        TimezoneInterface    $timezone)
    {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->priceHelper = $priceHelper;
        $this->accountFactory = $accountFactory;
        $this->timezone = $timezone;
        $this->dateTimeFormatter = $dateTimeFormatter;
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

    public function getCodeLength(): int
    {
        return (int)$this->scopeConfig->getValue(
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
        return (int)$this->scopeConfig->getValue(
            'affiliate_configuration/general/apply_discount',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getDiscountValue(): string
    {
        return (int)$this->scopeConfig->getValue(
            'affiliate_configuration/general/discount_value',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCommissionType(): string
    {
        return $this->scopeConfig->getValue(
            'affiliate_configuration/general/commission_type',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getReferLink($code): string
    {
        $baseUrl = 'http://magento2.loc/affiliate/refer/index/';
        $urlKey = $this->getUrlKey();
        return $baseUrl . $urlKey . '/' . $code;
    }

    public function getCommissionValue(): string
    {
        return (int)$this->scopeConfig->getValue(
            'affiliate_configuration/general/commission_value',
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
}