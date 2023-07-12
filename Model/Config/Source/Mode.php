<?php
namespace Ecentric\Payment\Model\Config\Source;
 
class Mode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'sandbox', 'label' => __('Sandbox')],
            ['value' => 'production_1', 'label' => __('Production 1')],
            ['value' => 'production_2', 'label' => __('Production 2')]
        ];
    }
}