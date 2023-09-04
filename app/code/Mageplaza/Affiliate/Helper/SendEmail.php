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

    public function sendEmail($emailInfo): void
    {
        $templateId = 'affiliate_email_template'; // Template ID
        $fromEmail = 'admin@mageplaza.vn';  // Địa chỉ email của người gửi
        $fromName = 'ADMIN';             // Tên người gửi
        $toEmail = $emailInfo['mail_to']; // Địa chỉ email của người nhận

        try {
            $templateVars = [
                'subject' => $emailInfo['subject'],
                'customer_name' => $emailInfo['customer_name'],
                'order_id' => $emailInfo['order_id'],
                'commission' => $emailInfo['commission'],
                'balance' => $emailInfo['balance'],
                'date' => $emailInfo['date'],
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