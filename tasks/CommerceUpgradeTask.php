<?php

use SilverCommerce\CatalogueAdmin\Model\CatalogueProduct;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\Queries\SQLDelete;
use SilverStripe\ORM\Queries\SQLSelect;
use SilverStripe\SiteConfig\SiteConfig;
use SilverCommerce\TaxAdmin\Model\TaxRate;
use SilverCommerce\OrdersAdmin\Model\Invoice;
use SilverCommerce\OrdersAdmin\Model\Estimate;
use SilverCommerce\OrdersAdmin\Model\LineItem;
use SilverCommerce\OrdersAdmin\Model\LineItemCustomisation;

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
        $this->convertTax();
        $this->convertItems();
        $this->convertCustomisations();
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
     * convert TaxRates tonew versions
     *
     * @return void
     */
    private function convertTax()
    {
        $rates = TaxRate::get();
        $config = SiteConfig::current_site_config();

        foreach ($rates as $rate) {
            $fetch = SQLSelect::create()
                ->setFrom('"TaxRate"')
                ->setWhere(array('"TaxRate"."ID"' => $rate->ID));
            $db = $fetch->execute()->first();
            $rate->SiteID = $config->ID;
            $rate->Rate = $db['Amount'];
            $rate->write();
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
            ### Update object data
            foreach ($row as $key => $value)
            {
                switch ($key) {
                    case "TaxRate":
                        // Find or make TaxRate object then set TaxRateID
                        $rate = TaxRate::get()->find('Rate', $value);
                        if ($rate) {
                            $row['TaxRateID'] = $rate->ID;
                        } else if (isset($row['StockID'])) {
                            $product = CatalogueProduct::get()->find('StockID', $row['StockID']);
                            if ($product && $product->TaxRateID) {
                                $row['TaxRateID'] = $product->ID;
                            }
                        }
                        break;

                }
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

        /**
     * convert OrderItems to LineItems & their Customisations
     *
     * @return void
     */
    private function convertCustomisations()
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