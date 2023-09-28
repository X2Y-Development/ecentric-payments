<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Controller\Wallet\Management;

use Ecentric\Payment\Logger\Logger as EcentricLogger;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index implements CsrfAwareActionInterface
{
    /**
     * @param PageFactory $resultPageFactory
     * @param EcentricLogger $ecentricLogger
     * @param RequestInterface $request
     */
    public function __construct(
        protected PageFactory $resultPageFactory,
        protected EcentricLogger $ecentricLogger,
        protected RequestInterface $request
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(): ResultInterface
    {
        $content = $this->request->getContent() ?? '';

        if (strlen($content) > 0) {
            $this->ecentricLogger->debug(__('Ecentric Wallet %1', $this->request->getContent()));
        }

        return $this->resultPageFactory->create();
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
}
