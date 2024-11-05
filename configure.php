#!/usr/bin/env php
<?php

use Illuminate\Support\Str;

require_once 'vendor/autoload.php';

function ask(string $question, string $default = ''): string
{
    $answer = \readline($question.($default ? " ({$default})" : null).': ');

    if (!$answer) {
        return $default;
    }

    return $answer;
}

function run(string $command): string
{
    return \trim((string) \shell_exec($command));
}

function replace_in_file(string $file, array $replacements): void
{
    $contents = \file_get_contents($file);

    \file_put_contents(
        $file,
        \str_replace(
            \array_keys($replacements),
            \array_values($replacements),
            $contents,
        ),
    );
}

function remove_prefix(string $prefix, string $content): string
{
    if (\str_starts_with($content, $prefix)) {
        return \mb_substr($content, \mb_strlen($prefix));
    }

    return $content;
}

function determineSeparator(string $path): string
{
    return \str_replace('/', \DIRECTORY_SEPARATOR, $path);
}

$currentDirectory = \getcwd();
$folderName = \basename($currentDirectory);

$packageName = $argv[1];
$packageSlug = Str::slug($packageName);
$packageNamespace = 'Blade'.Str::studly($packageName);

replace_in_file('.github/ISSUE_TEMPLATE/config.yml', [
    'skeleton' => $packageSlug,
]);

replace_in_file('composer.json', [
    'Skeleton' => $packageNamespace,
    'skeleton' => $packageSlug,
]);

replace_in_file('README.md', [
    'package_title' => Str::studly($packageSlug),
    'skeleton' => $packageSlug,
]);

replace_in_file('config/skeleton.php', [
    'skeleton' => $packageSlug,
]);

replace_in_file('src/ServiceProvider.php', [
    'Skeleton' => $packageNamespace,
    'skeleton' => $packageSlug,
]);

replace_in_file('tests/TestCase.php', [
    'Skeleton' => $packageNamespace,
]);

\rename(determineSeparator('./config/skeleton.php'), determineSeparator('./config/blade-icons-'.$packageSlug.'.php'));

run('composer install');
\unlink(__FILE__);
