<?php

namespace Mageplaza\GiftCard\Block\Adminhtml\Code\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Mageplaza\GiftCard\Helper\Data as GiftCardHelper;


class Code extends Generic implements TabInterface
{

    protected $giftCardHelper;
    protected $_giftCardFactory;

    public function __construct(Context        $context,
                                Registry       $registry, FormFactory $formFactory, $data = [],
                                GiftCardHelper $giftCardHelper, GiftCardFactory $giftCardFactory)
    {
        $this->giftCardHelper = $giftCardHelper;
        $this->_giftCardFactory = $giftCardFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm(): Code
    {
        $form = $this->_formFactory->create();
        $codeLength = $this->giftCardHelper->getCodeLength();
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Gift card information')]);
        
        $id = $this->_coreRegistry->registry('giftcard_id'); // From Mageplaza\GiftCard\Block\Adminhtml\Code\Edit
        if ($id) {
            $giftCard = $this->_giftCardFactory->create()->load($id);
            $fieldset->addField('giftcard_id', 'hidden', [
                'name' => 'giftcard_id',
                'value' => $giftCard->getId()
            ]);

            $fieldset->addField('code_length', 'text', [
                'name' => 'code_length',
                'label' => __('Code Length'),
                'title' => __('Code Length'),
                'required' => true,
                'class' => 'disabled',
                'placeholder' => __('Enter the number of characters in the code.'),
                'value' => $codeLength,
                'id' => 'code_length'
            ]);

            $fieldset->addField('balance', 'text', [
                'name' => 'balance',
                'label' => __('Balance'),
                'title' => __('Balance'),
                'required' => true,
                'class' => 'validate-number validate-not-negative-number',
                'placeholder' => __('Enter the balance of the gift card.'),
                'value' => $giftCard->getBalance(),
                'id' => 'balance',
            ]);

            $fieldset->addField('code', 'text', [
                'name' => 'code',
                'label' => __('Code'),
                'title' => __('Code'),
                'class' => 'disabled',
                'placeholder' => __('Enter the code of the gift card.'),
                'value' => $giftCard->getCode(),
                'id' => 'code'
            ]);

            $fieldset->addField('created_from', 'text', [
                'name' => 'created_from',
                'label' => __('Created From'),
                'title' => __('Created From'),
                'class' => 'disabled',
                'placeholder' => __('Enter the created from of the gift card.'),
                'value' => $giftCard->getCreatedFrom(),
                'id' => 'created_from'
            ]);
        } else {

            $fieldset->addField('code_lenght', 'text', [
                'name' => 'code_length',
                'label' => __('Code Length'),
                'title' => __('Code Length'),
                'required' => true,
                'class' => 'validate-number validate-not-negative-number',
                'placeholder' => __('Enter the number of characters in the code.'),
                'value' => $codeLength,
                'id' => 'code_length'
            ]);

            $fieldset->addField('balance', 'text', [
                'name' => 'balance',
                'label' => __('Balance'),
                'title' => __('Balance'),
                'required' => true,
                'class' => 'validate-number validate-not-negative-number',
                'placeholder' => __('Enter the balance of the gift card.'),
                'id' => 'balance'
            ]);
        }
        $this->setForm($form);
        return parent::_prepareForm();
    }


    public function getTabLabel()
    {
        return __('Gift card information');
    }

    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    public function canShowTab(): bool
    {
        return true;
    }

    public function isHidden(): bool
    {
        return false;
    }
}
