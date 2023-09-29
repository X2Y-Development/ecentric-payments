/**
 * @author     X2Y Development team <dev@x2y.io>
 * @copyright  2023 X2Y Development team
 */
define([
    'jquery',
    'ecentricSandboxJs',
    'ecentricLiveJs'
], function ($, ecentricSandboxJs, ecentricLiveJs) {
    $.widget('ecentric.customerWallet', {
        options: {
            mode: '',
            merchantId: '',
            userId: '',
            checksum: ''
        },

        /**
         * @private
         */
        _create: function () {
            let self = this;
            $('#ecentric_submit').bind('click', function () {
                    let ecentricJs;
                    let walletModel = {
                        MerchantID: self.options.merchantId,
                        UserID: self.options.userId,
                        Checksum: self.options.checksum
                    };

                    if (self.options.mode === 'sandbox') {
                        ecentricJs = ecentricSandboxJs;
                    } else {
                        ecentricJs = ecentricLiveJs
                    }

                    ecentricJs.wallet(walletModel)
                }
            )
        }
    });

    return $.ecentric.customerWallet;
});
