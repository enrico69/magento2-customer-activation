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
                if (!array_key_exists('account_is_active', $item)) {
                    // Before the installation of the module
                    $item['account_is_active'] = 'Need to reindex';
                    continue;
                }

                // After the installation of the module
                if ($item['account_is_active'] === '1') {
                    $item['account_is_active'] = __('Yes');
                } elseif ($item['account_is_active'] === null) {
                    $item['account_is_active'] = __('Yes (by default)');
                } else {
                    $item['account_is_active'] = __('No');
                }
            }
        }

        return $dataSource;
    }

}
