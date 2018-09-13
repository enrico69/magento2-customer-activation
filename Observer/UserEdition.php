<?php
/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * Date: 01/08/2017
 * Time: 11:29
 */
namespace Enrico69\Magento2CustomerActivation\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Enrico69\Magento2CustomerActivation\Setup\InstallData;
use Psr\Log\LoggerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Enrico69\Magento2CustomerActivation\Model\ActivationEmail;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\MailException;
use Enrico69\Magento2CustomerActivation\Model\Attribute\Active;

class UserEdition implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Enrico69\Magento2CustomerActivation\Model\ActivationEmail
     */
    protected $activationEmail;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connexion;

    /**
     * @var Active
     */
    protected $activeAttribute;

    /**
     * UserEdition constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param CustomerRepositoryInterface $customerRepository
     * @param ManagerInterface $messageManager
     * @param ActivationEmail $activationEmail
     * @param ResourceConnection $resourceConnection
     * @param Active $activeAttribute
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository,
        ManagerInterface $messageManager,
        ActivationEmail $activationEmail,
        ResourceConnection $resourceConnection,
        Active $activeAttribute
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
        $this->messageManager = $messageManager;
        $this->activationEmail = $activationEmail;
        $this->connexion = $resourceConnection->getConnection();
        $this->activeAttribute = $activeAttribute;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        
        $email_configmation = (method_exists($customer->getCustomAttribute(InstallData::CUSTOMER_ACTIVATION_EMAIL_SENT),'getValue')? $customer->getCustomAttribute(InstallData::CUSTOMER_ACTIVATION_EMAIL_SENT)->getValue() :  null) ;
        
        // At customer account update (in adminhtml), if the account is active
        // but the email has not been sent: send it to the customer to notice it
        if ($this->scopeConfig->getValue('customer/create_account/customer_account_activation',
                ScopeInterface::SCOPE_STORE,
                $customer->getStoreId())
            && $email_configmation !== '1'
            && $this->activeAttribute->isCustomerActive($customer)
        ) {
            $this->manageUserActivationEmail($customer);
        }
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     */
    protected function manageUserActivationEmail($customer)
    {
        $this->connexion->beginTransaction();
        $blnStatus = true;

        try {
            $this->updateUser($customer);
            $this->sendEmail($customer);
        } catch (CouldNotSaveException $ex) {
            $this->messageManager->addErrorMessage("Impossible to update user, email has not been sent");
            $blnStatus = false;
        } catch (MailException $e) {
            $this->messageManager->addErrorMessage(
                "Impossible to send the email. Please try to desactivate then reactive the user again"
            );
            $blnStatus = false;
        }

        if ($blnStatus) {
            $this->connexion->commit();
        } else {
            $this->connexion->rollBack();
        }
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function updateUser($customer)
    {
        try {
            $updatedCustomer = $this->customerRepository->getById($customer->getId());
            $updatedCustomer->setCustomAttribute(InstallData::CUSTOMER_ACTIVATION_EMAIL_SENT, true);
            $this->customerRepository->save($updatedCustomer);
        } catch (\Exception $ex) {
            $e = new CouldNotSaveException(__($ex->getMessage()), $ex);
            $this->logger->error(__FILE__ . ' : ' . $ex->getMessage());
            $this->logger->error(__FILE__ . ' : ' . $ex->getTraceAsString());
            throw  $e;
        }
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function sendEmail($customer)
    {
        try {
            $this->activationEmail->send($customer);
        } catch (\Exception $ex) {
            $e = new MailException(__($ex->getMessage()), $ex);
            $this->logger->error(__FILE__ . ' : ' . $ex->getMessage());
            $this->logger->error(__FILE__ . ' : ' . $ex->getTraceAsString());
            throw  $e;
        }
    }
}
