<?php

namespace SilverCommerce\Upgrader\Tasks;

use SilverStripe\ORM\DB;
use SilverCommerce\OrdersAdmin\Model\Notification;

/**
 * Task to update old commerce orders into new
 * invoices
 */
class MigrateOrderNotificationsTask extends CommerceUpgradeTask
{
    private static $run_during_dev_build = true;

    private static $segment = 'MigrateSS3OrderNotificationsTask';

    protected $title = 'Migrate SS3 Order Notifications';
 
    protected $description = 'Migrate SS3 commerce Order Notifications to new SilverCommerce Structure';

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
        // Unset classname
        unset($row['ClassName']);

        // Apply changes to database
        $item = Notification::get()->byID($row['ID']);

        if (empty($item)) {
            $item = Notification::create();
        }

        $item
            ->update($row)
            ->write();

        return true;
    }

    public function run($request)
    {
        parent::processTable(self::NOTIFICATION_TABLE, "Notifications");

        // Rename original table to obsolete
        DB::dont_require_table(self::NOTIFICATION_TABLE);

        return;
    }
}
