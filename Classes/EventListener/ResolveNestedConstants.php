<?php

declare(strict_types=1);

namespace KayStrobach\Themes\EventListener;

use TYPO3\CMS\Core\TypoScript\AST\Node\NodeInterface;
use TYPO3\CMS\Frontend\Event\ModifyTypoScriptConstantsEvent;

/**
 * Resolves constants referencing other constants, e.g. "siteTitle = {$themes.configuration.siteName}".
 *
 * TYPO3 up to v11 substituted constants within constants, the TypoScript parser of v12+ only substitutes them in the
 * setup. Themes and skins rely on it (logo titles, skin paths, ...), the core recommends this event for it.
 * References to undefined constants are kept as they are, like the core does in the setup.
 */
final class ResolveNestedConstants
{
    // nesting depth, also stops self references like "a = {$a}x"
    private const MAX_PASSES = 10;

    public function __invoke(ModifyTypoScriptConstantsEvent $event): void
    {
        $ast = $event->getConstantsAst();
        for ($pass = 0; $pass < self::MAX_PASSES; $pass++) {
            $flatConstants = $ast->flatten();
            if (!$this->substitute($ast, $flatConstants)) {
                break;
            }
        }
        $event->setConstantsAst($ast);
    }

    /**
     * @param array<string, string> $flatConstants
     * @return bool true if a value has been changed
     */
    private function substitute(NodeInterface $node, array $flatConstants): bool
    {
        $changed = false;
        foreach ($node->getNextChild() as $child) {
            $value = $child->getValue();
            if ($value !== null && str_contains($value, '{$')) {
                $resolved = preg_replace_callback(
                    '/\{\$([^{}$\s]+)\}/',
                    static fn (array $match): string => array_key_exists($match[1], $flatConstants)
                        ? (string)$flatConstants[$match[1]]
                        : $match[0],
                    $value
                );
                if ($resolved !== $value) {
                    $child->setValue($resolved);
                    $changed = true;
                }
            }
            $changed = $this->substitute($child, $flatConstants) || $changed;
        }
        return $changed;
    }
}
