<?php

declare(strict_types=1);

namespace KayStrobach\Themes\Migration;

/**
 * Converts <INCLUDE_TYPOSCRIPT: ...> to @import (TYPO3 v14 drops the old syntax silently).
 *
 * @import only loads files ending with .typoscript (TSconfig: also .tsconfig), so included files with other endings
 * are renamed, keeping setup, constants and TSconfig apart (Library/ often holds *.setupts and *.constantsts):
 *
 *   *.setupts      -> *.setup.typoscript
 *   *.constantsts  -> *.constants.typoscript
 *   *.pagets       -> *.tsconfig
 *   *.txt, *.ts, *.t3s, *.t3c -> *.typoscript
 *
 * The entry points TYPO3 and EXT:themes load by name (setup, constants and tsconfig with ending .txt/.ts) are renamed
 * as well: static templates of TYPO3 v14 only read setup.typoscript and constants.typoscript.
 *
 * Deterministic and idempotent: the result only depends on the files, a second run changes nothing. The order of the
 * included files is the one of TYPO3 12 (scandir, files before sub directories). A DIR: include becomes one wildcard
 * import per directory if that loads exactly the same files in the same order, otherwise one import per file.
 * condition="..." becomes a condition block.
 *
 * Only files below the given extension paths are changed. Includes that can not be converted (target outside of them
 * with an ending @import does not load, missing files) stay as they are and are reported.
 */
final class IncludeTypoScriptConverter
{
    private const SUFFIX_MAP = [
        'setupts' => 'setup.typoscript',
        'constantsts' => 'constants.typoscript',
        'pagets' => 'tsconfig',
        'txt' => 'typoscript',
        'ts' => 'typoscript',
        't3s' => 'typoscript',
        't3c' => 'typoscript',
    ];

    /** Endings @import loads */
    private const IMPORTABLE = ['typoscript', 'tsconfig'];

    /** Files that may contain TypoScript or TSconfig */
    private const SCAN_SUFFIXES = ['typoscript', 'tsconfig', 'txt', 'ts', 'setupts', 'constantsts', 'pagets', 't3s', 't3c'];

    private const SKIP_DIRECTORIES = ['.git', '.Build', 'node_modules', 'vendor', 'Tests', 'Public'];

    private const INCLUDE_PATTERN = '/^(?<indent>[ \t]*)<INCLUDE_TYPOSCRIPT:(?<attributes>.*)>[ \t]*$/';

    /** @var array<string, string> extension key => absolute path with trailing slash */
    private array $extensionPaths = [];

    /** @var array<string, string> absolute path => extension key, of the extensions to convert */
    private array $convertRoots = [];

    private string $publicPath = '';

    /** @var array<string, string> absolute old file name => absolute new file name */
    private array $renames = [];

    /** @var list<string> */
    private array $warnings = [];

