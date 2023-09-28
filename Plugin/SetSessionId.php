<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManager;

class SetSessionId
{
    /**
     * @param RequestInterface $request
     */
    public function __construct(
        private RequestInterface $request
    ) {
    }

    /**
     * @param SessionManager $subject
     * @return void
     */
    public function beforeStart(SessionManager $subject): void
    {
        if ($this->request->isPost() && $this->request->getPathInfo() == '/ecentric/wallet_management/') {
            $_COOKIE['PHPSESSID'] = $this->request->getCookie('PHPSESSID');
        }
    }
}
