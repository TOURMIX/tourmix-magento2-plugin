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
            
            // Make this component globally accessible
            window.tourmixIframeComponent = this;

            // Subscribe to shipping method change
            quote.shippingMethod.subscribe(this.toggleIframeVisibility.bind(this));

            // Initial check when the component is loaded
            this.toggleIframeVisibility(quote.shippingMethod());

            // Listen for the message event from the iframe
            window.addEventListener('message', function (event) {
                if (event.origin === 'https://tourmix.delivery' && event.data.timewindow) {
                    timewindowData(event.data.timewindow); // Store the time window data
                    console.log('Received time window:', timewindowData());

                    // Store timewindow data but don't save shipping information yet
                    // This will be handled when user clicks Next button
                }
            }, false);

            // Listen for Next button clicks using DOM event delegation
            var self = this;
            $(document).on('click', '.button.action.continue.primary', function(e) {
                console.log('Next button clicked, checking for timewindow data');
                if (timewindowData()) {
                    console.log('Attaching timewindow data to shipping address');
                    
                    // Attach timewindow to shipping address
                    var shippingAddress = quote.shippingAddress();
                    if (shippingAddress) {
                        if (!shippingAddress.extensionAttributes) {
                            shippingAddress.extensionAttributes = {};
                        }
                        shippingAddress.extensionAttributes.timewindow = timewindowData();
                        quote.shippingAddress(shippingAddress);
                        console.log('Timewindow attached:', timewindowData());
                    }
                    
                    // Call setShippingInformationAction to save the data
                    setShippingInformationAction();
                }
            });

            // Also listen for any form submission in the shipping step
            $(document).on('submit', '#co-shipping-form', function(e) {
                console.log('Shipping form submitted, checking for timewindow data');
                if (timewindowData()) {
                    console.log('Attaching timewindow data to shipping address');
                    
                    // Attach timewindow to shipping address
                    var shippingAddress = quote.shippingAddress();
                    if (shippingAddress) {
                        if (!shippingAddress.extensionAttributes) {
                            shippingAddress.extensionAttributes = {};
                        }
                        shippingAddress.extensionAttributes.timewindow = timewindowData();
                        quote.shippingAddress(shippingAddress);
                        console.log('Timewindow attached:', timewindowData());
                    }
                    
                    // Call setShippingInformationAction to save the data
                    setShippingInformationAction();
                }
            });
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
