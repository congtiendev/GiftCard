<?php

namespace Mageplaza\Affiliate\Block\Adminhtml\Account\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Affiliate\Model\AccountFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class Account extends Generic implements TabInterface
{
    protected AccountFactory $_accountFactory;
    protected CustomerFactory $_customerFactory;

    public function __construct(
        Context         $context,
        Registry        $registry,
        FormFactory     $formFactory,
        AccountFactory  $accountFactory,
        CustomerFactory $customerFactory,
        array           $data = []
    )
    {
        $this->_accountFactory = $accountFactory;
        $this->_customerFactory = $customerFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @throws LocalizedException
     */
    protected function _prepareForm(): Account
    {
        $form = $this->_formFactory->create();
        $id = $this->_coreRegistry->registry('account_id');
        $account = $this->_accountFactory->create()->load($id);
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Affiliate Account information')]);

        if ($account->getId()) {
            $customer = $this->_customerFactory->create()->load($account->getCustomerId());
            $fieldset->addField('account_id', 'hidden', [
                'name' => 'account_id',
                'value' => $account->getId()
            ]);

            $fieldset->addField('code', 'hidden', [
                'name' => 'code',
                'value' => $account->getCode()
            ]);

            $fieldset->addField('customer_id', 'label', [
                'name' => 'customer_id',
                'label' => __('Customer ID'),
                'title' => __('Customer ID'),
                'class' => 'disabled',
                'id' => 'customer_id',
                'value' => $account->getCustomerId()
            ]);

            $fieldset->addField('customer_name', 'label', [
                'name' => 'customer_name',
                'label' => __('Customer Name'),
                'title' => __('Customer Name'),
                'class' => 'disabled',
                'id' => 'customer_name',
                'value' => $customer->getFirstname() . ' ' . $customer->getLastname() . ' (' . $customer->getEmail() . ')'
            ]);

            $fieldset->addField('affiliate_code', 'label', [
                'name' => 'affiliate_code',
                'label' => __('Affiliate Code'),
                'title' => __('Affiliate Code'),
                'disabled' => true,
                'id' => 'affiliate_code',
                'value' => $account->getCode()
            ]);

            $fieldset->addField('status', 'select', [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'placeholder' => __('Enter the number of characters in the code.'),
                'id' => 'status',
                'values' => [
                    ['value' => 1, 'label' => __('Active')],
                    ['value' => 0, 'label' => __('Inactive')]
                ]
            ]);

            $fieldset->addField('balance', 'text', [
                'name' => 'balance',
                'label' => __('Balance'),
                'title' => __('Balance'),
                'required' => true,
                'class' => 'validate-number validate-not-negative-number validate-greater-than-zero',
                'placeholder' => __('Enter the balance of the gift card.'),
                'value' => $account->getBalance(),
                'id' => 'balance',
            ]);

            $fieldset->addField('created_at', 'label', [
                'name' => 'created_at',
                'label' => __('Created At'),
                'title' => __('Created At'),
                'class' => 'disabled',
                'value' => $account->getCreatedAt(),
                'id' => 'created_at'
            ]);


        } else {
            $fieldset->addField('customer_id', 'text', [
                'name' => 'customer_id',
                'label' => __('Customer ID'),
                'title' => __('Customer ID'),
                'required' => true,
                'class' => 'validate-number validate-not-negative-number validate-greater-than-zero',
                'placeholder' => __('Enter the number of characters in the code.'),
                'id' => 'customer_id'
            ]);

            $fieldset->addField('status', 'select', [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'placeholder' => __('Enter the number of characters in the code.'),
                'id' => 'status',
                'values' => [
                    ['value' => 1, 'label' => __('Active')],
                    ['value' => 0, 'label' => __('Inactive')]
                ]
            ]);

            $fieldset->addField('initial_balance', 'text', [
                'name' => 'balance',
                'label' => __('Initial Balance'),
                'title' => __('Initial Balance'),
                'required' => true,
                'class' => 'validate-number validate-not-negative-number validate-greater-than-zero',
                'placeholder' => __('Enter the initial balance.'),
                'id' => 'initial_balance'
            ]);
        }
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return __('Affiliate Account information');
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