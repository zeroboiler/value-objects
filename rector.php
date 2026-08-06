<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

/**
 * Shared Rector configuration for all ZeroBoiler packages.
 *
 * Copy this file to each package root as `rector.php` and adjust paths.
 */

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);

    $rectorConfig->skip([
        __DIR__.'/tests/Fixtures',
    ]);

    // PHP upgrades
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_85,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::INSTANCEOF,
        SetList::TYPE_DECLARATION,
        SetList::PRIVATIZATION,
    ]);

    // Note: Laravel, PHPUnit, and Doctrine Rector extensions are not installed.
    // Add rector/rector-laravel, rector/rector-phpunit, and rector/rector-doctrine
    // to require-dev if those sets are needed.

    // Auto-import
    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);

    // Parallel
    $rectorConfig->parallel();
};
