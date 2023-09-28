<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Plugin;

use Ecentric\Payment\Model\CustomerSession;
use Ecentric\Payment\Model\CustomerSessionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManager;

class SetSessionId
{
    /**
     * @param RequestInterface $request
     * @param CustomerSessionFactory $customerSession
     */
    public function __construct(
        private RequestInterface $request,
        private CustomerSessionFactory $customerSession
    ) {
    }

    /**
     * @param SessionManager $subject
     * @return void
     */
    public function beforeStart(SessionManager $subject): void
    {
        if ($this->request->isPost() && str_contains($this->request->getPathInfo(), '/ecentric/secure/payment')) {
            $orderId = $this->getOrderId($this->request->getPost('MerchantReference'));

            if ($orderId === null) {
                return;
            }

            /** @var CustomerSession $customerSession */
            $customerSession = $this->customerSession->create();
            $customerSession->loadByOrderId($orderId);

            $ssid = $customerSession->getCustomerSessionId() ?? null;

            if ($ssid) {
                $_COOKIE['PHPSESSID'] = $ssid;
            }
        }
    }

    /**
     * @param string $merchantRef
     * @return int|string|null
     */
    private function getOrderId(string $merchantRef):int|string|null
    {
        $pattern = '/\d+/';
        preg_match($pattern, $merchantRef, $matches);

        return $matches[0] ?? null;
    }
}
