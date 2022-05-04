<?php

namespace SilverCommerce\Upgrader\Tasks;

use SilverStripe\ORM\DB;
use SilverCommerce\Postage\Model\PriceBased;
use SilverCommerce\Postage\Model\WeightBased;
use SilverCommerce\TaxAdmin\Model\TaxCategory;
use SilverCommerce\Postage\Model\QuantityBased;
use SilverCommerce\GeoZones\Model\Zone;
use SilverCommerce\Postage\Model\SinglePostageRate;

class MigratePostageTask extends CommerceUpgradeTask
{
    private static $run_during_dev_build = true;

    private static $segment = 'MigrateSS3PostageTask';

    protected $title = 'Migrate SS3 Commerce Postage';
 
    protected $description = 'Migrate SS3 commerce postage to new SilverCommerce structure';
 
    protected $enabled = true;

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
        $postage = null;
        $tax_category = null;
        $name = null;
        $country = null;

        if (empty($row['Calculation']) && empty($row['Calculation'])) {
            return false;    
        }

        if (isset($row['Title'])) {
            $row['Name'] = $row['Title'];
            $name = $row['Title'];
        }

        // Try to configure zones as best as possible
        if (isset($row['Country'])) {
            $country = $row['Country'];
        }

        if (empty($name) || empty($country)) {
            return false;
        }

        switch ($row['Calculation']) {
            case 'Price':
                $classname = PriceBased::class;
                break;
            case 'Weight':
                $classname = WeightBased::class;
                break;
            case 'Items':
                $classname = QuantityBased::class;
                break;
        }

        if (!empty($row['Tax'])) {
            $tax_category = TaxCategory::get()->find(
                'Rates.Rate',
                $row['Tax']
            );

            if (!empty($tax_category)) {
                $row['TaxID'] = $tax_category->ID;
            }
        }

        // Try to find a relevent zone (or create one if not)
        $zone = Zone::get()->find(
            'Country:PartialMatch',
            $country
        );

        if (empty($zone)) {
            $zone = Zone::create([
                'Country' => json_encode([$country]),
                'AllRegions' => true,
                'Enabled' => true
            ]);
            $zone->write();
        }

        // Now try to find or create postage
        $postage = $classname::get()
            ->find('Name', $name);

        if (empty($postage)) {
            $postage = $classname::create();
        }

        unset($row['ClassName']);

        $postage
            ->update($row)
            ->write();

        // Finally try to map a postage rate based on the current area
        $rate = $postage
            ->Rates()
            ->filter([
                'Max' => $row['Unit'],
                'Price' => $row['Cost']
            ])->first();

        if (empty($rate)) {
            $rate = SinglePostageRate::create([
                'Max' => $row['Unit'],
                'Price' => $row['Cost']
            ])->write();

            $postage
                ->Rates()
                ->add($rate);
        }

        return true;
    }

    public function run($request)
    {
        parent::processTable(self::POSTAGE_TABLE, "Postage");

        // Flag alert that postage will still need manual
        // intervention
        $this->log(
            'Automatic postage migration complete, you will need to manually re-map zones'
        );

        // Rename original table to obsolete
        DB::dont_require_table(self::POSTAGE_TABLE);

        return;
    }
}