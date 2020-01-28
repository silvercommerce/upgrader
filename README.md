# upgrader
Upgrade legacy silverstripe-commerce (SS3) sites to SilverCommerce

## How it works

To use this module, you need to install it before running the silverstripe-upgrade tool.
By doing so, this will swap out the old i-lateral/silverstripe-[module] commerce modules for the silvercommerce/[module] modules when the silverstripe upgrader is ran.

### 1 - Intsalling
This module can be installed via composer:
```
composer require silvercommerce/upgrader
```
Installing this way may not work as composer sometimes refuses to pull down the correct version. For this reason I recommend adding the following linme to your composer.json manually.
```
"silvercommerce/upgrader": "ss3-dev"
```

### 2 - Usage
Once installed you needd to remove any/all of the followinng modules from your `composer.json`.
(it's likely you will have needed to remove many of these to install this module).

```
  i-lateral/silverstripe-commerce
  i-lateral/silverstripe-orders
  i-lateral/silverstripe-commerce-customisableproduct
  i-lateral/silverstripe-commerce-bulkprice
  silverstripe/silverstripe-omnipay
  i-lateral/silverstripe-commerce-groupedproduct
  i-lateral/silverstripe-themes-kube-commerce
```
then run `composer update` to update your composer.lock and to ensure you have the latest versions of all the modules. Followed by a `dev/build` to update the database to match.

### Upgrade

Now you should be able to run the silverstripe/upgrader method `upgrade-code recompose --write`, see (SilverStripe Docs)[https://docs.silverstripe.org/en/4/upgrading/upgrading_project/] for more information on upgrading.

This will update all your modules including this one - please see README.md of new version for further steps.

