<?php
/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * Date: 01/08/2017
 */
namespace Enrico69\Magento2CustomerActivation\Model;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Area;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ActivationEmail
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigInterface;

    /**
     * ActivationEmail constructor.
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManagerInterface,
        ScopeConfigInterface $scopeConfigInterface
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->scopeConfigInterface = $scopeConfigInterface;
    }

    /**
     * If an account is created, send the credentials to the user
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @throws \Magento\Framework\Exception\MailException
     */
    public function send($customer)
    {
        $this->transportBuilder->setTemplateIdentifier('enrico69_activation_email')
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND, // this is using frontend area to get the template file
                    'store' => Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars(['email' => $customer->getEmail()]);

        $this->transportBuilder->addTo($customer->getEmail());
        $this->transportBuilder->setFrom(
            [
                'name'=> $this->storeManagerInterface->getStore($customer->getStoreId())->getName(),
                'email' => $this->scopeConfigInterface->getValue(
                    'trans_email/ident_sales/email',
                    ScopeInterface::SCOPE_WEBSITE
                )
            ]
        );

        $this->transportBuilder->getTransport()->sendMessage();
    }
}
