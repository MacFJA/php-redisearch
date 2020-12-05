<?php

$projectPath = __DIR__ . '/';

$scanDirectories = [
    $projectPath . '/src/',
];

$scanFiles = [];
$excludeDirectories = [];
return [
    /**
     * Required params
     **/
    'composerJsonPath' => $projectPath . '/composer.json',
    'vendorPath' => $projectPath . '/vendor/',
    'scanDirectories' => $scanDirectories,

    /**
     * Optional params
     **/
    'skipPackages' => [
    ],
    'excludeDirectories' => $excludeDirectories,
    'scanFiles' => $scanFiles,
    'extensions' => ['*.php'],
    'requireDev' => false
];