<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Illuminate\Support\ServiceProvider;
use ZeroBoiler\ValueObjects\Console\Commands\ListValueObjectsCommand;
use ZeroBoiler\ValueObjects\Console\Commands\MakeValueObjectCommand;

/**
 * Laravel service provider for the ZeroBoiler Value Objects package.
 *
 * Registers console commands for scaffolding and listing value objects.
 */
final class ValueObjectsServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeValueObjectCommand::class,
                ListValueObjectsCommand::class,
            ]);
        }
    }
}
