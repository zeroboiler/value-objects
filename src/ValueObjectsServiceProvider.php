<?php

/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects;

use Illuminate\Support\ServiceProvider;
use ZeroBoiler\ValueObjects\Console\Commands\ListValueObjectsCommand;
use ZeroBoiler\ValueObjects\Console\Commands\MakeValueObjectCommand;

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
