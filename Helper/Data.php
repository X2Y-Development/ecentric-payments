<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Helper;

use Ecentric\Payment\Logger\Logger as EcentricLogger;
use Ecentric\Payment\Model\Config\Source\Mode;
use Ecentric\Payment\Model\Web\Hook;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    public const METHOD_CODE = 'ecentric';
    public const XPATH_PATTERN = 'payment/%s/general/%s';
    public const XPATH_GENERAL = 'payment/ecentric/general/';
    public const XPATH_API = 'payment/ecentric/api/';
    public const PAYMENT_ADD_INFO_KEY = 'ecentric_payment_info';
    public const HPP_LIVE = 'https://sandbox.ecentric.co.za/HPP'; // @TODO: get correct LIVE url!
    public const HPP_TEST = 'https://sandbox.ecentric.co.za/HPP';

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param EventManager $eventManager
     * @param OrderRepositoryInterface $orderRepository
     * @param EcentricLogger $logger
     * @param Session $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     * @param InvoiceService $invoiceService
     * @param Transaction $transaction
     */
    public function __construct(
        Context $context,
        private StoreManagerInterface $storeManager,
        private EventManager $eventManager,
        private OrderRepositoryInterface $orderRepository,
        private EcentricLogger $logger,
        private Session $checkoutSession,
        private CartRepositoryInterface $quoteRepository,
        private InvoiceService $invoiceService,
        private Transaction $transaction
    ) {
        parent::__construct($context);
    }

    /**
     * Get general information
     *
     * @param string $path
     * @param string $scope
     * @param int|string|null $scopeCode
     * @return mixed
     */
    public function getGeneralGroupInfo(
        string $path,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        null|int|string $scopeCode = null
    ): mixed {
        return $this->scopeConfig->getValue(self::XPATH_GENERAL . $path, $scope, $scopeCode);
    }

    /**
     * Get api data
     *
     * @param string $path
     * @param string $scope
     * @return mixed
     */
    public function getApiGroupInfo(string $path, string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT): mixed
    {
        return $this->scopeConfig->getValue(self::XPATH_API . $path, $scope);
    }

    /**
     * @param string $scope
     * @return string
     */
    public function getMerchantId(string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT): string
    {
        if ($this->getApiMode() === 'sandbox') {
            return $this->getApiGroupInfo('merchant_guid_sandbox', $scope);
        }

        return $this->getApiGroupInfo('merchant_guid_live', $scope);
    }

    /**
     * @param string $scope
     * @return string
     */
    public function getMerchantKey(string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT): string
    {
        if ($this->getApiMode() === 'sandbox') {
            return $this->getApiGroupInfo('merchant_key_sandbox', $scope);
        }

        return $this->getApiGroupInfo('merchant_key_live', $scope);
    }

    /**
     * Check if credentials set and payment method enabled
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isActiveModule(): bool
    {
        return $this->getGeneralGroupInfo(
            'active',
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
            ) && $this->isActiveMode();
    }

    /**
     * @param string $scope
     * @return string
     */
    public function getApiMode(string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT): string
    {
        return (string)$this->getApiGroupInfo('mode', $scope);
    }

    /**
     * Get New Order Status
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getNewOrderStatus(): string
    {
        return (string)$this->getGeneralGroupInfo(
            'new_order_status',
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * @return bool
     */
    private function isActiveMode(): bool
    {
        return $this->getMerchantId() && $this->getMerchantKey();
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return ($this->getApiMode() == Mode::PRODUCTION) ? self::HPP_LIVE : self::HPP_TEST;
    }

    /**
     * @param Hook $hook
     *
     * @return bool
     */
    public function processOrder(Hook $hook): bool
    {
        $order = $this->orderRepository->get($hook->getOrderId());
        $payment = $order->getPayment();

        if ($hook->getTransactionStatus() === 'Success') {
            if ($order instanceof Order && $payment instanceof Payment) {
                try {
                    $payment->setData('transaction_id', $hook->getData('transaction_id'));
                    $payment->setAdditionalInformation('ecentric_request', $hook->getData('request'));
                    $payment->registerCaptureNotification($hook->getData('amount'), true);

                    $order->addCommentToStatusHistory(__('Approved payment online in Ecentric.'), false, true);

                    $this->orderRepository->save($order);

                    $this->eventManager->dispatch('ecentric_payment_order_succeed', ['result' => $hook]);
                } catch (Exception $e) {
                    $this->logger->debug(
                        __('Error had happen while order processing. Exception: %1', $e->getMessage())
                    );
                }

                return true;
            }
        } else {
            $this->logger->debug(__('Transaction failure, order id: %1', $order->getId()));
            $order->addCommentToStatusHistory(
                __('Transaction failure. Order will be canceled automatically by cron')
            );
            $this->orderRepository->save($order);
        }

        $this->eventManager->dispatch('ecentric_payment_order_failed', ['result' => $hook]);

        return false;
    }

    /**
     * Restore last active quote based on checkout session
     *
     * @return bool True if quote restored successfully, false otherwise
     * @throws Exception
     */
    public function restoreQuote(): bool
    {;
        $order = $this->checkoutSession->getLastRealOrder();
        if ($order->getId()) {
            $quote = $this->quoteRepository->get($order->getQuoteId());
            if ($quote->getId()) {
                return $this->checkoutSession->restoreQuote();
            }
        }
        return false;
    }
}
