# **Magento 2 Tourmix Shipping Integration**

Tourmix Shipping Integration is a custom Magento 2 module that enables integration with the Tourmix shipping service. This module provides dynamic shipping rates and shipping label generation via the Tourmix API.

**Features**
* Tourmix shipping method available during checkout.
* Real-time shipping rates fetched from the Tourmix API.
* Configurable shipping method settings in the Magento Admin.
* Supports shipping label generation via Tourmix API.
* API request and response logging.
* Compatible with Magento 2.4.x.

**Requirements**
* Magento: 2.4.x or later.
* PHP: 7.4 or higher.
* Tourmix API credentials (API Key, API Secret).

**Installation**

1. Download the Module
   Clone or download this repository, and place it in the app/code/Tourmix/Shipping directory of your Magento 2 installation:

`cd <magento_root>/app/code
`
`mkdir -p Tourmix/Shipping`

Copy the module files into the Tourmix/Shipping directory.

2. Enable the Module
   Run the following commands to enable the module:

`php bin/magento module:enable Tourmix_Shipping
php bin/magento setup:upgrade
php bin/magento cache:flush`
3. Deploy Static Content (for production mode)
   If your Magento store is in production mode, deploy the static content:


`php bin/magento setup:static-content:deploy
`
4. Compile Dependency Injection (if required)
   If DI compilation is required, run the following:


`php bin/magento setup:di:compile
`

Configuration
To configure the module in the Magento Admin:

1. Go to Stores > Configuration > Sales > Shipping Methods.
2. Select Tourmix Shipping.
3. Configure the following settings:
4. Enabled: Yes/No.
5. Title: Set the title shown to customers during checkout.
6. API Key: Enter your Tourmix API key.
7. API Secret: Enter your Tourmix API secret.
8. Allowed Countries: Define the countries where this method is available.
9. Default Rate: Set a default rate to display if the API is unavailable.

__NOTE_:Also need to set origin information because Tourmix API uses start location of store from where delivery start_

**Usage**

* The Tourmix shipping option will appear on the checkout page if the destination is eligible.
* The module communicates with the Tourmix API to get real-time shipping rates.
* Shipping labels can be generated from the order details page in the Magento admin panel.


**Uninstallation**
To uninstall the module:

Disable the module:


`php bin/magento module:disable Tourmix_Shipping
`
Remove the module files:


`rm -rf <magento_root>/app/code/Tourmix/Shipping
`
Run the upgrade script and flush the cache:


`php bin/magento setup:upgrade
`

`php bin/magento cache:flush
`