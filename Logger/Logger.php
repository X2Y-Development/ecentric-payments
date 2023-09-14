<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Logger;

use DateTimeZone;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Monolog\Logger as MonologLogger;

class Logger extends MonologLogger
{
    /**
     * @param string $name
     * @param ScopeConfigInterface $scopeConfig
     * @param array $handlers
     * @param array $processors
     * @param DateTimeZone|null $timezone
     */
    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        string $name,
        array $handlers = [],
        array $processors = [],
        ?DateTimeZone $timezone = null
    ) {
        parent::__construct($name, $handlers, $processors, $timezone);
    }

    /**
     * @param string $message
     * @param array $context
     * @param bool $isDebug
     * @return void
     */
    public function debug($message, array $context = [], bool $isDebug = false): void
    {
        if ($this->scopeConfig->isSetFlag('payment/ecentric/general/debug')) {
            parent::debug($message, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     * @param bool $isDebug
     * @return void
     */
    public function error($message, array $context = [], bool $isDebug = false): void
    {
        if ($this->scopeConfig->isSetFlag('payment/ecentric/general/debug')) {
            parent::error($message, $context);
        }
    }
}
