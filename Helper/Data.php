<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Helper;

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
    public const CREDENTIALS_KEYS = [
        'merchant_guid_live',
        'merchant_key_live',
        'merchant_guid_sandbox',
        'merchant_key_sandbox',
    ];

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
        $value = $this->scopeConfig->getValue(self::XPATH_API . $path, $scope);
        if (in_array($path, self::CREDENTIALS_KEYS)) {
            return $this->encryptor->decrypt($value);
        }

        return $value;
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
}
