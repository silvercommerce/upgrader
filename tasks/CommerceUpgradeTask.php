<?php

use SilverStripe\Dev\BuildTask;
use SilverCommerce\OrdersAdmin\Model\Invoice;
use SilverCommerce\OrdersAdmin\Model\Estimate;
use SilverCommerce\OrdersAdmin\Model\LineItem;
use SilverStripe\ORM\Queries\SQLDelete;
use SilverStripe\ORM\Queries\SQLSelect;

/**
 * Task to update old commerce orders into new invoices
 */
class CommerceUpgradeTask extends BuildTask
{
    protected $title = 'MigrateCommerce';
 
    protected $description = 'Migrate SS3 Commerce DB to SilverCommerce';
 
    protected $enabled = true;

    function run($request) {

        $this->convertOrders();
        $this->convertItems();
    }  
    
    /**
     * Convert Orders/Estimates to Estimates/Invoices
     *
     * @return void
     */
    private function convertOrders() {
        $query = new SQLSelect();
        $query->setFrom('"Order"');

        $result = $query->execute();

        foreach ($result as $row) {
            $id = $row['ID'];
            $row = array_filter($row);
            $fetch = SQLSelect::create()
                ->setFrom('"Estimate"')
                ->setWhere(array('"Estimate"."ID"' => $id));
            $est = $fetch->execute()->first();
            $est = array_filter($est);
            $row = array_merge($est, $row);

            ### Update object data
            foreach ($row as $key => $value)
            {
            }

            ### Apply changes to database
            if ($row['ClassName'] == 'Estimate') {
                $existing = Estimate::get()->byID($row['ID']);
                // if an estimate already exists in the system we should update it instead of creating a new one
                $row['ClassName'] = Estimate::class;
                if ($existing) {
                    $existing->update($row);
                    $existing->write();
                } else {
                    $item = Estimate::create()->update($row);
                    $item->write();
                }
            } else {
                $existing = Estimate::get()->byID($row['ID']);

                $row['ClassName'] = Invoice::class;
                if ($existing) {
                    $existing->update($row);
                    $existing->write();
                } else {
                    $item = Invoice::create()->update($row);
                    $item->write();
                }
            }
        }
    }

    /**
     * convert OrderItems to LineItems & their Customisations
     *
     * @return void
     */
    private function convertItems()
    {
        $query = new SQLSelect();
        $query->setFrom('"OrderItem"');

        $result = $query->execute();

        foreach ($result as $row) {
            $id = $row['ID'];
            ### Update object data
            foreach ($row as $key => $value)
            {
            }

            ### Apply changes to database
            $existing = LineItem::get()->byID($row['ID']);
            $row['ClassName'] = LineItem::class;
            if ($existing) {
                $existing->update($row);
                $existing->write();
            } else {            
                $item = LineItem::create()->update($row);
                $item->write();
            }
        }
    }
}