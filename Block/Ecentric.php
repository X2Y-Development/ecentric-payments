<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Block;

use Ecentric\Payment\Helper\Data as EcentricHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Ecentric extends Template
{
    /**
     * @param CheckoutSession $checkoutSession
     * @param EcentricHelper $ecentricHelper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        private CheckoutSession $checkoutSession,
        private EcentricHelper $ecentricHelper,
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
            $merchantId = $this->ecentricHelper->getMerchantId();
            $merchantKey = $this->ecentricHelper->getMerchantKey();
            $transactionType = 'Payment';
            $merchantRef = 'Ord' . $lastOrderId;
            $amount = $lastOrder->getGrandTotal() * 100;
            $currency = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
            $checksum = hash('sha256', $merchantKey
                . '|' . $merchantId
                . '|' . $transactionType
                . '|' . $amount
                . '|' . $currency
                . '|' . $merchantRef);

            return [
                'MerchantID'        => $merchantId,
                'TransactionType'   => $transactionType,
                'MerchantReference' => $merchantRef,
                'Amount'            => $amount,
                'Currency'          => $currency,
                'Checksum'          => strtoupper($checksum)
            ];
        }

        return [];
    }

    /**
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->ecentricHelper->getApiUrl();
    }
}
