<?php

namespace Mageplaza\GiftCard\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;

class SendEmail extends AbstractHelper
{
    protected TransportBuilder $transportBuilder;
    protected StoreManagerInterface $storeManager;
    protected StateInterface $inlineTranslation;

    public function __construct(
        Context               $context,
        TransportBuilder      $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface        $state
    )
    {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        parent::__construct($context);
    }

    public function sendEmail($emailInfo, $emailType)
    {
        $fromEmail = 'admin@mageplaza.vn';
        $fromName = 'ADMIN';
        $toEmail = $emailInfo['mail_to'];
        $templateIds = [
            1 => 'buy_gift_card',
            2 => 'use_gift_card_for_order',
            3 => 'redeem_gift_card',
        ];

        try {
            $templateId = $templateIds[$emailType];
            $templateVars = [
                'customer_name' => $emailInfo['customer_name'],
                'increment_id' => $emailInfo['increment_id'],
                'gift_card_code' => $emailInfo['gift_card_code'],
                'balance' => $emailInfo['balance'] ?? null,
                'amount_used' => $emailInfo['amount_used'] ?? null,
                'amount' => $emailInfo['amount'] ?? null,
                'redeem_amount' => $emailInfo['redeem_amount'] ?? null,
            ];

            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId,
            ];

            $transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($toEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}