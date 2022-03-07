<?php

require __DIR__.'/vendor/autoload.php';

$app = new AviationAcceleratorLogAnalysis\LogAnalysis();
$log_entries = $app->latest();




$response = (object)[
    "Log File" => $app->latestLogFilepath(true),
    "Unique IP Addresses" => $app::countUniqueIpAddresses($log_entries),
    "OS Versions" => $app::countOsVersions($log_entries)
];


print_r($log_entries);
print_r($response ?? "\nNo log entries found\n\n");

