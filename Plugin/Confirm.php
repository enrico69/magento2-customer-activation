<?php
/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * Date: 05/12/2017
 *
 * Plugin to handle email address confirmation (if required)
 */
namespace Enrico69\Magento2CustomerActivation\Plugin;

use Magento\Customer\Controller\Account\Confirm as TargetClass;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Enrico69\Magento2CustomerActivation\Model\AdminNotification;
use Enrico69\Magento2CustomerActivation\Model\Attribute\Active;

class Confirm
{
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

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
     * @var Active
     */
    protected $activeAttribute;

    /**
     * Confirm constructor.
     * @param RedirectFactory $redirectFactory
     * @param RedirectInterface $redirectInterface
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param ManagerInterface $messageManager
     * @param AdminNotification $adminNotification
     * @param Active $activeAttribute
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        RedirectInterface $redirectInterface,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        ManagerInterface $messageManager,
        AdminNotification $adminNotification,
        Active $activeAttribute
    ) {
        $this->resultRedirectFactory = $redirectFactory;
        $this->redirect = $redirectInterface;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->messageManager = $messageManager;
        $this->adminNotification = $adminNotification;
        $this->activeAttribute = $activeAttribute;
    }

    /**
     * @param TargetClass $subject
     * @param $result
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function afterExecute(TargetClass $subject, $result)
    {
        if ($this->scopeConfig->getValue('customer/create_account/customer_account_activation', ScopeInterface::SCOPE_STORE)
            && $this->customerSession->isLoggedIn()
        ) {
            try {
                $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());

                if (!$this->activeAttribute->isCustomerActive($customer)) {
                    $lastCustomerId = $this->customerSession->getCustomerId();
                    $this->customerSession->logout()->setBeforeAuthUrl($this->redirect->getRefererUrl())
                        ->setLastCustomerId($lastCustomerId);

                    /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('*/*/logoutSuccess');
                    $result = $resultRedirect;

                    $this->adminNotification->send($customer);
                }
            } catch (NoSuchEntityException $ex) {
                // If the customer doesn't exists, let the controller to handle it
                unset($ex);
            }
        }

        return $result;
    }
}
