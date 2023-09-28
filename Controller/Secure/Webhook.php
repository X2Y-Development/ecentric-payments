<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Controller\Secure;

use Ecentric\Payment\Controller\Payment as AbstractPayment;
use Exception;
use Magento\Framework\App\ResponseInterface;

class Webhook extends AbstractPayment
{
    /**
     * @return ResponseInterface
     */
    public function execute(): ResponseInterface
    {
        $requestContent = $this->request->getContent() ?? '';
        $this->ecentricLogger->debug(__('Started processing Ecentric Order via webhook: %1', $requestContent));

        $result = false;

        if (strlen($requestContent) > 0) {
            try {
                $content = $this->serializer->unserialize($requestContent);
                $order = $this->getOrder($content['OrderNumber']);
                $response = $this->setResponseData([
                    'order' => $order,
                    'webhook_request_type' => $content['RequestType'],
                    'transaction_id' => $content['TransactionID'],
                    'amount' => (int) $content['Amount'] / 100,
                    'transaction_status' => $content['TransactionStatus'],
                    'ecentric_request' => $this->request->getContent()
                ]);

                $result = $this->processOrder->execute($response);
            } catch (Exception $e) {
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
