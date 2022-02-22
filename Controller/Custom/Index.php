<?php

namespace Greenwing\Technology\Controller\Custom;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;

    protected $_customerSession;

    protected $storeManager;

     /**
      *
      * @var urlReWriteFactory
      */
    protected $urlRWFactory;

    /**
     *
     * @var urlReWrite
     */
    protected $urlRW;
 
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
        \Magento\UrlRewrite\Model\UrlRewrite $urlRewrite
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->urlRWFactory = $urlRewriteFactory;
        $this->urlRW = $urlRewrite;
        parent::__construct($context);
    }
 
    public function execute()
    {
        return $this->_resultPageFactory->create();
    }
}
