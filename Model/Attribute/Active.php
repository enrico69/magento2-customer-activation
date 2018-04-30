<?php
/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * Date: 23/04/2018
 */
namespace Enrico69\Magento2CustomerActivation\Model\Attribute;

use Enrico69\Magento2CustomerActivation\Setup\InstallData;;

class Active
{
    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return bool
     */
    public function isCustomerActive($customer)
    {
        $attribute = $customer->getCustomAttribute(InstallData::CUSTOMER_ACCOUNT_ACTIVE);
        if ($attribute !== null) { // After the installation of the module
            $status = $attribute->getValue() === '1' ? true:false;
        } else { // Before the installation of the module
            $status = true;
        }

        return $status;
    }
}

