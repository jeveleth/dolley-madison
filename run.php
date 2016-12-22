<?php
namespace Government;
require 'vendor/autoload.php';

$dm = new DolleyMadison();

if (isset($argv[1]) && $argv[1] == 'update') {
    echo "Doing an update\n";
    $dm->update();
} else {
    echo "Forking the repos for the first time\n";
    $results = $dm->execute();
}