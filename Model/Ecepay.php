<?php

namespace Ecentric\Payment\Model;

class Ecepay extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = 'ecepay';

    protected $_isOffline = true;

	public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }
}