# upgrader
Upgrade legacy silverstripe-commerce (SS3) sites to SilverCommerce

## How it works

To use this module, you need to install it before running the silverstripe upgrade tool.
By doing so, this will swap out the old i-lateral/silverstripe-[module] commerce modules for the silvercommerce/[module] modules when the silverstripe upgrader is ran.

### Intsalling
```
composer require silvercommerce/upgrader
```
### Usage
Once installed you needd to remove any/all of the followinng modules from your composer.json

```
  i-lateral/silverstripe-commerce
  i-lateral/silverstripe-orders
  i-lateral/silverstripe-commerce-customisableproduct
  i-lateral/silverstripe-commerce-bulkprice
  silverstripe/silverstripe-omnipay
  i-lateral/silverstripe-commerce-groupedproduct
  i-lateral/silverstripe-themes-kube-commerce
```
then run `composer update` to update your composer.lock and to ensure you have the latest versions of all the modules.

### Now the hard bit

now you need to work through all of the errors you're going to get: *This module is not yet finished*
