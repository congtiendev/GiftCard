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

        $this->buttonList->update('save', 'label', __('Save Account'));
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
        $this->buttonList->update('delete', 'label', __('Delete'));
    }


    public function getHeaderText()
    {
        $account = $this->_coreRegistry->registry('mageplaza_affiliate_account');
        if ($account->getId()) {
            $accountTitle = $this->escapeHtml($account->getTitle());
            return __("Edit News '%1'", $accountTitle);
        } else {
            return __('Add News');
        }
    }
}