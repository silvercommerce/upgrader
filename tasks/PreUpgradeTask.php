<?php

class PreUpgradeTask extends BuildTask
{
    protected $title = 'Commerce Pre-Upgrade Task';
 
    protected $description = 'Updates all Commerce DataObjects ready for upgrading';
 
    protected $enabled = true;
 
    function run($request) 
    {
        
    }
}