<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Model;

use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Response model
 *
 * @method OrderInterface getOrder()
 * @method Response setOrder(OrderInterface $orderId)
 * @method string getWebhookRequestType()
 * @method Response setWebhookRequestType(string $requestType)
 * @method string getTransactionId()
 * @method Response setTransactionId(string $transactionId)
 * @method null|float getAmount()
 * @method Response setAmount(?float $amount)
 * @method string getTransactionStatus()
 * @method Response setTransactionStatus(string $transactionStatus)
 * @method string getEcentricRequest()
 * @method Response setEcentricRequest(string $ecentricRequest)
 */
class Response extends DataObject
{
}
