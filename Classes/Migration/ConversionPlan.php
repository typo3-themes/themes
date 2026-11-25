<?php

declare(strict_types=1);

namespace KayStrobach\Themes\Migration;

/**
 * Result of IncludeTypoScriptConverter::plan(): what apply() changes
 */
final class ConversionPlan
{
    /**
     * @param array<string, string> $renames absolute old file name => absolute new file name
     * @param array<string, string> $contents absolute file name (after the renames) => new content
     * @param list<string> $warnings includes that were kept, renames that were skipped
     */
    public function __construct(
        public readonly array $renames,
        public readonly array $contents,
        public readonly array $warnings,
    ) {}

    public function isEmpty(): bool
    {
        return $this->renames === [] && $this->contents === [];
    }
}
