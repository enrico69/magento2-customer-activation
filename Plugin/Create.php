<?php
/**
 * This plugin disconnect the user after account
 * creation if customer account activation by
 * admin is required.
 *
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * Date: 01/08/2017
 * Time: 11:33
 */
namespace Enrico69\Magento2CustomerActivation\Plugin;

use Magento\Customer\Controller\Account\CreatePost;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Session;

class Create
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
     * Connect constructor.
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param \Magento\Framework\App\Response\RedirectInterface $redirectInterface
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        RedirectInterface $redirectInterface,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        Session $customerSession
    ) {
        $this->resultRedirectFactory = $redirectFactory;
        $this->redirect = $redirectInterface;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Customer\Controller\Account\CreatePost $subject
     * @param $result
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function afterExecute(CreatePost $subject, $result)
    {
        if ($this->scopeConfig->getValue('customer/create_account/customer_account_activation', ScopeInterface::SCOPE_STORE)
            && $this->customerSession->getRegisterSuccess()
        ) {
            $lastCustomerId = $this->customerSession->getCustomerId();
            $this->customerSession->logout()->setBeforeAuthUrl($this->redirect->getRefererUrl())
                ->setLastCustomerId($lastCustomerId);

            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/logoutSuccess');
            $result = $resultRedirect;
        }

        return $result;
    }
}
