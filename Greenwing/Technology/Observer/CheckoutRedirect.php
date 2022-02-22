<?php

namespace Greenwing\Technology\Observer;

class CheckoutRedirect implements \Magento\Framework\Event\ObserverInterface
{
    protected $_resultPageFactory;

    protected $_customerSession;

    protected $storeManager;

    protected $redirect;

    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->redirect = $redirect;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $returnUrl = $this->_customerSession->getReturnURL();
        if ($returnUrl == null) {
            $controller = $observer->getControllerAction();
            return $this->_resultPageFactory->create();
        } else {
            $controller = $observer->getControllerAction();
            $this->redirect->redirect($controller->getResponse(), 'sso_signin/custom/');
            return $this;
        }
    }
}
