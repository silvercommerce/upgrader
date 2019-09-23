# upgrader
Upgrade legacy silverstripe-commerce (SS3) sites to SilverCommerce

# !!! Note: This is the SS4 branch - you will need to start on the SS3 branch !!! 

## How it works

To use this module, you need to install it before running the silverstripe upgrade tool.
By doing so, this will swap out the old i-lateral/silverstripe-[module] commerce modules for the silvercommerce/[module] modules when the silverstripe upgrader is ran.

### Installing

#### See SS3 branch

### Usage
This module provides a `CommerceUpgradeTask` you can run in the final steps of a standard SS3 to SS4 upgrade. This will update all your old DB objects into the new types.

