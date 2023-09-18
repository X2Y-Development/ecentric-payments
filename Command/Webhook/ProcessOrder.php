<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Command\Webhook;

use Ecentric\Payment\Model\Response;
use Ecentric\Payment\Service\RegisterPayment;
use Magento\Framework\Exception\LocalizedException;

class ProcessOrder
{
    /**
     * @param RegisterPayment $registerPayment
     */
    public function __construct(
        private RegisterPayment $registerPayment
    ) {
    }

    /**
     * @param Response $response
     * @return bool
     * @throws LocalizedException
     */
    public function execute(Response $response): bool
    {
        return $this->registerPayment->processOrder($response);
    }
}
