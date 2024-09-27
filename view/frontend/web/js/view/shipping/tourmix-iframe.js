define([
    'uiComponent',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/set-shipping-information',
    'jquery'
], function (Component, ko, quote, setShippingInformationAction, $) {
    'use strict';

    var timewindowData = ko.observable(null); // This will hold the time window data

    return Component.extend({
        defaults: {
            template: 'Tourmix_Shipping/shipping/tourmix-iframe'
        },
        initialize: function () {
            this._super();
            this.isIframeVisible = ko.observable(false);

            // Subscribe to shipping method change
            quote.shippingMethod.subscribe(this.toggleIframeVisibility.bind(this));

            // Initial check when the component is loaded
            this.toggleIframeVisibility(quote.shippingMethod());

            // Listen for the message event from the iframe
            window.addEventListener('message', function (event) {
                if (event.origin === 'https://tourmix.delivery' && event.data.timewindow) {
                    timewindowData(event.data.timewindow); // Store the time window data
                    console.log('Received time window:', timewindowData());

                    // Save timewindow to quote customAttributes
                    if (quote.shippingAddress()) {
                        var shippingAddress = quote.shippingAddress();

                        if (!shippingAddress.extensionAttributes) {
                            shippingAddress.extensionAttributes = {};
                        }

                        shippingAddress.extensionAttributes.timewindow = event.data.timewindow;

                        // Save the shipping information
                        setShippingInformationAction();
                    }
                }
            }, false);
        },
        toggleIframeVisibility: function (selectedShippingMethod) {
            // Check if Tourmix Shipping is selected and show window time
            if (selectedShippingMethod
                && selectedShippingMethod['carrier_code'] === 'tourmix_shipping'
                && selectedShippingMethod['extension_attributes']['show_window_time']) {
                this.isIframeVisible(true);
            } else {
                this.isIframeVisible(false);
            }
        }
    });
});
