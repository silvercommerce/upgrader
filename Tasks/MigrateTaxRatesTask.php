<?php

namespace SilverCommerce\Upgrader\Tasks;

use SilverStripe\ORM\Queries\SQLSelect;
use SilverStripe\ORM\Queries\SQLUpdate;
use SilverCommerce\TaxAdmin\Model\TaxRate;

/**
 * Task to update old commerce orders into new invoices
 */
class MigrateTaxRatesTask extends CommerceUpgradeTask
{
    private static $run_during_dev_build = true;

    private static $segment = 'MigrateSS3TaxRatesTask';

    protected $title = 'Migrate SS3 Commerce Tax Rates';
 
    protected $description = 'Migrate SS3 commerce Tax Rates to new SilverCommerce Structure';

    function run($request)
    {
        $table = TaxRate::singleton()->baseTable();
        $count = SQLSelect::create()
            ->setFrom('"' . $table  . '"')
            ->count();
        $rates = SQLSelect::create()
            ->setFrom('"' . $table  . '"')
            ->execute();
        
        $i = 1;

        $this->log(
            "- Migrating {$count} Tax Rates",
            true
        );

        foreach ($rates as $rate) {
            SQLUpdate::create('"' . $table . '"')
                ->addWhere(['ID' => $rate['ID']])
                ->assign('"Rate"', $rate['Amount'])
                ->execute();

            $this->log(
                "- Migrated {$i} of {$count} Tax Rates",
                true
            );

            $i++;
        }
    }
}