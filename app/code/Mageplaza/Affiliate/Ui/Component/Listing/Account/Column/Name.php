<?php

namespace Mageplaza\Affiliate\Ui\Component\Listing\Account\Column;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Name extends Column
{
    protected $customerRepository;

    public function __construct(
        ContextInterface            $context,
        UiComponentFactory          $uiComponentFactory,
        CustomerRepositoryInterface $customerRepository,
        array                       $components = [],
        array                       $data = []
    )
    {
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $customer = $this->customerRepository->getById($item['customer_id']);
                $item[$this->getData('name')] = $customer->getFirstname() . ' ' . $customer->getLastname();
            }
        }
        return $dataSource;
    }
}