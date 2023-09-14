<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Model\ResourceModel\Web;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Hook extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    public function _construct(): void
    {
        $this->_init('ecentric_payment_web_hooks', 'entity_id');
    }
}
