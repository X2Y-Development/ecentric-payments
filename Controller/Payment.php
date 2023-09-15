<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Controller;

use Ecentric\Payment\Command\Webhook\ProcessOrder;
use Ecentric\Payment\Helper\Data as EcentricHelper;
use Ecentric\Payment\Logger\Logger as EcentricLogger;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\SerializerInterface;

abstract class Payment implements CsrfAwareActionInterface
{
    /**
     * @param RequestInterface $request
     * @param SerializerInterface $serializer
     * @param ResponseInterface $response
     * @param ProcessOrder $processOrder
     * @param RedirectInterface $redirect
     * @param EcentricHelper $ecentricHelper
     * @param EcentricLogger $ecentricLogger
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        protected RequestInterface $request,
        protected SerializerInterface $serializer,
        protected ResponseInterface $response,
        protected ProcessOrder $processOrder,
        protected RedirectInterface $redirect,
        protected EcentricHelper $ecentricHelper,
        protected EcentricLogger $ecentricLogger,
        protected ResultFactory $resultFactory
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
     * @param string $path
     * @return ResultInterface
     */
    public function redirect(string $path): ResultInterface
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath($path, ['_secure' => true]);
    }
}
