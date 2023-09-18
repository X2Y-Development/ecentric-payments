<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Cron;

use Ecentric\Payment\Service\Config as ServiceConfig;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class CancelOrder
{
    /**
     * @param CollectionFactory $orderCollectionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        private CollectionFactory $orderCollectionFactory,
        private OrderRepositoryInterface $orderRepository,
        private TimezoneInterface $timezone
    ) {
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $currentDate = $this->timezone->date(new \DateTime('now'));
        $date = $currentDate->sub(new \DateInterval('P1D'))->format('Y-m-d H:i:s');

        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->join(
            ['sales_order_payment' => $orderCollection->getTable('sales_order_payment')],
            'main_table.entity_id = sales_order_payment.parent_id',
            ['method']
        );
        $orderCollection->addFieldToFilter('status', ['in' => [Order::STATE_PENDING_PAYMENT]]);
        $orderCollection->addFieldToFilter('method', ['eq' => ServiceConfig::METHOD_CODE]);
        $orderCollection->addFieldToFilter('created_at', ['lteq' => $date]);

        foreach ($orderCollection as $order) {
            /** @var Order $order */
            $order->cancel();
            $this->orderRepository->save($order);
        }
    }
}
