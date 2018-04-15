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
use Enrico69\Magento2CustomerActivation\Setup\InstallData;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

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
     * Connect constructor.
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param \Magento\Framework\App\Response\RedirectInterface $redirectInterface
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        RedirectInterface $redirectInterface,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        ManagerInterface $messageManager
    ) {
        $this->resultRedirectFactory = $redirectFactory;
        $this->redirect = $redirectInterface;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->messageManager = $messageManager;
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

                if ($customer->getCustomAttribute(InstallData::CUSTOMER_ACCOUNT_ACTIVE)->getValue() !== '1') {
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
