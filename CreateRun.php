<?php
/**
 * Created by PhpStorm.
 * User: joeward
 * Date: 29/08/2018
 * Time: 08:52
 */

use TestrailAPIRunCreate\GenerateTestRun;



if (is_file($autoload = getcwd() . '/vendor/autoload.php')) {
    require $autoload;
}

$projectname = getopt(null, ["name:"]);

if($projectname == null || $projectname['name'] == null){
    die('Name is either blank or not added as a param. Append --name=');
}

    $generate = new GenerateTestRun($projectname['name']);
    $generate->createNewRun();


