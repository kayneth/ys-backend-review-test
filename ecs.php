<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $ecsConfig->import(__DIR__ . '/vendor/sylius-labs/coding-standard/ecs.php');
};
