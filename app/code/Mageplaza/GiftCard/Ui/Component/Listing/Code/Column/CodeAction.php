<?php

namespace Mageplaza\GiftCard\Ui\Component\Listing\Code\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CodeAction extends Column
{
    /** Url path */
    public const ROW_EDIT_URL = 'mageplaza_giftcard/code/new';
    /** @var UrlInterface */
    protected $_urlBuilder;

    /**
     * @var string
     */
    private string $_editUrl;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface       $urlBuilder,
        array              $components = [],
        array              $data = [],
                           $editUrl = self::ROW_EDIT_URL
    )
    {
        $this->_urlBuilder = $urlBuilder;
        $this->_editUrl = $editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) { //  Kiểm tra xem có dữ liệu không
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name'); // Lấy tên cột hiện tại
                if (isset($item['giftcard_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->_urlBuilder->getUrl(
                            $this->_editUrl,
                            ['id' => $item['giftcard_id']]
                        ),
                        'label' => __('Edit'),
                    ];
                }
            }
        }

        return $dataSource;
    }
}