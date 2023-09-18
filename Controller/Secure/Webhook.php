<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Controller\Secure;

use Ecentric\Payment\Controller\Payment as AbstractPayment;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;

class Webhook extends AbstractPayment
{
    /**
     * @return ResponseInterface
     */
    public function execute(): ResponseInterface
    {
        $this->ecentricLogger->debug('Started processing order via webhook: ' . $this->request->getContent());

        $result = false;
        $content = $this->serializer->unserialize($this->request->getContent());
        $response = $this->setResponseData([
            'order_id' => $this->getOrderId($content['OrderNumber']),
            'webhook_request_type' => $content['RequestType'],
            'transaction_id' => $content['TransactionID'],
            'amount' => (float)$content['Amount'],
            'transaction_status' => $content['TransactionStatus'],
            'ecentric_request' => $this->request->getContent()
        ]);

        if (strlen($this->request->getContent()) > 0) {
            try {
                $result = $this->processOrder->execute($response);
            } catch (LocalizedException $e) {
                $this->ecentricLogger->error($e->getMessage());
            }
        }

        $this->ecentricLogger->debug('Finished processing order via webhook');

        if ($result === false) {
            return $this->response->setHttpResponseCode(400);
        }

        return $this->response->setHttpResponseCode(200);
    }
}
