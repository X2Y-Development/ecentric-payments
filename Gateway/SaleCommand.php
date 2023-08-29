<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Gateway;

use Magento\Payment\Gateway\CommandInterface;
use Ecentric\Payment\Helper\Data as EcentricHelper;

class SaleCommand implements CommandInterface
{
    /**
     * @param array $commandSubject
     * @return void
     */
    public function execute(array $commandSubject)
    {
        $payment = $commandSubject["payment"]->getPayment();
        $ecentricInfo = $payment->getAdditionalInformation(EcentricHelper::PAYMENT_ADD_INFO_KEY);
        /** TODO: Get transaction id from $ecentric info */
        $payment->setTransactionId('');
    }
}
