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
 *
 * @since 1.0.0
 */
final class ValueObjectsServiceProvider extends ServiceProvider
{
    /**
     * Get the services provided by the provider.
     *
     * @return list<string>
     */
    #[\Override]
    public function provides(): array
    {
        return [
            // ValueObjectsServiceProvider registers no singletons —
            // only console commands loaded in boot().
        ];
    }

    #[\Override]
    public function register(): void
    {
        // No bindings — ValueObjectsServiceProvider only loads console commands in boot().
    }

    #[\Override]
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
