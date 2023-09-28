<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Model;

use Magento\Framework\Model\AbstractModel;

class CustomerSession extends AbstractModel
{
    /**
     * Entity code
     */
    const ENTITY = 'ecentric_payment_customer_session';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'ecentric_payment_customer_session';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'ecentric_payment_customer_session';

    /**
     * {@inheritdoc}
     */
    public function _construct(): void
    {
        parent::_construct();
        $this->_init('Ecentric\Payment\Model\ResourceModel\CustomerSession');
    }

    /**
     * @param int|string $orderId
     *
     * @return CustomerSession
     */
    public function loadByOrderId(int|string $orderId): CustomerSession
    {
        return $this->load($orderId, 'order_id');
    }
}
