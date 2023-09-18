<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Model\Ui;

use Ecentric\Payment\Service\Config as ServiceConfig;
use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @param ServiceConfig $ecentricConfig
     */
    public function __construct(
        private ServiceConfig $ecentricConfig
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                ServiceConfig::METHOD_CODE => [
                    'isActive' => $this->ecentricConfig->isActiveModule()
                ]
            ]
        ];
    }
}
