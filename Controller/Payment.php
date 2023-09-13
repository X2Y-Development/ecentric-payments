<?php

namespace Ecentric\Payment\Controller;

use Ecentric\Payment\Command\Webhook\ProcessOrder;
use Ecentric\Payment\Helper\Data as EcentricHelper;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
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
     */
    public function __construct(
        protected RequestInterface $request,
        protected SerializerInterface $serializer,
        protected ResponseInterface $response,
        protected ProcessOrder $processOrder,
        protected RedirectInterface $redirect,
        protected EcentricHelper $ecentricHelper
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
     * @param $path
     * @return ResponseInterface|null
     */
    public function redirect($path): ?ResponseInterface
    {
        $this->redirect->redirect($this->response, $path);

        return $this->response;
    }
}
