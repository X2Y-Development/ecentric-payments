# Ecentric Payment magento 2 module

This magento 2 module adds Ecentric Payment method to your magento 2 store.

Installation guide:
-

### Installation by copying the code

After receiving the `ecentric-payment-1.x.x.zip` module package from Ecentric, follow the steps below to install the module.

* Unzip `ecentric-payment-1.x.x.zip`
* Copy the contents of `ecentric-payment-1.x.x/` directory to your server under `app/code/Ecentric/Payment/`
* Next, SSH into your Magento server, go to the webroot directory of your Magento installation and run:

```sh
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
```

After all commands ran successfully, congratulations, you've just installed Ecentric Payment on your Magento store.

### Installation via composer

SSH into your Magento server, go to the webroot directory of your Magento installation and run:

```sh
composer config repositories.ecentric vcs https://github.com/X2Y-Development/ecentric-payments
```

```sh
composer req ecentric/module-payment
```


Configuration guide:
-

To configure and enable the Ecentric Payment module, follow these steps:
1. Go to Stores > Configuration > Sales > Payment Methods
2. Click Configure Ecentric and set Enable Solution to Yes
3. Next, expand the API section of the Ecentric configuration.
4. Set module operation mode: Sandbox or Production
5. Input your Merchant GUID, Merchant Key based on the selected operation mode
6. Click Save Config.