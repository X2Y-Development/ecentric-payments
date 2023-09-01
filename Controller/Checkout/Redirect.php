<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Controller\Checkout;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Redirect implements HttpGetActionInterface
{
    /**
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        private PageFactory $resultPageFactory
    ) {
    }


    /**
     * @inheritDoc
     */
    public function execute(): ResultInterface
    {
        return $this->resultPageFactory->create();
    }
}
