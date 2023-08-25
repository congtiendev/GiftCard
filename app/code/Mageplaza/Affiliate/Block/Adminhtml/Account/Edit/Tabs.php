<?php

namespace Mageplaza\Affiliate\Block\Adminhtml\Account\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliate_account_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Afiliate Account Information'));
    }
}
