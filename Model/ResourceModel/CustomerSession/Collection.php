<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Model\ResourceModel\CustomerSession;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Ecentric\Payment\Model\CustomerSession as Model;
use Ecentric\Payment\Model\ResourceModel\CustomerSession as ResourceModel;

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