    /**
     * @param array<string, string> $extensionPaths all available extensions: key => absolute path
     * @param list<string> $extensionsToConvert keys of the extensions whose files are changed
     */
    public function plan(array $extensionPaths, array $extensionsToConvert, string $publicPath = ''): ConversionPlan
    {
        $this->extensionPaths = array_map(static fn (string $path): string => rtrim($path, '/') . '/', $extensionPaths);
        $this->convertRoots = [];
        foreach ($extensionsToConvert as $key) {
            if (!isset($this->extensionPaths[$key])) {
                throw new \InvalidArgumentException(sprintf('Unknown extension "%s"', $key), 1764150001);
            }
            $this->convertRoots[$this->extensionPaths[$key]] = $key;
        }
        ksort($this->convertRoots);
        $this->publicPath = $publicPath !== '' ? rtrim($publicPath, '/') . '/' : '';
        $this->renames = [];
        $this->warnings = [];

        $files = [];
        foreach (array_keys($this->convertRoots) as $root) {
            array_push($files, ...$this->scan($root));
        }
        sort($files);

        // 1. entry points and include targets with endings @import does not load
        foreach ($files as $file) {
            if ($this->isEntryPoint($file)) {
                $this->rename($file);
            }
        }
        $includes = [];
        foreach ($files as $file) {
            foreach ($this->includeLines($file) as $lineNumber => $include) {
                $includes[$file][$lineNumber] = $include;
                foreach ($include['targets'] ?? [] as $target) {
                    $this->rename($target);
                }
            }
        }

        // 2. include lines -> @import, references to renamed files
        $contents = [];
        foreach ($files as $file) {
            $content = (string)file_get_contents($file);
            $lines = explode("\n", $content);
            foreach ($includes[$file] ?? [] as $lineNumber => $include) {
                $replacement = $this->convert($include, $file, $lineNumber + 1);
                if ($replacement !== null) {
                    $lines[$lineNumber] = $replacement;
                }
            }
            $new = $this->rewriteReferences(implode("\n", $lines));
            if ($new !== $content) {
                $contents[$this->renames[$file] ?? $file] = $new;
            }
        }
        foreach (array_keys($this->convertRoots) as $root) {
            foreach ($this->scanReferenceFiles($root) as $file) {
                $content = (string)file_get_contents($file);
                $new = $this->rewriteReferences($content);
                if ($new !== $content) {
                    $contents[$file] = $new;
                }
            }
        }
        ksort($contents);
        ksort($this->renames);
        return new ConversionPlan($this->renames, $contents, $this->warnings);
    }

    public function apply(ConversionPlan $plan): void
    {
        foreach ($plan->renames as $old => $new) {
            if (!rename($old, $new)) {
                throw new \RuntimeException('Could not rename ' . $old, 1764150002);
            }
        }
        foreach ($plan->contents as $file => $content) {
            if (file_put_contents($file, $content) === false) {
                throw new \RuntimeException('Could not write ' . $file, 1764150003);
            }
        }
    }

    /**
     * @return list<string> absolute file names that may contain TypoScript
     */
    private function scan(string $directory): array
    {
        $files = [];
        foreach ($this->sortedEntries($directory) as $entry) {
            $path = $directory . $entry;
            if (is_dir($path)) {
                if (!in_array($entry, self::SKIP_DIRECTORIES, true)) {
                    array_push($files, ...$this->scan($path . '/'));
                }
            } elseif (in_array($this->suffix($entry), self::SCAN_SUFFIXES, true)) {
                $files[] = $path;
            }
        }
        return $files;
    }

    /**
     * PHP and YAML files may reference renamed files (EXT:my_theme/Configuration/TypoScript/setup.txt)
     *
     * @return list<string>
     */
    private function scanReferenceFiles(string $directory): array
    {
        $files = [];
        foreach ($this->sortedEntries($directory) as $entry) {
            $path = $directory . $entry;
            if (is_dir($path)) {
                if (!in_array($entry, self::SKIP_DIRECTORIES, true)) {
                    array_push($files, ...$this->scanReferenceFiles($path . '/'));
                }
            } elseif (in_array($this->suffix($entry), ['php', 'yaml', 'yml', 'html'], true)) {
                $files[] = $path;
            }
        }
        return $files;
    }

    /**
     * @return list<string>
     */
    private function sortedEntries(string $directory): array
    {
        // scandir() sorts like TYPO3 does when it reads a directory
        return array_values(array_diff(scandir($directory) ?: [], ['.', '..']));
    }

    private function isEntryPoint(string $file): bool
    {
        return in_array(basename($file), ['setup.txt', 'constants.txt', 'tsconfig.txt', 'setup.ts', 'constants.ts', 'tsconfig.ts'], true);
    }

    /**
     * Plans the rename of a file with an ending @import does not load, if it belongs to an extension to convert.
     */
    private function rename(string $file): void
    {
        if (isset($this->renames[$file]) || $this->convertRootOf($file) === null) {
            return;
        }
        $new = $this->renamedFileName($file);
        if ($new === $file) {
            return;
        }
        if (file_exists($new) || in_array($new, $this->renames, true)) {
            $this->warnings[] = sprintf('%s: not renamed, %s exists already', $this->display($file), basename($new));
            return;
        }
        $this->renames[$file] = $new;
    }

