<?php

namespace SilverCommerce\Upgrader\Tasks;

use SilverStripe\ORM\DB;
use SilverStripe\ORM\Queries\SQLSelect;
use SilverCommerce\OrdersAdmin\Model\Invoice;
use SilverCommerce\OrdersAdmin\Model\Estimate;
use SilverCommerce\ShoppingCart\Model\ShoppingCart;

/**
 * Task to update old commerce orders into new invoices
 */
class MigrateOrdersTask extends CommerceUpgradeTask
{
    private static $run_during_dev_build = true;

    private static $segment = 'MigrateSS3OrdersTask';

    protected $title = 'Migrate SS3 Commerce Orders';
 
    protected $description = 'Migrate SS3 commerce Orders to new SilverCommerce Structure';

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
        $id = $row['ID'];

        // if no valid order billing details, then
        // skip migrating the order (to save data)
        if (empty($row['FirstName'])
            && empty($row['Surname'])
            && empty($row['Postcode'])
            && empty($row['Email'])
            && empty($row['PhoneNumber'])
        ) {
            return false;
        }

        $estimate = SQLSelect::create()
            ->setFrom('"' . self::ESTIMATE_TABLE . '"')
            ->setWhere(array('"Estimate"."ID"' => $id))
            ->execute()
            ->first();

        if (isset($estimate) && is_array($estimate)) {
            $data = array_merge($estimate, $row);
        } else {
            $data = $row;
        }

        $data['StartDate'] = $row['Created'];

        if (isset($row['OrderNumber'])) {
            $data['Number'] = $row['OrderNumber'];
            $data['Ref'] = $row['OrderNumber'];
        }

        if (isset($row['DeliveryFirstnames'])) {
            $data['DeliveryFirstName'] = $row['DeliveryFirstnames'];
        }

        // Apply changes to database
        $existing = Estimate::get()->byID($id);

        // Try to determine which classes map to legacy classes
        if ($data['ClassName'] == self::ESTIMATE_TABLE) {
            $data['ClassName'] = Estimate::class;
            $class = Estimate::class;
        } elseif ($data['ClassName'] == self::ESTIMATE_TABLE
            && isset($data['Cart']) && $data['Cart'] === '1'
        ) {
            $data['ClassName'] = ShoppingCart::class;
            $class = ShoppingCart::class;
        } else {
            $existing = Estimate::get()->byID($id);
            $row['ClassName'] = Invoice::class;
            $class = Invoice::class;
        }

        if ($existing) {
            $existing
                ->update($data)
                ->write();
        } else {
            $class::create($data)
                ->write();
        }

        return true;
    }

    public function run($request)
    {
        parent::processTable(self::ORDER_TABLE, "Orders");

        // Rename original table to obsolete
        DB::dont_require_table(self::ORDER_TABLE);

        return;
    }
}