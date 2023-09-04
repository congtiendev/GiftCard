<?php

namespace Mageplaza\Affiliate\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Mageplaza\Affiliate\Model\AccountFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\CookieManagerInterface as CookieManager;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface as DateTimeFormatter;


class Data extends AbstractHelper
{
    protected $resultRedirect;

    protected $scopeConfig;

    protected PriceHelper $priceHelper;
    protected TimezoneInterface $timezone;
    protected CookieManager $cookieManager;
    protected MessageManager $messageManager;
    protected AccountFactory $accountFactory;
    protected CustomerSession $customerSession;
    protected StoreManagerInterface $storeManager;
    protected PriceCurrencyInterface $priceCurrency;
    protected DateTimeFormatter $dateTimeFormatter;
    protected CookieMetadataFactory $cookieMetadataFactory;
    public const CONFIG_PATH_ENABLE_AFFILIATE = 'affiliate_configuration/general/enable';
    public const CONFIG_PATH_SELECT_STATIC_BLOCK = 'affiliate_configuration/general/select_static_block';
    public const CONFIG_PATH_CODE_LENGTH = 'affiliate_configuration/general/code_length';
    public const CONFIG_PATH_URL_KEY = 'affiliate_configuration/general/url_key';
    public const CONFIG_PATH_APPLY_DISCOUNT = 'affiliate_configuration/affiliate_rule/apply_discount';
    public const CONFIG_PATH_DISCOUNT_VALUE = 'affiliate_configuration/affiliate_rule/discount_value';
    public const CONFIG_PATH_COMMISSION_TYPE = 'affiliate_configuration/affiliate_rule/commission_type';
    public const CONFIG_PATH_COMMISSION_VALUE = 'affiliate_configuration/affiliate_rule/commission_value';


    public function __construct(
        Context                $context,
        ScopeConfigInterface   $scopeConfig,
        AccountFactory         $accountFactory,
        CookieMetadataFactory  $cookieMetadataFactory,
        PriceHelper            $priceHelper,
        StoreManagerInterface  $storeManager,
        PriceCurrencyInterface $priceCurrency,
        CustomerSession        $customerSession,
        DateTimeFormatter      $dateTimeFormatter,
        MessageManager         $messageManager,
        CookieManager          $cookieManager,
        TimezoneInterface      $timezone,
        ResultFactory          $resultFactory)
    {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->priceHelper = $priceHelper;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->accountFactory = $accountFactory;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->cookieManager = $cookieManager;
        $this->timezone = $timezone;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->resultRedirect = $resultFactory->create(ResultFactory::TYPE_REDIRECT);
    }

    public function isAffiliateEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::CONFIG_PATH_ENABLE_AFFILIATE, ScopeInterface::SCOPE_STORE);
    }

    public function getRegisterStaticBlock(): string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_SELECT_STATIC_BLOCK,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCodeLength(): float
    {
        return (float)$this->scopeConfig->getValue(
            self::CONFIG_PATH_CODE_LENGTH,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getUrlKey(): string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_URL_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getApplyDiscount(): string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_APPLY_DISCOUNT,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getDiscountValue()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_DISCOUNT_VALUE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCommissionType(): string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMMISSION_TYPE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCommissionValue()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_COMMISSION_VALUE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function setAffiliateCode($code): void
    {
        $metaData = $this->cookieMetadataFactory->createPublicCookieMetadata()->setDurationOneYear()->setPath('/')->setHttpOnly(false);
        $this->cookieManager->setPublicCookie($this->getUrlKey(), $code, $metaData);
    }

    public function getAffiliateCode()
    {
        return $this->cookieManager->getCookie($this->getUrlKey());
    }

    public function deleteAffiliateCode(): void
    {
        $cookieMetadata = $this->cookieMetadataFactory->createCookieMetadata()
            ->setPath('/');
        $this->cookieManager->deleteCookie($this->getUrlKey(), $cookieMetadata);
    }

    public function getReferLink($code): string
    {
        $baseUrl = 'http://magento2.loc/affiliate/refer/index/';
        $urlKey = $this->getUrlKey();
        return $baseUrl . $urlKey . '/' . $code;
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

    public function calculateAffiliate($baseSubtotal, $baseValue, $type)
    {
        if ($type === 'fixed') {
            $value = $baseValue;
        } else if ($type === 'percentage') {
            $value = $baseSubtotal * $baseValue / 100;
        } else {
            $value = 0;
        }
        return min($value, $baseSubtotal);
    }

    public function formatCurrencyByCode($amount, $orderCurrencyCode): string
    {
        return $this->priceCurrency->convertAndFormat(
            $amount,
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->storeManager->getStore(),
            $orderCurrencyCode
        );
    }

}