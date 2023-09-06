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
        $account = $this->_coreRegistry->registry('affiliate_account');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Affiliate Account information')]);

        if ($account->getId()) {
            // Edit
            $customer = $this->_customerFactory->create()->load($account->getCustomerId());
            $formValues = [
                'account_id' => $account->getId(),
                'customer_id' => $account->getCustomerId(),
                'customer_id_label' => $account->getCustomerId(),
                'customer_name' => $customer->getName() . ' (' . $customer->getEmail() . ')',
                'code' => $account->getCode(),
                'status' => $account->getStatus(),
                'balance' => $account->getBalance(),
                'created_at' => $account->getCreatedAt(),
            ];
            $fieldset->addField('account_id', 'hidden', [
                'name' => 'account_id',
            ]);
            $fieldset->addField('customer_id', 'hidden', [
                'name' => 'customer_id',
            ]);

            $fieldset->addField('customer_id_label', 'label', [
                'name' => 'customer_id_label',
                'label' => __('Customer ID'),
                'title' => __('Customer ID'),
                'class' => 'disabled',
                'id' => 'customer_id_label',
            ]);

            $fieldset->addField('customer_name', 'label', [
                'name' => 'customer_name',
                'label' => __('Customer Name'),
                'title' => __('Customer Name'),
                'class' => 'disabled',
                'id' => 'customer_name',
            ]);

            $fieldset->addField('code', 'label', [
                'name' => 'code',
                'label' => __('Affiliate Code'),
                'title' => __('Affiliate Code'),
                'disabled' => true,
                'id' => 'code',
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
                'class' => 'validate-number validate-not-negative-number',
                'placeholder' => __('Enter the balance of the gift card.'),
                'id' => 'balance',
            ]);

            $fieldset->addField('created_at', 'label', [
                'name' => 'created_at',
                'label' => __('Created At'),
                'title' => __('Created At'),
                'class' => 'disabled',
                'id' => 'created_at'
            ]);
            $form->setValues($formValues);
        } else {
            // Create
            $fieldset->addField('customer_id', 'text', [
                'name' => 'customer_id',
                'label' => __('Customer ID'),
                'title' => __('Customer ID'),
                'required' => true,
                'class' => 'validate-number validate-not-negative-number',
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
                'class' => 'validate-number validate-not-negative-number',
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