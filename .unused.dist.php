
<?php

$projectPath = __DIR__.'/';

$scanDirectories = [
    $projectPath.'/src/',
    $projectPath.'/tests/',
];

return [
    // Required params
    'composerJsonPath' => $projectPath.'/composer.json',
    'vendorPath' => $projectPath.'/vendor/',
    'scanDirectories' => $scanDirectories,

    // Optional params
    'skipPackages' => [
        'phpmd/phpmd', // QA tool
        'vimeo/psalm', // QA tool
        'friendsofphp/php-cs-fixer', // QA tool
        'rskuipers/php-assumptions', // QA tool
        'ergebnis/composer-normalize', // QA tool
        'enlightn/security-checker', // QA tool
        'php-parallel-lint/php-parallel-lint', // QA tool
        'sebastian/phpcpd', // QA tool
        'ukko/phpredis-phpdoc', // Stubs
    ],
    'excludeDirectories' => [],
    'scanFiles' => [],
    'extensions' => ['*.php'],
    'requireDev' => true,
];
