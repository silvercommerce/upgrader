<?php

namespace SilverCommerce\Upgrader\Tasks;

use SilverStripe\ORM\DB;
use SilverStripe\ORM\Queries\SQLUpdate;
use SilverCommerce\OrdersAdmin\Model\LineItemCustomisation;

/**
 * Task to update old commerce orders into new invoices
 */
class MigrateLineItemCustomisationsTask extends CommerceUpgradeTask
{
    private static $run_during_dev_build = true;

    private static $segment = 'MigrateLineItemCustomisationsTask';

    protected $title = 'Migrate Commerce Line Item Customisations';
 
    protected $description = 'Migrate SS3 commerce Line Item Customisations to new SilverCommerce Structure';

    protected function processRow(array $row)
    {
        $id = 0;
        $class = null;

        // Attempt to match classnames
        if (isset($row['ClassName'])
            && $row['ClassName'] == self::ITEM_CUSTOMISATIONS_TABLE
        ) {
            $row['ClassName'] = LineItemCustomisation::class;
            $class = LineItemCustomisation::class;
        }

        if (isset($row['ID'])) {
            $id = $row['ID'];
        }

        if (empty($class) || $id < 1) {
            return false;
        }

        $existing = LineItemCustomisation::get()
            ->byID($id);

        if ($existing) {
            // Manually insert classname (as it tends to be set to default)
            SQLUpdate::create('"' . $existing->baseTable() . '"')
                ->addWhere(['ID' => $id])
                ->assign('"ClassName"', $class)
                ->execute();

            $existing->update($row);
            $existing->write();
        }
        
        
        LineItemCustomisation::create()
            ->update($row)
            ->write();

        return true;
    }

    public function run($request)
    {
        parent::processTable(self::ITEM_CUSTOMISATIONS_TABLE, "ItemCustomisations");

        // Rename original table to obsolete
        DB::dont_require_table(self::ITEM_CUSTOMISATIONS_TABLE);

        return;
    }
}