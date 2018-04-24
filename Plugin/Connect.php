<?php
/**
 * This plugin disconnect the user after login
 * if its account has not been activated by an admin AND
 * if account activation is required
 *
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * Date: 01/08/2017
 * Time: 11:31
 */
namespace Enrico69\Magento2CustomerActivation\Plugin;

use Magento\Customer\Controller\Account\LoginPost;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Enrico69\Magento2CustomerActivation\Model\Attribute\Active;

class Connect
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
     * @var Active
     */
    protected $activeAttribute;

    /**
     * Connect constructor.
     * @param RedirectFactory $redirectFactory
     * @param RedirectInterface $redirectInterface
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param ManagerInterface $messageManager
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
        Active $activeAttribute
    ) {
        $this->resultRedirectFactory = $redirectFactory;
        $this->redirect = $redirectInterface;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->messageManager = $messageManager;
        $this->activeAttribute = $activeAttribute;
    }

    /**
     * @param \Magento\Customer\Controller\Account\LoginPost $subject
     * @param $result
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterExecute(LoginPost $subject, $result)
    {
        if ($this->scopeConfig->getValue('customer/create_account/customer_account_activation', ScopeInterface::SCOPE_STORE)) {
            try {
                $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());

                if (!$this->activeAttribute->isCustomerActive($customer)) {
                    $lastCustomerId = $this->customerSession->getCustomerId();
                    $this->customerSession->logout()->setBeforeAuthUrl($this->redirect->getRefererUrl())
                        ->setLastCustomerId($lastCustomerId);

                    $this->messageManager->addNoticeMessage(__('Your account has not been enabled yet'));

                    /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('*/*/logoutSuccess');
                    $result = $resultRedirect;
                }
            } catch (NoSuchEntityException $ex) {
                // If the customer doesn't exists, let the controller to handle it
                unset($ex);
            }
        }

        return $result;
    }
}
