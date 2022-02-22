<?php

/**
 *
 * @package    GreeenwingTechnology
 * @subpackage GreewingTechnology
 * @author     Squiz Pty Ltd <products@squiz.net>
 * @copyright  1997-2005 The Greenwing Technology
 */

namespace Greenwing\Technology\Controller\Index;

use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Cart;
use Greeenwing\Technology\Model\InsertDataFactory;
use Greeenwing\Technology\Model\InsertCartDataFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Request\InvalidRequestException;

const TOKEN_ID = 'Token_id';
const EMAIL = 'email';
const CUSTOMERID = 'CustomerID';

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     *
     * @var pagefactory
     */
    protected $pgFactory;

    /**
     *
     * @var request
     */
    protected $request;

    /**
     *
     * @var customer
     */
    protected $_customer;

    /**
     *
     * @var customerFactory
     */
    protected $custFactory;

    /**
     *
     * @var storeManager
     */
    protected $storeManager;

    /**
     *
     * @var resource
     */
    protected $objresource;

    /**
     *
     * @var cart
     */
    protected $cart;

    /**
     *
     * @var checkoutSession
     */
    protected $chkoutSession;

    /**
     *
     * @var resultJsonFactory
     */
    protected $resultJsonFactory;

    /**
     *
     * @var customerSession
     */
    protected $_customerSession;

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

    protected $session;

    protected $_insertData;

    protected $_insertCartData;

    protected $resultRedirect;
    
    protected $_result;

    protected $_logger;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Customer $customers,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Session $customerSession,
        Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
        \Magento\UrlRewrite\Model\UrlRewrite $urlRewrite,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Greenwing\Technology\Model\InsertDataFactory  $insertData,
        \Greenwing\Technology\Model\InsertCartDataFactory  $insertCartData,
        \Magento\Framework\Controller\ResultFactory $result,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->custFactory = $customerFactory;
        $this->_customer = $customers;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_customerSession = $customerSession;
        $this->cart = $cart;
        $this->chkoutSession = $checkoutSession;
        $this->urlRWFactory = $urlRewriteFactory;
        $this->urlRW = $urlRewrite;
        $this->session = $session;
        $this->_insertData = $insertData;
        $this->_insertCartData = $insertCartData;
        $this->resultRedirect = $result;
        $this->_logger = $logger;
        return parent::__construct($context);
    }
    
    public function execute()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $urlparams = $this->request->getParams();
        try {

        if (isset($urlparams['key'])) {
            $key = $urlparams['key'];
            $data = $this->_insertData->create()->getCollection();
            foreach ($data as $ssodt) {
                if ($ssodt->getData()[TOKEN_ID] == $key) {
                    $rows[0] = $ssodt->getData();
                }
            }
            $setvalue = explode('_', $rows[0]['Orignal_link']);
            $sso_user = $this->_insertData->create();
                       
            $ssoUpdate = $sso_user->load($rows[0][TOKEN_ID], TOKEN_ID);
            $ssoUpdate->setStatus(1);
            $ssoUpdate->save();
            $customer = $this->_customer;

            $customerdata = $this->_customer->getCollection();
            $customerdata = $customerdata->addAttributeToSelect('*');
            $customerdata = $customerdata->addAttributeToFilter(EMAIL, ['eq' => $setvalue[2]])->load();
            $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
         
            $customer->setWebsiteId($websiteId);
        
            if (isset($customerdata->getData()[0][EMAIL])) {
                $customer_login = $this->_customer->loadByEmail($setvalue[2]);
            } else {
                $customerbyid = $this->_customer->getCollection()->addAttributeToSelect('*');
                $customerbyid = $customerbyid->addAttributeToFilter('entity_id', ['eq' => $rows[0][CUSTOMERID]]);
                $customerbyid = $customerbyid->load();
                $customer = $this->_customer->loadByEmail($customerbyid->getData()[0][EMAIL]);
            }

            $this->_customerSession->setCustomerAsLoggedIn($customer);
            $this->_customerSession->setBuyerCookie($rows[0]['BuyerCookie']);
            $this->_customerSession->setCustomID($rows[0][CUSTOMERID]);
            $this->_customerSession->setReturnURL($rows[0]['ReturnURL']);

            $cartrows = $this->_insertCartData->create()->getCollection();
            $cartQuote = $this->chkoutSession->getQuote();
            $items = $cartQuote->getAllItems();
            $this->_logger->debug(count($items));

            if ($setvalue[4] == 'SetupRequest') {
                foreach ($items as $item) {
                    $this->cart->removeItem($item->getId())->save();
                }
            }
            if ($setvalue[4] == 'EditRequest') {
                foreach ($items as $item) {
                    foreach ($cartrows as $cartitem) {
                        if ($cartitem->getData()[EMAIL] == $customer->getData()[EMAIL] && $cartitem['item_sku'] == $item->getSku()) {
                            $itemData = [$item->getId() => ['qty' => $cartitem['qty']]];
                            $this->cart->updateItems($itemData)->save();
                        }
                    }
                }
            }
            try {
                $model = $this->_insertCartData->create();
                $model->load($customer->getData()[EMAIL], EMAIL);
                $model->delete();
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            return $this->resultRedirectFactory->create()->setUrl($baseUrl);
        }//end if
    } catch (\Exception $e) {
        $this->logger->critical($e->getMessage());

    }

    }
}
