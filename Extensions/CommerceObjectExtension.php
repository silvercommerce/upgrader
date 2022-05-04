<?php

namespace SilverCommerce\Upgrader\Extensions;

use LogicException;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;

class CommerceObjectExtension extends DataExtension
{

    /**
     * Create a generic way to load migration classes to the various SilverCommerce
     * models that need migrating
     */
    public function requireDefaultRecords()
    {
        $owner = $this->getOwner();
        $migration_task = Config::inst()->get(
            get_class($owner),
            'commerce_migration_class'
        );

        if (!class_exists($migration_task)) {
            throw new LogicException('Invalid migration task class: ' . $migration_task);
        }

        $run_migration = Config::inst()->get(
            $migration_task,
            'run_during_dev_build'
        );

        if ($run_migration) {
            $request = Injector::inst()->get(HTTPRequest::class);
            Injector::inst()->get($migration_task)->run($request);
        }
    }
}