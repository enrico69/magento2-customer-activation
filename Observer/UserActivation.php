<?php
/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * Date: 01/08/2017
 * Time: 11:28
 */
namespace Enrico69\Magento2CustomerActivation\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Enrico69\Magento2CustomerActivation\Setup\InstallData;
use Psr\Log\LoggerInterface;
use Enrico69\Magento2CustomerActivation\Model\AdminNotification;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;

class UserActivation implements ObserverInterface
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
     * @var \Enrico69\Magento2CustomerActivation\Model\AdminNotification
     */
    protected $adminNotification;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * UserActivation constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Enrico69\Magento2CustomerActivation\Model\AdminNotification $adminNotification
     * @param \Magento\Customer\Model\Session $customerSession
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerRepositoryInterface $customerRepository,
        ManagerInterface $messageManager,
        LoggerInterface $logger,
        AdminNotification $adminNotification,
        Session $customerSession,
        AccountManagementInterface $accountManagement
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->customerRepository = $customerRepository;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->adminNotification = $adminNotification;
        $this->customerSession = $customerSession;
        $this->accountManagement = $accountManagement;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function execute(EventObserver $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if ($this->scopeConfig->getValue('customer/create_account/customer_account_activation', ScopeInterface::SCOPE_STORE)) {
            $newCustomer = $this->customerRepository->get($customer->getEmail());
            $newCustomer->setCustomAttribute(InstallData::CUSTOMER_ACCOUNT_ACTIVE, 0);
            $this->customerRepository->save($newCustomer);
            $this->messageManager->addNoticeMessage(__('Your account will be enabled by the site owner soon'));
            $this->customerSession->setRegisterSuccess(true);

            $confirmationStatus = $this->accountManagement->getConfirmationStatus($newCustomer->getId());
            if ($confirmationStatus !== AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                $this->adminNotification->send($newCustomer);
            }
        }
    }
}
