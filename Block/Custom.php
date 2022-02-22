<?php

namespace Greenwing\Technology\Block;

class Custom extends \Magento\Framework\View\Element\Template
{
    private $requestjson;

    private $supplierpartid;
    
    protected $_checkoutSession;

    protected $_cart;

    protected $session;

    protected $prRepository;
    
    protected $_scopeConfig;

    protected $_customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->requestjson = 'request';
        $this->supplierpartid = 'SupplierPartID';
        $this->_cart = $cart;
        $this->_checkoutSession = $checkoutSession;
        $this->session = $session;
        $this->prRepository = $productRepository;
        $this->_scopeConfig = $scopeConfig;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }
 
    public function getCartData()
    {
        $getCurrentQuote = $this->_checkoutSession->getQuote();
        $getAllitems = $getCurrentQuote->getAllItems();
        $buyerCookie = $this->_customerSession->getBuyerCookie();
        $customerId = $this->_customerSession->setCustomID();
        $returnUrl = $this->_customerSession->getReturnURL();
        $supplierPartAuxId = $getCurrentQuote->getId();
        $requestArray = [];
        $requestArray[$this->requestjson]['type'] = "ReturnCart";
        $requestArray[$this->requestjson]['buyercookie'] = $buyerCookie;
        $requestArray[$this->requestjson]['CustomerID'] = $customerId;
        $itemCount = 0;
        $debugEnable = $this->_scopeConfig->getValue("technology/general/enable");

        foreach ($getAllitems as $_item) {
            if (isset($cartitem[$this->supplierpartid]) && $_item['sku'] == $cartitem[$this->supplierpartid]) {
                continue;
            }
            $product = $this->prRepository->getById($_item['product_id']);
            $itemCategory = $product->getCategoryIds()[0];
            $cartitem[$this->supplierpartid] = $_item['sku'];
            $cartitem['Description'] = $_item['name'];
            $cartitem['Quantity'] = (int)$_item['qty'];
            $cartitem['Supplierpartauxid'] = $supplierPartAuxId;
            $cartitem['UnitPrice'] = $_item['price'];
            $cartitem['UOM'] = 'EA';
            $cartitem['Currency'] = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();

            $cartitem['UNSPSC'] = $itemCategory[0];
            $itemCount++;
            $requestArray[$this->requestjson]['body']['items'][] = $cartitem;
        }
        
        $responseArray = [];
        $responseArray['ReturnURL'] = $returnUrl;
        $responseArray[$this->requestjson] = $requestArray;
        $responseArray['debugEnable'] = $debugEnable;
 
        return json_encode($responseArray);
    }
}
