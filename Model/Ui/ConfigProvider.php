<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Ecentric\Payment\Helper\Data as EcentricHelper;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @param EcentricHelper $ecentricHelper
     */
    public function __construct(
        private EcentricHelper $ecentricHelper
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                EcentricHelper::METHOD_CODE => [
                    'isActive' => $this->ecentricHelper->isActiveModule()
                ]
            ]
        ];
    }
}