    private function renamedFileName(string $file): string
    {
        $suffix = $this->suffix($file);
        if (!isset(self::SUFFIX_MAP[$suffix])) {
            return $file;
        }
        return substr($file, 0, -strlen($suffix)) . self::SUFFIX_MAP[$suffix];
    }

    /**
     * @return array<int, array{indent: string, source: string, condition: string, extensions: list<string>, targets: list<string>|null, raw: string}>
     *     line index => include; targets null: target not found
     */
    private function includeLines(string $file): array
    {
        $includes = [];
        foreach (explode("\n", (string)file_get_contents($file)) as $index => $line) {
            if (!preg_match(self::INCLUDE_PATTERN, rtrim($line, "\r"), $match)) {
                continue;
            }
            $attributes = $match['attributes'];
            $source = preg_match('/source\s*=\s*"([^"]*)"/', $attributes, $m) ? trim($m[1]) : '';
            $condition = '';
            if (preg_match('/condition\s*=\s*"((?:\\\\\\\\|\\\\"|[^"])*)"/', $attributes, $m)) {
                $condition = stripslashes(trim($m[1]));
                if (str_starts_with($condition, '[') && str_ends_with($condition, ']')) {
                    $condition = substr($condition, 1, -1);
                }
            }
            $extensions = preg_match('/extensions\s*=\s*"([^"]*)"/', $attributes, $m)
                ? array_values(array_filter(array_map('trim', explode(',', $m[1])), static fn (string $e): bool => $e !== ''))
                : [];
            $include = [
                'indent' => $match['indent'],
                'source' => $source,
                'condition' => $condition,
                'extensions' => $extensions,
                'targets' => $this->targets($source, $extensions, $file),
                'raw' => $line,
            ];
            $includes[$index] = $include;
        }
        return $includes;
    }

    /**
     * Files the include loads, in the order of TYPO3 12
     *
     * @param list<string> $extensions
     * @return list<string>|null
     */
    private function targets(string $source, array $extensions, string $includingFile): ?array
    {
        if (str_starts_with($source, 'FILE:')) {
            $file = $this->resolve(substr($source, 5), $includingFile);
            if ($file === null) {
                return null;
            }
            // a file that only exists with the ending @import loads (page.css.setupts -> page.css.typoscript)
            foreach ([$file, $this->renamedFileName($file), $this->withoutSuffix($file) . '.typoscript'] as $candidate) {
                if (is_file($candidate)) {
                    return [$candidate];
                }
            }
            return null;
        }
        if (str_starts_with($source, 'DIR:')) {
            $directory = $this->resolve(substr($source, 4), $includingFile);
            if ($directory === null) {
                return null;
            }
            // a directory that does not exist (yet) loads nothing, with @import as well
            return is_dir($directory) ? $this->directoryTargets(rtrim($directory, '/') . '/', $extensions) : [];
        }
        return null;
    }

    /**
     * @param list<string> $extensions
     * @return list<string>
     */
    private function directoryTargets(string $directory, array $extensions): array
    {
        $files = [];
        $subDirectories = [];
        foreach ($this->sortedEntries($directory) as $entry) {
            if (is_dir($directory . $entry)) {
                $subDirectories[] = $entry;
                continue;
            }
            if ($extensions === [] || array_filter($extensions, static fn (string $e): bool => str_ends_with($entry, $e)) !== []) {
                $files[] = $directory . $entry;
            }
        }
        foreach ($subDirectories as $subDirectory) {
            array_push($files, ...$this->directoryTargets($directory . $subDirectory . '/', $extensions));
        }
        return $files;
    }

