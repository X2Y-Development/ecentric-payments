<?php
/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */

namespace Ecentric\Payment\Block\Adminhtml\System\Config\Fieldset;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\View\Helper\Js;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Payment extends Fieldset
{
    /**
     * @param Context $context
     * @param Session $authSession
     * @param Js $jsHelper
     * @param SecureHtmlRenderer $secureRenderer
     * @param PackageInfo $packageInfo
     * @param array $data
     */
    public function __construct(
        Context                      $context,
        Session                      $authSession,
        Js                           $jsHelper,
        protected SecureHtmlRenderer $secureRenderer,
        protected PackageInfo        $packageInfo,
        array                        $data = []
    )
    {
        parent::__construct($context, $authSession, $jsHelper, $data, $secureRenderer);
    }


    /**
     * Add custom css class
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getFrontendClass($element): string
    {
        return parent::_getFrontendClass($element) . ' with-button enabled';
    }

    /**
     * Return header title part of html for payment solution
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getHeaderTitleHtml($element): string
    {
        $html = '<div class="config-heading" >';

        $groupConfig = $element->getGroup();

        $htmlId = $element->getHtmlId();
        $html .= '<div class="button-container"><button type="button"' .
            ' class="button action-configure' .
            (empty($groupConfig['ecentric_ec_separate']) ? '' : ' ecentric-ec-separate') .
            '" id="' . $htmlId . '-head" >' .
            '<span class="state-closed">' . __(
                'Configure'
            ) . '</span><span class="state-opened">' . __(
                'Close'
            ) . '</span></button>';

        $html .= /* @noEscape */
            $this->secureRenderer->renderEventListenerAsTag(
                'onclick',
                "ecentricToggleSolution.call(this, '" . $htmlId . "', '" . $this->getUrl('adminhtml/*/state') .
                "');event.preventDefault();",
                'button#' . $htmlId . '-head'
            );

        $html .= '</div>';

        $html .= '<div class="heading"><strong>' . $element->getLegend() . '</strong>';
        if ($element->getComment()) {
            $html .= '<span class="heading-intro">' . $element->getComment() . '</span>';
        }
        $html .= '<div class="version">' . __('Module Version:') . ' <strong>'  . $this->packageInfo->getVersion('Ecentric_Payment') .  '</strong></div>';
        $html .= '<div class="config-alt"></div>';
        $html .= '</div></div>';

        return $html;
    }

    /**
     * Return header comment part of html for payment solution
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element): string
    {
        return '';
    }

    /**
     * Get collapsed state on-load
     *
     * @param AbstractElement $element
     * @return false
     */
    protected function _isCollapseState($element): bool
    {
        return false;
    }

    /**
     * Return extra Js.
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getExtraJs($element): string
    {
        $script = "require(['jquery', 'prototype'], function(jQuery){
            window.ecentricToggleSolution = function (id, url) {
                var doScroll = false;
                Fieldset.toggleCollapse(id, url);
                if ($(this).hasClassName(\"open\")) {
                    \$$(\".with-button button.button\").each(function(anotherButton) {
                        if (anotherButton != this && $(anotherButton).hasClassName(\"open\")) {
                            $(anotherButton).click();
                            doScroll = true;
                        }
                    }.bind(this));
                }
                if (doScroll) {
                    var pos = Element.cumulativeOffset($(this));
                    window.scrollTo(pos[0], pos[1] - 45);
                }
            }
        });";

        return $this->_jsHelper->getScript($script);
    }
}
