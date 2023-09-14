<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Model\Web;

use Magento\Framework\Model\AbstractModel;

class Hook extends AbstractModel
{
    /**
     * Entity code
     */
    const ENTITY = 'ecentric_payment_web_hooks';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'ecentric_payment_web_hooks';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'ecentric_payment_web_hooks';

    /**
     * {@inheritdoc}
     */
    public function _construct(): void
    {
        parent::_construct();
        $this->_init('Ecentric\Payment\Model\ResourceModel\Web\Hook');
    }

    /**
     * @param int|string $orderId
     *
     * @return Hook
     */
    public function loadByOrderId(int|string $orderId): Hook
    {
        return $this->load($orderId, 'order_id');
    }
}
