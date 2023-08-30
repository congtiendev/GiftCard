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

    public function sendEmail(): void
    {
        $templateId = 'affiliate_email_template'; // Template ID
        $fromEmail = 'tienlcph26838@gmail.com';  // Địa chỉ email của người gửi
        $fromName = 'Admin';             // Tên người gửi
        $toEmail = 'congtiendev@gmail.com'; // Địa chỉ email của người nhận

        try {
            $templateVars = [
                'customer_name' => 'Lê Công Tiến',
                'balance' => 1000,
            ];

            $storeId = $this->storeManager->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

            // Bổ sung tiêu đề cho email trong templateOptions
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId,
                'subject' => 'Subject of the Email' // Đặt tiêu đề cho email
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