    private function resolve(string $path, string $includingFile): ?string
    {
        if (str_starts_with($path, './')) {
            return dirname($includingFile) . '/' . substr($path, 2);
        }
        if (str_starts_with($path, 'EXT:')) {
            [$key, $relative] = array_pad(explode('/', substr($path, 4), 2), 2, '');
            return isset($this->extensionPaths[$key]) ? $this->extensionPaths[$key] . $relative : null;
        }
        if ($this->publicPath === '' || str_starts_with($path, '/') || str_contains($path, '..')) {
            return null;
        }
        return $this->publicPath . $path;
    }

    /**
     * @param array{indent: string, source: string, condition: string, extensions: list<string>, targets: list<string>|null, raw: string} $include
     * @return string|null replacement, null: keep the line
     */
    private function convert(array $include, string $file, int $lineNumber): ?string
    {
        $where = sprintf('%s:%d', $this->display($file), $lineNumber);
        if ($include['targets'] === null) {
            $path = substr($include['source'], 5);
            if (!str_starts_with($include['source'], 'FILE:') || !in_array($this->suffix($path), self::IMPORTABLE, true)) {
                $this->warnings[] = sprintf('%s: kept, "%s" not found', $where, $include['source']);
                return null;
            }
            // e.g. a file of an extension that is not installed: @import ignores missing files as well
            $this->warnings[] = sprintf('%s: "%s" not found, converted anyway', $where, $include['source']);
            $imports = [$path];
        } else {
            $imports = str_starts_with($include['source'], 'FILE:')
                ? $this->fileImport($include['targets'][0], $include['source'], $file)
                : $this->directoryImports($include, $file);
        }
        if ($imports === null) {
            $this->warnings[] = sprintf('%s: kept, "%s" can not be loaded with @import (ending)', $where, $include['source']);
            return null;
        }
        $indent = $include['indent'];
        $lines = array_map(static fn (string $import): string => $indent . "@import '" . $import . "'", $imports);
        if ($include['condition'] !== '') {
            $lines = [$indent . '[' . $include['condition'] . ']', ...$lines, $indent . '[END]'];
        }
        return implode("\n", $lines);
    }

    /**
     * @return list<string>|null
     */
    private function fileImport(string $target, string $source, string $includingFile): ?array
    {
        $new = $this->renames[$target] ?? $target;
        if (!in_array($this->suffix($new), self::IMPORTABLE, true)) {
            // a target outside of the converted extensions may exist with the new ending already
            $candidate = $this->renamedFileName($target);
            if ($candidate === $target || !is_file($candidate)) {
                return null;
            }
            $new = $candidate;
        }
        $path = substr($source, 5);
        if (str_starts_with($path, './')) {
            return ['./' . $this->relativePath(dirname($this->renames[$includingFile] ?? $includingFile) . '/', $new)];
        }
        return [$this->referenceFor($new) ?? $path];
    }

    /**
     * One wildcard import per directory, if that loads exactly the included files in the same order,
     * otherwise one import per file.
     *
     * @param array{source: string, extensions: list<string>, targets: list<string>|null} $include
     * @return list<string>|null
     */
    private function directoryImports(array $include, string $includingFile): ?array
    {
        $files = array_map(fn (string $target): string => $this->renames[$target] ?? $target, $include['targets'] ?? []);
        foreach ($files as $file) {
            if (!in_array($this->suffix($file), self::IMPORTABLE, true)) {
                return null;
            }
        }
        $directory = rtrim((string)$this->resolve(substr($include['source'], 4), $includingFile), '/') . '/';
        $byDirectory = [];
        foreach ($files as $file) {
            $byDirectory[dirname($file) . '/'][] = $file;
        }
        $wildcards = [];
        foreach ($byDirectory as $subDirectory => $directoryFiles) {
            $pattern = $this->wildcardPattern($subDirectory, $directoryFiles);
            if ($pattern === null) {
                $wildcards = null;
                break;
            }
            $wildcards[] = $subDirectory . $pattern;
        }
        if ($files === []) {
            // nothing to include (yet): keep the directory import for files added later
            if ($include['extensions'] === []) {
                return null;
            }
            $wildcards = [];
            foreach ($include['extensions'] as $extension) {
                $wildcards[] = $directory . '*' . substr($this->renamedFileName('x.' . ltrim($extension, '.')), 1);
            }
            $wildcards = array_values(array_unique($wildcards));
        }
        $imports = [];
        foreach ($wildcards ?? $files as $path) {
            $import = $this->referenceFor($path);
            if ($import === null) {
                return null;
            }
            $imports[] = $import;
        }
        return $imports;
    }

