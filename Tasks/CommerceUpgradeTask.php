<?php

namespace SilverCommerce\Upgrader\Tasks;

use LogicException;
use SilverStripe\ORM\DB;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Control\Director;
use SilverStripe\ORM\Queries\SQLSelect;

/**
 * Task to update old commerce orders into new
 * invoices
 *
 */
abstract class CommerceUpgradeTask extends BuildTask
{
    const ORDER_TABLE = "Order";

    const ESTIMATE_TABLE = "Estimate";

    const ITEMS_TABLE = "OrderItem";

    const ITEM_CUSTOMISATIONS_TABLE = "OrderItemCustomisation";

    const TAX_RATE_TABLE = "TaxRate";

    const DISCOUNTS_TABLE = "Discount";

    const POSTAGE_TABLE = "PostageArea";

    const NOTIFICATION_TABLE = "OrderNotification";

    const CHUNK_SIZE = 200;

    protected function getTableList(): array
    {
        return DB::table_list();
    }

    protected function getBaseQuery($table)
    {
        return SQLSelect::create()->setFrom('"' . $table . '"');
    }

    protected function processRow(array $row)
    {
        throw new LogicException("You must implement your own version of ProcessRow");
    }

    protected function processTable(string $table_name, string $plural)
    {
        $tables = $this->getTableList();
        $chunk_size = self::CHUNK_SIZE;
        $curr_chunk = 0;
        $total_chunks = 1;
        $migrated = 0;
        $skipped = 0;
        $start_time = time();

        if (!in_array($table_name, $tables)) {
            return;
        }

        $count = $this
            ->getBaseQuery($table_name)
            ->count();

        if ($count > 0) {
            $this->log("- {$count} {$plural} to migrate.");

            // Round up the total chunks, so stragglers are caught
            $total_chunks = ceil(($count / $chunk_size));
        }

        $this->log(
            "- Migrated {$migrated}, Skipped {$skipped} of {$count}",
            true
        );

        /**
         * Break list into chunks to try and save memory
         */
        while ($curr_chunk < $total_chunks) {
            $chunked_list =  $this
                ->getBaseQuery($table_name)
                ->setLimit($chunk_size, $curr_chunk * $chunk_size)
                ->execute();

            foreach ($chunked_list as $row) {
                $result = $this->processRow($row);

                if ($result === true) {
                    $migrated++; 
                } else {
                    $skipped++;
                }
            }

            $chunked_time = time() - $start_time;

            $this->log(
                "- Migrated {$migrated}, Skipped {$skipped} of {$count} in {$chunked_time}s",
                true
            );

            $curr_chunk++;
        }

        // purge current var
        $chunked_list = null;
    }

    /**
     * Log a message to the terminal/browser
     * 
     * @param string $message   Message to log
     * @param bool   $linestart Set cursor to start of line (instead of return)
     * 
     * @return null
     */
    protected function log($message, $linestart = false)
    {
        if (Director::is_cli()) {
            $end = ($linestart) ? "\r" : "\n";
            print_r($message . $end);
        } else {
            print_r($message . "<br/>");
        }
    }
}