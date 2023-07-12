<?php
namespace Ecentric\Payment\Controller\Checkout;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
class Success extends \Magento\Framework\App\Action\Action
{
	protected $_checkoutSession;
	protected $_orderFactory;
	protected $_storeManager;
	protected $_scopeConfig;
	protected $_encryptor;
 
    public function __construct(Context $context, \Magento\Checkout\Model\Session $checkoutSession, \Magento\Sales\Model\OrderFactory $orderFactory, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\Encryption\EncryptorInterface $encryptor)
    {
		$this->_checkoutSession = $checkoutSession;
		$this->_orderFactory = $orderFactory;
		$this->_scopeConfig = $scopeConfig;
		$this->_encryptor = $encryptor;
        parent::__construct($context);
    }
	public function execute()
	{
		$orderId = $this->getRequest()->getPost("orderId");
		$TransactionID = $this->getRequest()->getPost("TransactionID");
		$Result = $this->getRequest()->getPost("Result");
		$FailureMessage = $this->getRequest()->getPost("FailureMessage");
		$Checksum = $this->getRequest()->getPost("Checksum");
		if($Result == 'Success'){
			if ($orderId && $TransactionID) {
				$order = $this->_orderFactory->create()->loadByIncrementId($orderId);
				$state = Order::STATE_PAYMENT_REVIEW;
				$order->setStatus($state);
				$order->save();
				$this->_redirect('checkout/onepage/success');
			}
		} else {
			$params = array('fail_message' => $FailureMessage);
			$this->_redirect('ece/checkout/fail/', array('_query' => $params));
		}
	}
}
?>