    /**
     * "*.setup.typoscript", if @import 'directory/*.setup.typoscript' loads exactly these files in this order
     *
     * @param list<string> $files
     */
    private function wildcardPattern(string $directory, array $files): ?string
    {
        $suffixes = array_unique(array_map(fn (string $file): string => $this->longSuffix($file), $files));
        if (count($suffixes) !== 1) {
            return null;
        }
        $suffix = reset($suffixes);
        // the directory after the renames, in the order @import reads it
        $names = [];
        foreach ($this->sortedEntries($directory) as $entry) {
            $existing = $this->renames[$directory . $entry] ?? $directory . $entry;
            if (!is_dir($existing) && str_ends_with(basename($existing), '.' . $suffix)) {
                $names[] = basename($existing);
            }
        }
        sort($names, SORT_STRING);
        $matching = array_map(static fn (string $name): string => $directory . $name, $names);
        return $matching === $files ? '*.' . $suffix : null;
    }

    /**
     * "setup.typoscript" for foo.setup.typoscript, "typoscript" for foo.typoscript
     */
    private function longSuffix(string $file): string
    {
        $name = basename($file);
        foreach (['setup.typoscript', 'constants.typoscript'] as $long) {
            if (str_ends_with($name, '.' . $long)) {
                return $long;
            }
        }
        return $this->suffix($name);
    }

    private function referenceFor(string $file): ?string
    {
        // the longest path: the root package of the project contains all others
        $match = null;
        foreach ($this->extensionPaths as $key => $path) {
            if (str_starts_with($file, $path) && ($match === null || strlen($path) > strlen($this->extensionPaths[$match]))) {
                $match = $key;
            }
        }
        if ($match !== null) {
            return 'EXT:' . $match . '/' . substr($file, strlen($this->extensionPaths[$match]));
        }
        if ($this->publicPath !== '' && str_starts_with($file, $this->publicPath)) {
            return substr($file, strlen($this->publicPath));
        }
        return null;
    }

    private function rewriteReferences(string $content): string
    {
        $replacements = [];
        foreach ($this->renames as $old => $new) {
            $oldReference = $this->referenceFor($old);
            $newReference = $this->referenceFor($new);
            if ($oldReference !== null && $newReference !== null) {
                $replacements[$oldReference] = $newReference;
            }
        }
        if ($replacements === []) {
            return $content;
        }
        // EXT:x/setup.txt must not match EXT:x/setup.txt.dist
        return (string)preg_replace_callback(
            '/(' . implode('|', array_map(static fn (string $r): string => preg_quote($r, '/'), array_keys($replacements))) . ')(?![\w.])/',
            static fn (array $match): string => $replacements[$match[1]],
            $content
        );
    }

    private function relativePath(string $fromDirectory, string $file): string
    {
        return str_starts_with($file, $fromDirectory) ? substr($file, strlen($fromDirectory)) : basename($file);
    }

    private function convertRootOf(string $file): ?string
    {
        foreach ($this->convertRoots as $root => $key) {
            if (str_starts_with($file, $root)) {
                return $root;
            }
        }
        return null;
    }

    private function withoutSuffix(string $file): string
    {
        $suffix = $this->suffix($file);
        return $suffix === '' ? $file : substr($file, 0, -strlen($suffix) - 1);
    }

    private function suffix(string $file): string
    {
        $position = strrpos(basename($file), '.');
        return $position === false ? '' : strtolower(substr(basename($file), $position + 1));
    }

    private function display(string $file): string
    {
        return $this->referenceFor($file) ?? $file;
    }
}
