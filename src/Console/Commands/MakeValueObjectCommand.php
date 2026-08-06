<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects\Console\Commands;

use Illuminate\Console\Command;

/**
 * Generate a custom ValueObject class scaffold.
 *
 * Usage:
 *   php artisan zeroboiler:value-object:make ProductPrice
 *   php artisan zeroboiler:value-object:make ProductPrice --namespace=App\\ValueObjects
 */
final class MakeValueObjectCommand extends Command
{
    protected $signature = 'zeroboiler:value-object:make {name : The value object class name} {--namespace= : Custom namespace (default: App\ValueObjects)}';

    protected $description = 'Generate a custom ValueObject class scaffold';

    public function handle(): int
    {
        $name = (string) $this->argument('name');
        $namespace = (string) ($this->option('namespace') ?? 'App\\ValueObjects');

        $className = $this->sanitizeClassName($name);
        $directory = str_replace('\\', '/', $namespace);
        $path = base_path($directory.'/'.$className.'.php');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        if (file_exists($path) && ! $this->confirm("File {$path} already exists. Overwrite?")) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        $stub = $this->getStub($className, $namespace);
        file_put_contents($path, $stub);

        $relative = str_replace(base_path().'/', '', $path);
        $this->info("Generated: {$relative}");

        return self::SUCCESS;
    }

    /**
     * Sanitize class name.
     */
    private function sanitizeClassName(string $name): string
    {
        // Remove non-alphanumeric and underscore characters
        $name = preg_replace('/[^a-zA-Z0-9_]/', '', $name);

        // Ensure it starts with uppercase
        return ucfirst((string) $name);
    }

    /**
     * Get the class stub template.
     */
    private function getStub(string $className, string $namespace): string
    {
        return <<<PHP
            <?php

            declare(strict_types=1);

            namespace {$namespace};

            use ZeroBoiler\ValueObjects\ValueObject;

            /**
             * {$className} value object.
             */
            final class {$className} extends ValueObject
            {
                // Define your properties here
                // Example:
                // public string \$property;
                // public int \$anotherProperty;

                /**
                 * Create a new {$className} instance.
                 *
                 * @param  mixed  \$property
                 */
                public function __construct(
                    // Add constructor parameters here
                ) {
                    // Validate input if needed
                    // \$this->validate([...], [...]);
                }

                public function toArray(): array
                {
                    return [
                        // Return array representation
                    ];
                }

                public function __toString(): string
                {
                    // Return string representation
                    return '';
                }
            }
            PHP;
    }
}
