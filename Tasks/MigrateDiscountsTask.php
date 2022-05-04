<?php

namespace SilverCommerce\Upgrader\Tasks;

use DateTime;
use SilverStripe\ORM\Queries\SQLUpdate;
use SilverCommerce\Discounts\Model\DiscountCode;
use SilverCommerce\Discounts\Model\FixedRateDiscount;
use SilverCommerce\Discounts\Model\PercentageDiscount;
use SilverCommerce\Discounts\Model\FreePostageDiscount;

class MigrateDiscountsTask extends CommerceUpgradeTask
{
    private static $run_during_dev_build = true;

    private static $segment = 'MigrateSS3DiscountsTask';

    protected $title = 'Migrate SS3 Commerce Discounts';
 
    protected $description = 'Migrate SS3 commerce Discounts to new SilverCommerce structure';
 
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
        $discount = null;

        if (empty($row['Type']) && empty($row['Amount'])) {
            return false;    
        }

        switch ($row['Type']) {
            case 'Percentage':
                $discount = PercentageDiscount::create();
                $classname = PercentageDiscount::class;
                break;
            case 'Fixed':
                $discount = FixedRateDiscount::create();
                $classname = FixedRateDiscount::class;
                break;
            case 'Free Shipping':
                $discount = FreePostageDiscount::create();
                $classname = FreePostageDiscount::class;
                break;
        }

        if (empty($row['Starts'])) {
            $starts = new DateTime($row['Created']);
            $row['Starts'] = $starts->format('Y-m-d');
        }

        if (!empty($discount)) {
            // Ensure we don't overwrite default classnames
            unset($row['ClassName']);

            $discount
                ->update($row)
                ->write();

            // Manually insert classname (as it tends to be set to default)
            SQLUpdate::create('"' . $discount->baseTable() . '"')
                ->addWhere(['ID' => $row['ID']])
                ->assign('"ClassName"', $classname)
                ->execute();

            // finally generate a code if needed
            if (!isset($row['Code']) || strlen($row['Code'] <= 0)) {
                return true;
            }

            $existing_code = DiscountCode::get()->find('Code', $row['Code']);
            
            if (empty($existing_code)) {
                DiscountCode::create()
                    ->setField('Code', $row['Code'])
                    ->setField('DiscountID', $discount->ID)
                    ->write();
            }

            return true;
        }

        return false;
    }

    public function run($request)
    {
        return parent::processTable(self::DISCOUNTS_TABLE, "Discounts");
    }
}