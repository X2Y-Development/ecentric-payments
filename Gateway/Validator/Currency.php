<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

declare(strict_types=1);

namespace Ecentric\Payment\Gateway\Validator;

use Ecentric\Payment\Helper\Data as EcentricHelper;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Currency extends AbstractValidator
{
    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param EcentricHelper $ecentricHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        private EcentricHelper $ecentricHelper,
        private StoreManagerInterface $storeManager
    ) {
        parent::__construct($resultFactory);
    }

    /**
     * @inheritDoc
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $currencies = $this->ecentricHelper->getGeneralGroupInfo(
            'currency',
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );

        return $this->createResult(in_array($validationSubject['currency'], explode(',', $currencies)));
    }
}
