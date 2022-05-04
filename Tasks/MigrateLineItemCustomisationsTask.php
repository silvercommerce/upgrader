<?php

namespace SilverCommerce\Upgrader\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\Queries\SQLSelect;
use SilverCommerce\OrdersAdmin\Model\LineItemCustomisation;

/**
 * Task to update old commerce orders into new invoices
 */
class MigrateLineItemCustomisationsTask extends BuildTask
{
    private static $run_during_dev_build = true;

    private static $segment = 'MigrateLineItemCustomisationsTask';

    protected $title = 'Migrate Commerce Line Item Customisations';
 
    protected $description = 'Migrate SS3 commerce Line Item Customisations to new SilverCommerce Structure';

    function run($request)
    {
        $query = new SQLSelect();
        $query->setFrom('"OrderItemCustomisation"');
        $result = $query->execute();

        foreach ($result as $row) {
            $id = $row['ID'];
            ### Update object data
            foreach ($row as $key => $value)
            {
            }

            ### Apply changes to database
            $existing = LineItemCustomisation::get()->byID($row['ID']);
            $row['ClassName'] = LineItemCustomisation::class;
            if ($existing) {
                $existing->update($row);
                $existing->write();
            } else {            
                $item = LineItemCustomisation::create()->update($row);
                $item->write();
            }
        }
    }
}