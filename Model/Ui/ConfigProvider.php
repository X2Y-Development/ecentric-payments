<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Ecentric\Payment\Helper\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @param ConfigInterface $config
     * @param Data $data
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private ConfigInterface $config,
        private Data $data,
        private StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                Data::METHOD_CODE => [
                    'isActive' => $this->isActive()
                ]
            ]
        ];
    }

    /**
     * Check if credentials set and payment method enabled
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    private function isActive(): bool
    {
        return $this->data->getApiGroupInfo('merchant_guid_live') &&
            $this->data->getApiGroupInfo('merchant_key_live') &&
            $this->config->getValue('active', $this->storeManager->getStore()->getId());
    }
}
