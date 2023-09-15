<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Controller\Secure;

use Ecentric\Payment\Controller\Payment as AbstractPayment;
use Magento\Framework\Controller\ResultInterface;

class Payment extends AbstractPayment
{
    /**
     * @inheritDoc
     */
    public function execute(): ResultInterface
    {
        $this->ecentricLogger->debug('Payment Ecentric' . $this->request->getContent());
        $merchantRef = $this->request->getPost('MerchantReference');
        $pattern = '/\d+/';
        preg_match($pattern, $merchantRef, $orderId);
        $transactionId = $this->request->getPost("TransactionID");
        $status = $this->request->getPost("Result");
        $amount = $this->request->getPost("Amount");
        $message = $this->request->getPost("FailureMessage");
        $checksum = $this->request->getPost("Checksum");
        $content = [
            'TransactionID' => $transactionId,
            'OrderNumber' => $orderId[0],
            'TransactionStatus' => $status,
            'Amount' => $amount,
            'request' => $this->request->getContent()
        ];

        $this->processOrder->execute($content);

        if ($status === 'Success') {
            return $this->redirect('checkout/onepage/success');
        } else {
            $this->ecentricHelper->restoreQuote();

            return $this->redirect('checkout/cart');
        }
    }
}
