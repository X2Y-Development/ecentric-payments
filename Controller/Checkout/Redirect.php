<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Controller\Checkout;

use Ecentric\Payment\Model\CustomerSession;
use Ecentric\Payment\Model\CustomerSessionFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\View\Result\PageFactory;

class Redirect implements HttpGetActionInterface
{
    /**
     * @param PageFactory $resultPageFactory
     * @param Session $session
     * @param CustomerSessionFactory $customerSession
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        private PageFactory $resultPageFactory,
        private Session $session,
        private CustomerSessionFactory $customerSession,
        private EncryptorInterface $encryptor
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(): ResultInterface
    {
        $order = $this->session->getLastRealOrder();
        $sessionId = $this->encryptor->encrypt($_COOKIE['PHPSESSID']);

        /** @var CustomerSession $customerSession */
        $customerSession = $this->customerSession->create();
        $customerSession->setCustomerId($order->getCustomerId());
        $customerSession->setOrderId($order->getId());
        $customerSession->setCustomerSessionId($sessionId);
        $customerSession->save();

        return $this->resultPageFactory->create();
    }
}
