<?php

declare(strict_types=1);

namespace KayStrobach\Themes\EventListener;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\TypoScript\AST\Node\NodeInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Frontend\Event\ModifyTypoScriptConstantsEvent;

/**
 * Rewrites "typo3conf/ext/<extension>/Resources/Public/..." in constants to the public URL of the resource.
 *
 * Older themes have such paths in their constants (e.g. themes.configuration.font.icon.fontFilePath of theme_bootstrap).
 * In composer mode (default since TYPO3 v12) the extensions are not below typo3conf/ext, their public resources are
 * published to /_assets/<hash>/. Paths of extensions that are not loaded or outside of Resources/Public stay untouched.
 */
final class RewriteLegacyExtensionPaths
{
    public function __invoke(ModifyTypoScriptConstantsEvent $event): void
    {
        if (!Environment::isComposerMode()) {
            return;
        }
        $ast = $event->getConstantsAst();
        $this->rewrite($ast);
        $event->setConstantsAst($ast);
    }

    private function rewrite(NodeInterface $node): void
    {
        foreach ($node->getNextChild() as $child) {
            $value = $child->getValue();
            if ($value !== null && str_contains($value, 'typo3conf/ext/')) {
                $child->setValue($this->rewriteValue($value));
            }
            $this->rewrite($child);
        }
    }

    /**
     * "/typo3conf/ext/<extension>/Resources/Public/…" (also relative, "../typo3conf/ext/…") to the public URL.
     */
    public function rewriteValue(string $value): string
    {
        return (string)preg_replace_callback(
            '#(?:\.\./)*/?typo3conf/ext/([a-z0-9_]+)/Resources/Public/#',
            static function (array $match): string {
                if (!ExtensionManagementUtility::isLoaded($match[1])) {
                    return $match[0];
                }
                return '/' . trim(PathUtility::getPublicResourceWebPath('EXT:' . $match[1] . '/Resources/Public/'), '/') . '/';
            },
            $value
        );
    }
}
