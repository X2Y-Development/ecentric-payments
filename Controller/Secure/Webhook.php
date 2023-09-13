<?php

namespace Ecentric\Payment\Controller\Secure;

use Ecentric\Payment\Controller\Payment as AbstractPayment;
use Magento\Framework\App\ResponseInterface;

class Webhook extends AbstractPayment
{
    /**
     * @return ResponseInterface
     */
    public function execute(): ResponseInterface
    {
        $content = $this->serializer->unserialize($this->request->getContent());

        if (strlen($content) > 0) {
            $this->processOrder->execute($content);
        }

        return $this->response->setHttpResponseCode(200);
    }
}
