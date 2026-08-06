<?php

/**
 * This file is part of ZeroBoiler, licensed under the MIT license.
 */

declare(strict_types=1);

namespace ZeroBoiler\ValueObjects\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ZeroBoiler\ValueObjects\ValueObject;

/**
 * List all ValueObject classes in the app directory.
 *
 * Usage:
 *   php artisan zeroboiler:value-object:list
 *   php artisan zeroboiler:value-object:list --path=app/ValueObjects
 */
final class ListValueObjectsCommand extends Command
{
    protected $signature = 'zeroboiler:value-object:list {--path=app/ValueObjects : Path to search for ValueObjects}';

    protected $description = 'List all ValueObject classes in the app directory';

    public function handle(): int
    {
        $path = (string) $this->option('path');
        $basePath = base_path($path);

        if (! is_dir($basePath)) {
            $this->warn("Directory {$path} does not exist.");
            $this->info('You can create one with:');
            $this->line("  mkdir -p {$path}");

            return self::SUCCESS;
        }

        $files = File::allFiles($basePath);
        $classes = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = $file->getRelativePathname();
            $className = str_replace(['/', '.php'], ['\\', ''], $relativePath);

            if (! class_exists($className)) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($className);
                if (! $reflection->isSubclassOf(ValueObject::class)) {
                    continue;
                }
                if ($reflection->isAbstract()) {
                    continue;
                }

                $classes[] = [
                    'class' => $className,
                    'file' => $relativePath,
                ];
            } catch (\ReflectionException) {
                continue;
            }
        }

        if ($classes === []) {
            $this->info('No ValueObject classes found in '.$path);

            return self::SUCCESS;
        }

        $this->info('Found '.count($classes).' ValueObject class(es):');
        $this->newLine();

        foreach ($classes as $class) {
            $this->line("  <fg=cyan>{$class['class']}</fg=cyan>");
            $this->line("    <fg=gray>{$class['file']}</fg=gray>");
            $this->newLine();
        }

        return self::SUCCESS;
    }
}
