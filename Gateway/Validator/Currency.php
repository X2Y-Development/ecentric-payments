<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Gateway\Validator;

use Ecentric\Payment\Service\Config as EcentricConfig;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Currency extends AbstractValidator
{
    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param EcentricConfig $ecentricConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        private EcentricConfig $ecentricConfig,
        private StoreManagerInterface $storeManager
    ) {
        parent::__construct($resultFactory);
    }

    /**
     * @inheritDoc
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $currencies = $this->ecentricConfig->getGeneralGroupInfo(
            'currency',
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );

        return $this->createResult(in_array($validationSubject['currency'], explode(',', $currencies)));
    }
}
