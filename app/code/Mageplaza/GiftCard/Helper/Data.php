<?php

namespace Mageplaza\GiftCard\Helper;

use DateTime;
use Zend_Log_Exception;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;


class Data extends AbstractHelper
{
    protected $scopeConfig;
    protected $giftCardFactory;

    public function __construct(Context $context, ScopeConfigInterface $scopeConfig, GiftCardFactory $giftCardFactory)
    {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->giftCardFactory = $giftCardFactory;
    }

    public function isGiftCardEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'giftcard_config/general/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function allowUsedGiftCardAtCheckout(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'giftcard_config/general/allow_used_giftcard_at_checkout',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function allowRedeemGiftCard(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'giftcard_config/general/allow_redeem_giftcard',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCodeLength(): int
    {
        return (int)$this->scopeConfig->getValue(
            'giftcard_config/code_general/code_length',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function generateGiftCode($codeLength): string
    {
        $code = substr(str_shuffle(str_repeat($x = 'ABCDEFGHIJKLMLOPQRSTUVXYZ0123456789', ceil($codeLength / strlen($x))
        )), 1, $codeLength);
        $giftCard = $this->giftCardFactory->create();
        $giftCard->load($code, 'code');
        if ($giftCard->getId()) {
            $this->generateGiftCode($codeLength);
        }
        return $code;
    }


    public function getMyGiftCardUrl(): string
    {
        return $this->_getUrl('giftcard/index/mygiftcard');
    }

    public function getRefererUrl(): string
    {
        return $this->_getRequest()->getServer('HTTP_REFERER');
    }

    /**
     * @throws Zend_Log_Exception|\JsonException
     */
    public function debug($data): void
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(json_encode($data, JSON_THROW_ON_ERROR));
    }

    public function getGiftCardCode($code): \Mageplaza\GiftCard\Model\GiftCard
    {
        return $this->giftCardFactory->create()->load($code, 'code');
    }

    /**
     * @throws \Exception
     */
    public function formatDateTime($date): string
    {
        $date = new DateTime($date);
        return $date->format('d-m-Y');
    }


}
