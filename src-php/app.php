<?php

use packager\cli\ConsoleApp;
use packager\ui\MainForm;
use php\gui\UXApplication;
use php\gui\UXForm;

global $app;

if($argv[count($argv) -1] == 'ui')
{
    UXApplication::launch(function(UXForm $form){
        $form = new MainForm($form);
        
        $form->show();
    });
    return;
}

$app = new ConsoleApp();
$app->main($argv);