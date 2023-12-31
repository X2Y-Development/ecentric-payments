<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Block\Checkout;

use Ecentric\Payment\Service\Config as EcentricConfig;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Ecentric extends Template
{
    private const TRANSACTION_TYPE = 'Payment';

    /**
     * @param CheckoutSession $checkoutSession
     * @param EcentricConfig $ecentricConfig
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        private CheckoutSession $checkoutSession,
        private EcentricConfig $ecentricConfig,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getFormData(): array
    {
        $lastOrder = $this->checkoutSession->getLastRealOrder();

        if ($lastOrder->getEntityId()) {
            $lastOrderId = $lastOrder->getEntityId();
            $merchantId = $this->ecentricConfig->getMerchantId();
            $merchantKey = $this->ecentricConfig->getMerchantKey();
            $transactionType = self::TRANSACTION_TYPE;
            $merchantRef = 'Ord' . $lastOrderId;
            $amount = $lastOrder->getGrandTotal() * 100;
            $currency = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
            $checksumData = implode('|', [
                $merchantKey,
                $merchantId,
                $transactionType,
                $amount,
                $currency,
                $merchantRef
            ]);
            $checksum = hash('sha256', $checksumData);

            return [
                'MerchantID' => $merchantId,
                'TransactionType' => $transactionType,
                'MerchantReference' => $merchantRef,
                'Amount' => $amount,
                'Currency' => $currency,
                'Checksum' => strtoupper($checksum)
            ];
        }

        return [];
    }

    /**
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->ecentricConfig->getApiUrl();
    }
}
