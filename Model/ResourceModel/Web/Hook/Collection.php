<?php

namespace Ecentric\Payment\Model\ResourceModel\Web\Hook;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Ecentric\Payment\Model\Web\Hook as Model;
use Ecentric\Payment\Model\ResourceModel\Web\Hook as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct(): void
    {
        parent::_construct();
        $this->_init(Model::class, ResourceModel::class);
    }
}
