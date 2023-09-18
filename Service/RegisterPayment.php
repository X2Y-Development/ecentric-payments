<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Service;

use Ecentric\Payment\Model\Response;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\InvoiceService;

class RegisterPayment
{
    /**
     * @param EventManager $eventManager
     * @param OrderRepositoryInterface $orderRepository
     * @param Session $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     * @param InvoiceService $invoiceService
     */
    public function __construct(
        private EventManager $eventManager,
        private OrderRepositoryInterface $orderRepository,
        private Session $checkoutSession,
        private CartRepositoryInterface $quoteRepository,
        private InvoiceService $invoiceService
    ) {
    }

    /**
     * Process order for Ecentric payment
     *
     * @param Response $response
     * @return bool
     * @throws LocalizedException
     */
    public function processOrder(Response $response): bool
    {
        $order = $this->orderRepository->get($response->getOrderId());

        if (!($order instanceof OrderInterface)) {
            throw new LocalizedException(__('Order should be specified'));
        }

        if ($response->getTransactionStatus() === 'Failure') {
            $order->addCommentToStatusHistory(
                __('Transaction failure. Order will be canceled automatically by cron')
            );
            $this->eventManager->dispatch(
                'ecentric_payment_order_failed',
                ['order' => $order, 'content' => $response]
            );
            $this->orderRepository->save($order);

            $this->setLastDataToSession(
                (int)$order->getQuoteId(),
                (int)$order->getId(),
                $order->getIncrementId(),
                $order->getStatus(),
                $response->getWebhookRequestType()
            );

            throw new LocalizedException(__('Transaction failure, order id: %1', $order->getId()));
        }

        $payment = $order->getPayment();

        if (!($payment instanceof OrderPaymentInterface)) {
            throw new LocalizedException(__('Payment should be specified'));
        }

        try {
            $this->registerPayment($response, $order, $payment);
            $this->eventManager->dispatch(
                'ecentric_payment_order_succeed',
                ['order' => $order, 'content' => $response]
            );
            $this->orderRepository->save($order);
        } catch (Exception $e) {
            throw new LocalizedException(
                __('Error had happen while order processing. Exception: %1', $e->getMessage())
            );
        }

        return true;
    }

    /**
     * Restore last active quote based on checkout session
     *
     * @return bool True if quote restored successfully, false otherwise
     * @throws Exception
     */
    public function restoreQuote(): bool
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if ($order->getId()) {
            $quote = $this->quoteRepository->get($order->getQuoteId());
            if ($quote->getId()) {
                return $this->checkoutSession->restoreQuote();
            }
        }

        return false;
    }

    /**
     * Add comments to order, create invoices and transactions
     *
     * @param Response $response
     * @param OrderInterface $order
     * @param OrderPaymentInterface $payment
     * @return void
     */
    private function registerPayment(
        Response $response,
        OrderInterface $order,
        OrderPaymentInterface $payment
    ): void {
        if (!in_array($order->getState(), [Order::STATE_PENDING_PAYMENT, Order::STATE_NEW, Order::STATE_PROCESSING])) {
            return;
        }

        if ($response->getWebhookRequestType() === null) {
            $this->setLastDataToSession(
                (int)$order->getQuoteId(),
                (int)$order->getId(),
                $order->getIncrementId(),
                $order->getStatus(),
                $response->getWebhookRequestType()
            );

            return;
        }

        $payment->setData('transaction_id', $response->getTransactionId());
        $payment->setAdditionalInformation('ecentric_request', $response->getRequest());

        if ($response->getWebhookRequestType() === 'Capture') {
            $payment->registerCaptureNotification($response->getAmount(), true);
        }

        if ($response->getWebhookRequestType() === 'Authorize') {
            $payment->registerAuthorizationNotification($response->getAmount());
        }

//        $order->addCommentToStatusHistory(
//            __('Approved payment online in Ecentric.'),
//            false,
//            true
//        );
    }

    /**
     * Set last data to redirect to success page or cart page
     *
     * @param int $quoteId
     * @param int $orderId
     * @param string $orderIncrementId
     * @param string $orderStatus
     * @param string|null $requestType
     * @return void
     */
    private function setLastDataToSession(
        int $quoteId,
        int $orderId,
        string $orderIncrementId,
        string $orderStatus,
        ?string $requestType
    ): void {
        if ($requestType === null) {
            $this->checkoutSession->setLastQuoteId($quoteId);
            $this->checkoutSession->setLastSuccessQuoteId($quoteId);
            $this->checkoutSession->clearHelperData();
            $this->checkoutSession->setLastOrderId($orderId);
            $this->checkoutSession->setLastRealOrderId($orderIncrementId);
            $this->checkoutSession->setLastOrderStatus($orderStatus);
        }
    }
}
