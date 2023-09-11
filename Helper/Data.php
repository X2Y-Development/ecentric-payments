<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Helper;

use Ecentric\Payment\Model\Config\Source\Mode;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    public const METHOD_CODE = 'ecentric';
    public const XPATH_PATTERN = 'payment/%s/general/%s';
    public const XPATH_GENERAL = 'payment/ecentric/general/';
    public const XPATH_API = 'payment/ecentic/api/';
    public const PAYMENT_ADD_INFO_KEY = 'ecentric_payment_info';
    public const HPP_LIVE = 'https://sandbox.ecentric.co.za/HPP'; // @TODO: get correct LIVE url!
    public const HPP_TEST = 'https://sandbox.ecentric.co.za/HPP';
    /**
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        private EncryptorInterface $encryptor,
        private StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
    }

    /**
     * Get general information
     *
     * @param string $path
     * @param string $scope
     * @param int|string|null $scopeCode
     * @return mixed
     */
    public function getGeneralGroupInfo(
        string $path,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        null|int|string $scopeCode = null
    ): mixed {
        return $this->scopeConfig->getValue(self::XPATH_GENERAL . $path, $scope, $scopeCode);
    }

    /**
     * Get api data
     *
     * @param string $path
     * @param string $scope
     * @return mixed
     */
    public function getApiGroupInfo(string $path, string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT): mixed
    {
        return $this->scopeConfig->getValue(self::XPATH_API . $path, $scope);
    }

    /**
     * @param string $scope
     * @return string
     */
    public function getMerchantId(string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT): string
    {
        if ($this->getApiMode() === 'sandbox') {
            $merchantId = $this->getApiGroupInfo('merchant_guid_sandbox', $scope);

            return $this->encryptor->decrypt($merchantId);
        }

        $merchantId = $this->getApiGroupInfo('merchant_guid_live', $scope);

        return $this->encryptor->decrypt($merchantId);
    }

    /**
     * @param string $scope
     * @return string
     */
    public function getMerchantKey(string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT): string
    {
        if ($this->getApiMode() === 'sandbox') {
            $merchantId = $this->getApiGroupInfo('merchant_key_sandbox', $scope);

            return $this->encryptor->decrypt($merchantId);
        }

        $merchantId = $this->getApiGroupInfo('merchant_key_live', $scope);

        return $this->encryptor->decrypt($merchantId);
    }

    /**
     * Check if credentials set and payment method enabled
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isActiveModule(): bool
    {
        return $this->getGeneralGroupInfo('active', '', $this->storeManager->getStore()->getId())
            && $this->isActiveMode();
    }

    /**
     * @param string $scope
     * @return string
     */
    public function getApiMode(string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT): string
    {
        return (string)$this->getApiGroupInfo('mode', $scope);
    }

    /**
     * Get New Order Status
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getNewOrderStatus(): string
    {
        return (string)$this->getGeneralGroupInfo(
            'new_order_status',
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * @return bool
     */
    private function isActiveMode(): bool
    {
        return $this->getMerchantId() && $this->getMerchantKey();
    }

    public function getApiUrl(): string
    {
        return ($this->getApiMode() == Mode::PRODUCTION) ? self::HPP_LIVE : self::HPP_TEST;
    }
}
