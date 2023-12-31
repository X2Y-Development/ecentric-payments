<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Controller\Secure;

use Ecentric\Payment\Controller\Payment as AbstractPayment;
use Exception;
use Magento\Framework\Controller\ResultInterface;

class Payment extends AbstractPayment
{
    /**
     * @inheritDoc
     */
    public function execute(): ResultInterface
    {
        $this->ecentricLogger->debug('Started processing Ecentric Order: ' . $this->request->getContent());

        $result = false;
        $order = null;
        try {
            $order = $this->getOrder($this->request->getPost('MerchantReference'));
            $response = $this->setResponseData([
                'transaction_id' => $this->request->getPost("TransactionID"),
                'order' => $order,
                'transaction_status' => $this->request->getPost("Result"),
                'amount' => (int) $this->request->getPost("Amount") / 100,
                'request' => $this->request->getContent()
            ]);

            $result = $this->processOrder->execute($response);
        } catch (Exception $e) {
            $this->ecentricLogger->error($e->getMessage());
        }

        if ($order) {
            $this->registerPayment->setLastDataToSession($order);
        }

        $this->ecentricLogger->debug('Finished processing Ecentric Order');

        if ($result === true) {
            return $this->redirect('checkout/onepage/success');
        } else {
            $this->registerPayment->restoreQuote();
            $message = __(
                'Payment was not completed successfully.
                     Please try again, and complete the payment to process the order.'
            );

            if ($this->request->getPost('FailureMessage')) {
                $message = __($this->request->getPost('FailureMessage'));
            }

            $this->messageManager->addErrorMessage($message);

            return $this->redirect('checkout/cart');
        }
    }
}
