<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Service;

use Ecentric\Payment\Model\Config\Source\Mode;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config
{
    public const METHOD_CODE = 'ecentric';
    public const XPATH_MODE = 'mode';
    public const XPATH_MERCHANT_GUID_SANDBOX = 'merchant_guid_sandbox';
    public const XPATH_MERCHANT_GUID_LIVE = 'merchant_guid_live';
    public const XPATH_MERCHANT_KEY_SANDBOX = 'merchant_key_sandbox';
    public const XPATH_MERCHANT_KEY_LIVE = 'merchant_key_live';
    public const XPATH_NEW_ORDER_STATUS = 'new_order_status';
    public const XPATH_ACTIVE = 'active';
    public const XPATH_PATTERN = 'payment/%s/general/%s';
    public const XPATH_GENERAL = 'payment/ecentric/general/';
    public const XPATH_API = 'payment/ecentric/api/';
    public const HPP_LIVE = 'https://sandbox.ecentric.co.za/HPP'; // @TODO: get correct LIVE url!
    public const HPP_TEST = 'https://sandbox.ecentric.co.za/HPP';

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private StoreManagerInterface $storeManager,
        private ScopeConfigInterface $scopeConfig
    ) {
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
    public function getMerchantId(string $scope = ScopeInterface::SCOPE_WEBSITE): string
    {
        if ($this->getApiMode() === MODE::SANDBOX) {
            return $this->getApiGroupInfo(self::XPATH_MERCHANT_GUID_SANDBOX, $scope);
        }

        return $this->getApiGroupInfo(self::XPATH_MERCHANT_GUID_LIVE, $scope);
    }

    /**
     * @param string $scope
     * @return string
     */
    public function getMerchantKey(string $scope = ScopeInterface::SCOPE_WEBSITE): string
    {
        if ($this->getApiMode() === MODE::SANDBOX) {
            return $this->getApiGroupInfo(self::XPATH_MERCHANT_KEY_SANDBOX, $scope);
        }

        return $this->getApiGroupInfo(self::XPATH_MERCHANT_KEY_LIVE, $scope);
    }

    /**
     * Check if credentials set and payment method enabled
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isActiveModule(): bool
    {
        return $this->getGeneralGroupInfo(
            self::XPATH_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
            ) && $this->isActiveMode();
    }

    /**
     * @param string $scope
     * @return string
     */
    public function getApiMode(string $scope = ScopeInterface::SCOPE_WEBSITE): string
    {
        return (string)$this->getApiGroupInfo(self::XPATH_MODE, $scope);
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
            self::XPATH_NEW_ORDER_STATUS,
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

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return ($this->getApiMode() == Mode::PRODUCTION) ? self::HPP_LIVE : self::HPP_TEST;
    }
}
