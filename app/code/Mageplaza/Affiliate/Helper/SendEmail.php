<?php

namespace Mageplaza\Affiliate\Helper;

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

    public function sendEmail($emailInfo, $emailType): void
    {
        $fromEmail = 'admin@mageplaza.vn';
        $fromName = 'ADMIN';
        $toEmail = $emailInfo['mail_to'];
        $templateIds = [
            1 => 'affiliate_register',
            2 => 'balance_change_admin',
            3 => 'balance_change_order',
            4 => 'status_change',
        ];
        try {
            $templateId = $templateIds[$emailType];
            $templateVars = [
                'customer_name' => $emailInfo['customer_name'],
                'code' => $emailInfo['code'] ?? null,
                'refer_link' => $emailInfo['refer_link'] ?? null,
                'order_id' => $emailInfo['order_id'] ?? null,
                'commission' => $emailInfo['commission'] ?? null,
                'balance' => $emailInfo['balance'] ?? null,
                'initial_balance' => $emailInfo['initial_balance'] ?? null,
                'new_balance' => $emailInfo['new_balance'] ?? null,
                'current_status' => $emailInfo['current_status'] ?? null,
                'date' => $emailInfo['date'] ?? null
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