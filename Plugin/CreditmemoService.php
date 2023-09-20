<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Plugin;

use Ecentric\Payment\Service\Config;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\CreditmemoService as MagentoCreditMemoService;

class CreditmemoService
{
    /**
     * @param MessageManagerInterface $messageManager
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        private MessageManagerInterface $messageManager,
        private OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * @param MagentoCreditMemoService $subject
     * @param CreditmemoInterface $result
     * @param CreditmemoInterface $creditmemo
     * @param bool $offlineRequested
     * @return CreditmemoInterface
     */
    public function afterRefund(
        MagentoCreditMemoService $subject,
        CreditmemoInterface $result,
        CreditmemoInterface $creditmemo,
        bool $offlineRequested = false
    ): CreditmemoInterface {
        if (!$result->getEntityId()) {
            return $result;
        }

        $order = $result->getOrder();
        $payment = $order->getPayment();
        $paymentMethod = $payment->getMethod();

        if ($paymentMethod === Config::METHOD_CODE && $order->getState() === Order::STATE_CLOSED) {
            $link = "<a href='https://portal.ecentricpaymentgateway.co.za/'>Ecentric Payments Merchant Portal</a>";
            $message = __("Please make sure to refund the order via %1", $link);
            $order->addCommentToStatusHistory($message);
            $this->orderRepository->save($order);

            $this->messageManager->addSuccess($message);
        }
        return $result;
    }
}
