<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Controller\Checkout;

use Ecentric\Payment\Model\ResourceModel\Web\Hook as ResourceHook;
use Ecentric\Payment\Model\Web\Hook;
use Ecentric\Payment\Model\Web\HookFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Redirect implements HttpGetActionInterface
{
    /**
     * @param HookFactory $hookFactory
     * @param ResourceHook $hooksResource
     * @param PageFactory $resultPageFactory
     * @param Session $checkoutSession
     */
    public function __construct(
        private HookFactory $hookFactory,
        private ResourceHook $hooksResource,
        private PageFactory $resultPageFactory,
        private Session $checkoutSession
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(): ResultInterface
    {
        $orderId = $this->checkoutSession->getData('last_order_id');
        /** @var Hook $hook */
        $hook = $this->hookFactory->create()->loadByOrderId($orderId);
        $hook->setOrderId($orderId);

        $this->hooksResource->save($hook);

        return $this->resultPageFactory->create();
    }
}
