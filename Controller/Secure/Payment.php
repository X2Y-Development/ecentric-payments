<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Controller\Secure;

use Ecentric\Payment\Controller\Payment as AbstractPayment;
use Magento\Framework\App\ResponseInterface;

class Payment extends AbstractPayment
{
    /**
     * @inheritDoc
     */
    public function execute(): ResponseInterface
    {
        $orderId = $this->request->getPost("orderId");
        $transactionId = $this->request->getPost("TransactionID");
        $status = $this->request->getPost("Result");
        $message = $this->request->getPost("FailureMessage");
        $checksum = $this->request->getPost("Checksum");
        $content = [
            'TransactionId' => $transactionId,
            'OrderNumber' => $orderId,
            'TransactionStatus' => $status
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
