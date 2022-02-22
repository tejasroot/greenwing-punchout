<?php

namespace Greenwing\Technology\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Cart;
use Greeenwing\Technology\Model\InsertDataFactory;
use Greeenwing\Technology\Model\InsertCartDataFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Request\InvalidRequestException;
 
const TOKEN_ID = 'Token_id';
const EMAIL = 'email';
const CUSTOMERID = 'CustomerID';
/**
 * Class CategoryLinkManagement
 * Managing Rest Api for getting punchout
 */
class CategoryLinkManagement implements \Greenwing\Technology\Api\CategoryLinkManagementInterface
{
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

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;
 
    /**
     * @var \Greenwing\Technology\Api\Data\CategoryProductLinkInterfaceFactory
     */
    protected $productLinkFactory;

    protected $response;
 
    /**
     * CategoryLinkManagement constructor.
     *
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface                   $categoryRepository
     * @param \Greenwing\Technology\Api\Data\CategoryProductLinkInterfaceFactory $productLinkFactory
     */
    public function __construct(
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Greenwing\Technology\Api\Data\CategoryProductLinkInterfaceFactory $productLinkFactory,
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
        $this->categoryRepository = $categoryRepository;
        $this->productLinkFactory = $productLinkFactory;
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
    }
    /**
     * {@inheritdoc}
     */
    public function getAssignedProducts()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $resultJson = $this->resultJsonFactory->create();

        $POSTdata = file_get_contents('php://input', true);
        $datajson = json_decode($POSTdata);
        $email = $datajson->GWTSSO->Email;
        $name = $datajson->GWTSSO->Name;
        $buyerCookie = $datajson->GWTSSO->BuyerCookie;
        // $customerid = $datajson->GWTSSO->CustomerID;
        $customergroup = $datajson->GWTSSO->CustomerGroup;

        // $customer = $this->_customer->getCollection()->addAttributeToSelect('*');
        // $customer = $customer->addAttributeToFilter('entity_id', ['eq' => $customerid])->load();

        $ReturnURL = $datajson->GWTSSO->ReturnURL;
        $namea = explode(" ", $name);
        $fname = $namea[0];
        $lname = $namea[1];

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+";
        $password = substr(str_shuffle($chars), 0, 8);
        $this->login($fname, $lname, $email, $customergroup, $password);
        try {
            $resultJson = $this->resultJsonFactory->create();
            $customer = $this->_customer->getCollection()->addAttributeToSelect('*');
            $customer = $customer->addAttributeToFilter('email', ['eq' => $email])->load();
            $customerid = $customer->getData()[0]['entity_id'];
            $id = $customerid;
            $requestType = $datajson->GWTSSO->Type;

            $message = 'cst_' . $id . '_' . $email . '_' . $password . '_' . $requestType;

            $max_msg_size = 1000;
            $message = substr($message, 0, $max_msg_size);
            
            $model = $this->_insertData->create();
            $msg_bundle = bin2hex(random_bytes(32));
            $model->addData(
                [
                    "user_id" => $id,
                    TOKEN_ID => $msg_bundle,
                    "Orignal_link" => $message,
                    "status" => 0,
                    "BuyerCookie" => $buyerCookie,
                    CUSTOMERID => $customerid,
                    "ReturnURL" => $ReturnURL,
                ]
            );
            $model->save();

            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            $myObj[0]['GWTSSO']['LoggedInURL'] = $baseUrl . 'sso_signin/?key=' . $msg_bundle;
            if ($datajson->GWTSSO->Type == 'EditRequest') {
                $editcartdata = json_decode($POSTdata);
                foreach ($editcartdata->GWTSSO->Items as $cartitem) {
                    $model = $this->_insertCartData->create();
                    $model->addData(
                        [
                        "customerid" => $customerid,
                        EMAIL => $editcartdata->GWTSSO->Email,
                        "item_sku" => $cartitem->SupplierPartID,
                        "qty" => $cartitem->Quantity,
                        ]
                    );
                    $model->save();
                }//endforeach
            }//endif
            return $myObj;
        } catch (Exception $e) {
            return "Something went wrong.";
        }//end try
    }
    public function login($pfname, $plname, $pemail, $customergroup, $ppassword)
    {
        try {
            // Create new customer.
            $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
            $curcustomer = $this->_customer;
            if ($websiteId) {
                $curcustomer->setWebsiteId($websiteId);
            }
            $customerExist = $curcustomer->loadByEmail($pemail);
            if (!$customerExist->getId()) {
                $customer = $this->custFactory->create();
                $customer->setWebsiteId($websiteId);
                // Preparing data for new customer.
                $customer->setEmail($pemail);
                $customer->setFirstname($pfname);
                $customer->setLastname($plname);
                $customer->setPassword($ppassword);
                $customer->setData('group_id', $customergroup);
                $customer->save();
            }
        } catch (Exception $ex) {
            return "Something went wrong.";
        }//end try
    }
}
