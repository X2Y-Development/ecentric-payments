<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Controller;

use Ecentric\Payment\Command\Webhook\ProcessOrder;
use Ecentric\Payment\Logger\Logger as EcentricLogger;
use Ecentric\Payment\Service\RegisterPayment;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Ecentric\Payment\Model\Response;
use Ecentric\Payment\Model\ResponseFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

abstract class Payment implements CsrfAwareActionInterface
{
    /**
     * @param RequestInterface $request
     * @param SerializerInterface $serializer
     * @param ResponseInterface $response
     * @param ProcessOrder $processOrder
     * @param RedirectInterface $redirect
     * @param EcentricLogger $ecentricLogger
     * @param RegisterPayment $registerPayment
     * @param ResultFactory $resultFactory
     * @param ResponseFactory $responseFactory
     * @param MessageManagerInterface $messageManager
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        protected RequestInterface $request,
        protected SerializerInterface $serializer,
        protected ResponseInterface $response,
        protected ProcessOrder $processOrder,
        protected RedirectInterface $redirect,
        protected EcentricLogger $ecentricLogger,
        protected RegisterPayment $registerPayment,
        protected ResultFactory $resultFactory,
        protected ResponseFactory $responseFactory,
        protected MessageManagerInterface $messageManager,
        protected OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * @param string $orderNumber
     * @return OrderInterface
     */
    protected function getOrder(string $orderNumber): OrderInterface
    {
        $pattern = '/\d+/';
        preg_match($pattern, $orderNumber, $matches);

        $orderId = $matches[0] ?? null;

        return $this->orderRepository->get($orderId);
    }

    /**
     * @param array $content
     * @return Response
     */
    protected function setResponseData(array $content): Response
    {
        $response = $this->responseFactory->create();
        $response->setData($content);

        return $response;
    }

    /**
     * @param string $path
     * @return ResultInterface
     */
    protected function redirect(string $path): ResultInterface
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath($path, ['_secure' => true]);
    }
}
