<?php

namespace Mageplaza\GiftCard\Block\Adminhtml\Code;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Edit extends Container
{

    protected $_coreRegistry = null;


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
        $this->_objectId = 'giftcard_id';
        $this->_controller = 'adminhtml_code'; // <=> Mageplaza\GiftCard\Block\Adminhtml\Code\Grid
        $this->_blockGroup = 'Mageplaza_GiftCard';


        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Gift Card'));
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
        $giftcard = $this->_coreRegistry->registry('mageplaza_giftcard');
        if ($giftcard->getId()) {
            $giftCardTitle = $this->escapeHtml($giftcard->getTitle());
            return __("Edit News '%1'", $giftCardTitle);
        } else {
            return __('Add News');
        }
    }
}