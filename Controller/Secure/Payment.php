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
        $response = $this->setResponseData([
            'transaction_id' => $this->request->getPost("TransactionID"),
            'order_id' => $this->getOrderId($this->request->getPost('MerchantReference')),
            'transaction_status' => $this->request->getPost("Result"),
            'amount' => (float)$this->request->getPost("Amount"),
            'request' => $this->request->getContent()
        ]);

        try {
            $result = $this->processOrder->execute($response);
        } catch (Exception $e) {
            $this->ecentricLogger->error($e->getMessage());
        }

        $this->ecentricLogger->debug('Finished processing Ecentric Order');

        if ($result === true) {
            return $this->redirect('checkout/onepage/success');
        } else {
            $this->registerPayment->restoreQuote();
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your order. Please try again')
            );

            return $this->redirect('checkout/cart');
        }
    }
}
