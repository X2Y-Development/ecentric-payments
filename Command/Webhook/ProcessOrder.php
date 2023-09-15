<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Command\Webhook;

use Ecentric\Payment\Helper\Data as EcentricHelper;
use Ecentric\Payment\Model\Web\Hook;
use Ecentric\Payment\Model\Web\HookFactory;
use Ecentric\Payment\Model\ResourceModel\Web\Hook as ResourceHooks;

class ProcessOrder
{
    /**
     * @param HookFactory $webhookFactory
     * @param ResourceHooks $hookResource
     * @param EcentricHelper $ecentricHelper
     */
    public function __construct(
        private HookFactory $webhookFactory,
        private ResourceHooks $hookResource,
        private EcentricHelper $ecentricHelper,
    ) {
    }

    /**
     * @param array $content
     * @return void
     */
    public function execute(array $content): void
    {
        if ($content['TransactionStatus'] === 'Success') {
            $hook = $this->setWebHookData($content);
            $this->hookResource->save($hook);
            $this->ecentricHelper->processOrder($hook);
        }
    }

    /**
     * @param array $content
     * @return Hook
     */
    private function setWebHookData(array $content): Hook
    {
        /** @var Hook $webhook */
        $webhook = $this->webhookFactory->create();

        $webhook->setRequestType($content['RequestType'] ?? '');
        $webhook->setMerchantId($content['MerchantID'] ?? '');
        $webhook->setCardAcceptorId($content['CardAcceptorID'] ?? '');
        $webhook->setTransactionId($content['TransactionID'] ?? '');
        $webhook->setTranscationTimestamp($content['TransactionDateTime'] ?? '');
        $webhook->setReconciliationId($content['ReconID'] ?? '');
        $webhook->setAmount($content['Amount'] ?? '');
        $webhook->setCurrencyCode($content['CurrencyCode'] ?? '');
        $webhook->setOrderId($content['OrderNumber'] ?? null);
        $webhook->setPaymentService($content['PaymentService'] ?? '');
        $webhook->setAuthCode($content['AuthCode'] ?? '');
        $webhook->setTransactionStatus($content['TransactionStatus'] ?? '');
        $webhook->setResponseSource($content['ResponseSource'] ?? '');
        $webhook->setResponseCode($content['ResponseCode'] ?? '');
        $webhook->setMaskedCardNumber($content['MaskedCardNumber'] ?? '');
        $webhook->setExpiryMonth($content['ExpiryMonth'] ?? '');
        $webhook->setExpiryYear($content['ExpiryYear'] ?? '');
        $webhook->setSystemsTraceAuditNumber($content['SystemsTraceAuditNumber'] ?? '');
        $webhook->setSaleReconciliationId($content['SaleReconID'] ?? '');
        $webhook->setRetrievalReferenceNumber($content['RetrievalReferenceNumber'] ?? '');
        $webhook->setCommProtocol($content['CommProtocol'] ?? '');
        $webhook->setInitialAuthRef($content['InitialAuthRef'] ?? '');
        $webhook->setRequest($content['request'] ?? '');

        return $webhook;
    }
}
