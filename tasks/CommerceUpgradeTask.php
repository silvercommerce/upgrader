<?php

use SilverStripe\Dev\BuildTask;

class CommerceUpgradeTask extends BuildTask
{
    protected $title = 'Say Hi';
 
    protected $description = 'A class that says <strong>Hi</strong>';
 
    protected $enabled = true;
 
    function run($request) {
        echo "I'm trying to say hi...";
    }   
}