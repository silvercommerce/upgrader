# SilverStripe Commerce Upgrader

Tool to assist in upgrading legacy `silverstripe-commerce` (SS3) sites to SilverCommerce (SS4)

**!!! NOTE: This is the SS4 branch - you will need to start on the SS3 branch !!!**

## How it works

To use this module, you need to install it before running the silverstripe upgrade tool.

By doing so, this will swap out the old i-lateral/silverstripe-[module] commerce modules for the silvercommerce/[module] modules when the silverstripe upgrader is
run.

## Installing

This module is installed via composer, to get more info **please refer to the `SS3` branch**

### Usage

For the most part this module provides re-mappings needed for migrating an
`i-lateral/silverstripe-commerce` module to `SilverCommerce`, You should
be able to just run the SilverStripe `upgrade-code` tool as per usual.

This module also provides some specific upgrade tasks that will run to migrate
legacy data to the new structure. By default these tasks will run during `dev/build` and are responsible for migrating the following data:

1. `Order` (SS3) data will now be re-mapped to the new `Estimate`/`Invoice` structure.
2. `OrderItem` (SS3) data and `Customisation` data will be migrated to the new `LineItem` structure.
3. `TaxRate` (SS3) data will attempt to be migrated to the new `Zone` based category structure.
4. `Discount` (SS3) data will be migrated from the simplified system to the new modular system used in SilverCommerce.
5. `PostageArea` (SS3) data will be migrated from the table based postage table to SilverCommerce modular, `Zone` based postage data.

**NOTE** Tax Rate and Postage migrations are quite complex and the migration 
tasks will do their best to migrate data to the new format, but **YOU MUST FULLY
CHECK TAX AND POSTAGE DATA PRIOR TO PUTTING AN UPDATE LIVE**