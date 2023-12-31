<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Block\Customer;

use Ecentric\Payment\Service\Config as EcentricConfig;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class WalletManagement extends Template
{
    /**
     * @param EcentricConfig $ecentricConfig
     * @param Session $customerSession
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        protected EcentricConfig $ecentricConfig,
        protected Session $customerSession,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getWalletParameters(): array
    {
        $merchantId = $this->ecentricConfig->getMerchantId();
        $merchantKey = $this->ecentricConfig->getMerchantKey();
        $userId = $this->customerSession->getCustomerId();
        $checksumData = implode('|', [
            $merchantKey,
            $merchantId,
            $userId
        ]);
        $checksum = hash('sha256', $checksumData);

        return [
            'MerchantID' => $merchantId,
            'UserID' => $userId,
            'Checksum' => strtoupper($checksum),
        ];
    }

    /**
     * @return string
     */
    public function getApiMode(): string
    {
        return $this->ecentricConfig->getApiMode();
    }
}
