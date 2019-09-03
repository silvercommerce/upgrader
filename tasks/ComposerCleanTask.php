<?php

class ComposerCleanTask extends BuildTask
{
    protected $title = 'Commerce Composer Cleaner';
 
    protected $description = 'Removes unwanted modules from composer.json';
 
    protected $enabled = true;
 
    private static $commerce_modules = [
        "i-lateral/silverstripe-commerce",
        "i-lateral/silverstripe-orders",
        "i-lateral/silverstripe-commerce-customisableproduct",
        "i-lateral/silverstripe-commerce-bulkprice",
        "silverstripe/silverstripe-omnipay",
        "i-lateral/silverstripe-commerce-groupedproduct",
        "i-lateral/silverstripe-themes-kube-commerce"
    ];

    function run($request) 
    {
        $composer = file_get_contents(Controller::join_links(BASE_PATH,'composer.json'));
        $decode = json_decode($composer);

        $require = $decode->require;
        foreach ($require as $module => $version) {
            if (in_array($module, $this->config()->commerce_modules)) {
                $this->log('removing '.$module);
                unset($require->$module);      
            }
        }

        $encode = json_encode($decode, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents(Controller::join_links(BASE_PATH,'composer.json'), $encode);
        $this->log('Cleaned Commerce modules from composer.json');
        $this->log('You can now run `composer update`');
    }

    private function log($message, $break = true)
    {
        if (Director::is_cli()) {
            if ($break) {
                echo $message . "\n";
            } else {
                echo $message . "\r";
            }
        } else {
            echo $message . "<br/>";
        }
    }
}