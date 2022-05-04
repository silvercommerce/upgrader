<?php

namespace SilverCommerce\Upgrader\Tasks;

use SilverStripe\ORM\DB;
use SilverCommerce\TaxAdmin\Model\TaxRate;
use SilverCommerce\OrdersAdmin\Model\LineItem;
use SilverCommerce\CatalogueAdmin\Model\CatalogueProduct;

/**
 * Task to update old commerce orders into new
 * invoices
 */
class MigrateLineItemsTask extends CommerceUpgradeTask
{
    private static $run_during_dev_build = true;

    private static $segment = 'MigrateSS3LineItemsTask';

    protected $title = 'Migrate SS3 Commerce Line Items';
 
    protected $description = 'Migrate SS3 commerce Line Items to new SilverCommerce Structure';

    /**
     * Process the row data, if migrated return true,
     * else return false
     *
     * @param array $row
     *
     * @return bool
     */
    protected function processRow(array $row)
    {
        // Attempt to match classnames
        if (isset($row['ClassName'])
            && $row['ClassName'] == self::ITEMS_TABLE
        ) {
            $row['ClassName'] = LineItem::class;
        }

        // Migrate item price to new column
        if (isset($row['Price'])) {
            $row['BasePrice'] = $row['Price'];
        }

        // ProductClass did not exist in SS3, map to default
        // "Product"
        $row['ProductClass'] = "Product";

        // Attempt to match tax rate to existing rate
        if (isset($row['TaxRate'])
            && (int)$row['TaxRate'] > 0
        ) {
            // Find or make TaxRate object then set TaxRateID
            $rate = TaxRate::get()->find(
                'Rate',
                $row['TaxRate']
            );

            if (!empty($rate)) {
                $row['TaxRateID'] = $rate->ID;
            } else if (isset($row['StockID'])) {
                $product = CatalogueProduct::get()->find('StockID', $row['StockID']);

                if (!empty($product) && $product->TaxRateID) {
                    $row['TaxRateID'] = $product->TaxRateID;
                }
            }
        }

        // Apply changes to database
        $item = LineItem::get()->byID($row['ID']);

        if (empty($item)) {
            $item = LineItem::create();
        }

        $item
            ->update($row)
            ->write();

        return true;
    }

    public function run($request)
    {
        parent::processTable(self::ITEMS_TABLE, "Items");

        // Rename original table to obsolete
        DB::dont_require_table(self::ITEMS_TABLE);

        return;
    }
}