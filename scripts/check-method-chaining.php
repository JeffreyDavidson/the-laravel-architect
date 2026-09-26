<?php

declare(strict_types=1);

/*
 * Method chains must put each call on its own line. A line fails only when an
 * object operator continues a chain whose previous link is a method call on
 * the same line, such as `$query->where()->first()`. Separate accesses such as
 * `$this->save($model->id)`, enum `->value`, or property chains are allowed.
 * Merged migrations are never edited, so they are not checked.
 */

$root = dirname(__DIR__);
$paths = array_slice($argv, 1);
$ignoredDirectories = ['.git', 'node_modules', 'storage', 'vendor'];
$ignoredPrefixes = ['database/migrations/'];

if ($paths === []) {
    $paths = ['.'];
}

$files = [];

foreach ($paths as $path) {
    $absolutePath = str_starts_with($path, '/') ? $path : $root.'/'.ltrim($path, '/');

    if (is_file($absolutePath)) {
        $files[] = $absolutePath;

        continue;
    }

    if (! is_dir($absolutePath)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($absolutePath, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
}

/**
 * Line numbers where an object operator continues a method call from the same line.
 *
 * @return list<int>
 */
function chainedCallLines(string $source): array
{
    $objectOperators = [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR];
    $ignored = [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT];
    $significant = [];
    $lines = [];
    $stringDepth = 0;

    foreach (token_get_all($source) as $token) {
        $text = is_array($token) ? $token[1] : $token;
        $id = is_array($token) ? $token[0] : null;

        if ($text === '"' || $id === T_START_HEREDOC || $id === T_END_HEREDOC) {
            $stringDepth = $id === T_END_HEREDOC || ($text === '"' && $stringDepth > 0) ? 0 : 1;
        }

        if ($id !== null && in_array($id, $ignored, true)) {
            continue;
        }

        if ($stringDepth === 0 && $id !== null && in_array($id, $objectOperators, true)) {
            $line = $token[2];
            $previousLink = previousLinkOperator($significant);

            if ($previousLink !== null && $previousLink[2] === $line) {
                $lines[$line] = $line;
            }
        }

        $significant[] = $token;
    }

    return array_values($lines);
}

/**
 * When the tokens end with `->name(...)`, return that `->` token; otherwise null.
 *
 * @param  list<array{0: int, 1: string, 2: int}|string>  $significant
 * @return array{0: int, 1: string, 2: int}|null
 */
function previousLinkOperator(array $significant): ?array
{
    $index = count($significant) - 1;

    if ($index < 0 || $significant[$index] !== ')') {
        return null;
    }

    $depth = 0;

    for (; $index >= 0; $index--) {
        $text = is_array($significant[$index]) ? $significant[$index][1] : $significant[$index];

        if ($text === ')') {
            $depth++;
        } elseif ($text === '(') {
            $depth--;
        }

        if ($depth === 0) {
            break;
        }
    }

    $name = $significant[$index - 1] ?? null;
    $operator = $significant[$index - 2] ?? null;

    if (! is_array($name) || $name[0] !== T_STRING || ! is_array($operator)) {
        return null;
    }

    return in_array($operator[0], [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR], true) ? $operator : null;
}

$violations = [];

foreach (array_unique($files) as $file) {
    $file = realpath($file) ?: $file;
    $relativePath = str_starts_with($file, $root.'/') ? substr($file, strlen($root) + 1) : $file;
    $segments = explode(DIRECTORY_SEPARATOR, $relativePath);

    if (array_intersect($segments, $ignoredDirectories) !== []) {
        continue;
    }

    foreach ($ignoredPrefixes as $prefix) {
        if (str_starts_with($relativePath, $prefix)) {
            continue 2;
        }
    }

    foreach (chainedCallLines((string) file_get_contents($file)) as $line) {
        $violations[] = sprintf('%s:%d chains more than one call on one line.', $relativePath, $line);
    }
}

if ($violations !== []) {
    fwrite(STDERR, "Method chains must put each call on its own line:\n".implode("\n", $violations)."\n");
    exit(1);
}

echo "Method-chain formatting passed.\n";
