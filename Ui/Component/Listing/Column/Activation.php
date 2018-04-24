<?php
/**
 * Datasource used in the customer grid for the "is activated' field
 *
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * Date: 05/12/2017
 */
namespace Enrico69\Magento2CustomerActivation\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Activation extends Column
{
    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item['account_is_active'] = $item['account_is_active'] === '1' ? __('Yes') : __('No');
            }
        }

        return $dataSource;
    }

}
