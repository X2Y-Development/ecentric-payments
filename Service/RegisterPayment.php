<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Service;

use Ecentric\Payment\Model\Response;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class RegisterPayment
{
    private const TRANSACTION_STATUS_FAILURE = 'Failure';
    private const TRANSACTION_TYPE_CAPTURE = 'Capture';

    /**
     * @param EventManager $eventManager
     * @param OrderRepositoryInterface $orderRepository
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        private EventManager $eventManager,
        private OrderRepositoryInterface $orderRepository,
        private CheckoutSession $checkoutSession,
        private CartRepositoryInterface $quoteRepository
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
        $order = $response->getOrder();

        if ($response->getTransactionStatus() === self::TRANSACTION_STATUS_FAILURE) {
            $order->addCommentToStatusHistory(
                __('Transaction failure. Order will be canceled automatically by cron')
            );
            $this->eventManager->dispatch(
                'ecentric_payment_order_failed',
                ['order' => $order, 'content' => $response]
            );
            $this->orderRepository->save($order);

            throw new LocalizedException(__('Transaction failure, order id: %1', $order->getId()));
        }

        $payment = $order->getPayment();

        if (!($payment instanceof OrderPaymentInterface)) {
            throw new LocalizedException(__('Payment should be specified'));
        }

        try {
            $this->registerPayment($response, $order, $payment, $response->getWebhookRequestType());
            $this->eventManager->dispatch(
                'ecentric_payment_order_success',
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
     * @param string|null $requestType
     * @return void
     */
    private function registerPayment(
        Response $response,
        OrderInterface $order,
        OrderPaymentInterface $payment,
        ?string $requestType
    ): void {
        if (!in_array($order->getState(), [Order::STATE_PENDING_PAYMENT, Order::STATE_NEW, Order::STATE_PROCESSING])) {
            return;
        }

        $isNullOrCapture = $requestType === self::TRANSACTION_TYPE_CAPTURE || $requestType === null;

        if ($isNullOrCapture && in_array($order->getState(), [Order::STATE_PENDING_PAYMENT, Order::STATE_NEW])) {
            $payment->setTransactionId($response->getTransactionId());
            $payment->setAdditionalInformation('ecentric_request', $response->getRequest());
            $payment->registerCaptureNotification($response->getAmount(), true);
        }

        if ($requestType !== null) {
            $order->addCommentToStatusHistory(
                __(
                    'Request %1 from Ecentric amount of %2. Transaction ID: "%3"',
                    $requestType,
                    $order->getBaseCurrency()->formatTxt($response->getAmount()),
                    $response->getTransactionId()
                ),
            );
        }
    }

    /**
     * Set last data to redirect to success page
     *
     * @param OrderInterface $order
     * @return void
     */
    public function setLastDataToSession(OrderInterface $order): void
    {
        $this->checkoutSession->setLastQuoteId((int) $order->getQuoteId());
        $this->checkoutSession->setLastSuccessQuoteId((int) $order->getQuoteId());
        $this->checkoutSession->setLastOrderId($order->getEntityId());
        $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->checkoutSession->setLastOrderStatus($order->getStatus());
    }
}
