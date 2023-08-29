<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */
declare(strict_types=1);

namespace Ecentric\Payment\Logger;

use DateTimeZone;
use Ecentric\Payment\Helper\Data as EcentricHelper;
use Monolog\Logger as MonologLogger;

class Logger extends MonologLogger
{
    /**
     * @param EcentricHelper $ecentricHelper
     * @param string $name
     * @param array $handlers
     * @param array $processors
     * @param DateTimeZone|null $timezone
     */
    public function __construct(
        private EcentricHelper $ecentricHelper,
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
     * @return void
     */
    public function debug($message, array $context = []): void
    {
        if ($this->ecentricHelper->getGeneralGroupInfo('debug')) {
            parent::debug($message, $context);
        }
    }

    /**
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error($message, array $context = []): void
    {
        if ($this->ecentricHelper->getGeneralGroupInfo('debug')) {
            parent::error($message, $context);
        }
    }
}
