<?php
namespace Ecentric\Payment\Block;
use Magento\Framework\View\Element\Template;
class Fail extends \Magento\Framework\View\Element\Template
{
    public function getParameters()
    {
		$params = $this->getRequest()->getParams();
        return $params;
    }
}
?>