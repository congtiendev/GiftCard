<?php

namespace Mageplaza\Affiliate\Block\Adminhtml\Account;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Edit extends Container
{
    protected ?Registry $_coreRegistry = null;

    public function __construct(
        Context  $context,
        Registry $registry,
        array    $data = []
    )
    {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'account_id';
        $this->_controller = 'adminhtml_account'; // <=> Mageplaza\Affiliate\Block\Adminhtml\Account\Grid
        $this->_blockGroup = 'Mageplaza_Affiliate';
        parent::_construct();

        parent::_construct();
        $account = $this->_coreRegistry->registry('affiliate_account');
        if ($account->getId()) {
            $deleteUrl = $this->getUrl('affiliate/account/delete', ['id' => $account->getId()]);
            $deleteConfirmMsg = __("Are you sure you want to remove this account?");
            $this->addButton(
                'delete',
                [
                    'label' => __('Delete'),
                    'class' => 'delete',
                    'onclick' => "deleteConfirm('{$deleteConfirmMsg}', '{$deleteUrl}')",
                ]
            );
        }
        $this->buttonList->add(
            'save_and_continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            10
        );
    }


    public function getHeaderText()
    {
        $account = $this->_coreRegistry->registry('affiliate_account');
        if ($account->getId()) {
            return __("Edit News '%1'", $this->escapeHtml($account->getId()));
        }
        return __('Add News');
    }
}