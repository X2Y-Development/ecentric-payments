<?php

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
