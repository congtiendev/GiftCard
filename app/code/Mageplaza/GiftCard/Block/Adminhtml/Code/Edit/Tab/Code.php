<?php

namespace Mageplaza\GiftCard\Block\Adminhtml\Code\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Mageplaza\GiftCard\Model\GiftCardFactory;
use Mageplaza\GiftCard\Helper\Data as GiftCardHelper;


class Code extends Generic implements TabInterface
{

    protected GiftCardHelper $giftCardHelper;
    protected GiftCardFactory $_giftCardFactory;

    public function __construct(Context        $context,
                                Registry       $registry, FormFactory $formFactory, $data = [],
                                GiftCardHelper $giftCardHelper, GiftCardFactory $giftCardFactory)
    {
        $this->giftCardHelper = $giftCardHelper;
        $this->_giftCardFactory = $giftCardFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @throws LocalizedException
     */
    protected function _prepareForm(): Code
    {
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Gift card information')]);
        $giftCard = $this->_coreRegistry->registry('giftcard');
        $codeLength = $this->giftCardHelper->getCodeLength();
        if ($giftCard->getId()) {
            // Edit
            $formValues = [
                'giftcard_id' => $giftCard->getId(),
                'code_length' => $codeLength,
                'balance' => $giftCard->getBalance(),
                'code' => $giftCard->getCode(),
                'created_from' => $giftCard->getCreatedFrom()
            ];
            $fieldset->addField('giftcard_id', 'hidden', [
                'name' => 'giftcard_id',
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
                'id' => 'balance',
            ]);

            $fieldset->addField('code', 'text', [
                'name' => 'code',
                'label' => __('Code'),
                'title' => __('Code'),
                'class' => 'disabled',
                'placeholder' => __('Enter the code of the gift card.'),
                'id' => 'code'
            ]);

            $fieldset->addField('created_from', 'text', [
                'name' => 'created_from',
                'label' => __('Created From'),
                'title' => __('Created From'),
                'class' => 'disabled',
                'placeholder' => __('Enter the created from of the gift card.'),
                'id' => 'created_from'
            ]);
            $form->setValues($formValues);
        } else {
            // Create
            $fieldset->addField('code_length', 'text', [
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
