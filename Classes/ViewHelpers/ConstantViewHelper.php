<?php

declare(strict_types=1);

namespace KayStrobach\Themes\ViewHelpers;

use Psr\Http\Message\ServerRequestInterface;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * ConstantViewHelper
 */
class ConstantViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Initialize arguments.
     *
     * @throws Exception
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('constant', 'string', 'TypoScript constant');
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $constant = trim((string) $this->arguments['constant']);
        // TYPO3 v12 and later: the constants of the page are in the request attribute "frontend.typoscript"
        // the request is an attribute of the rendering context since TYPO3 v13, v12 has getRequest()
        $request = null;
        if (method_exists($this->renderingContext, 'hasAttribute') && $this->renderingContext->hasAttribute(ServerRequestInterface::class)) {
            $request = $this->renderingContext->getAttribute(ServerRequestInterface::class);
        } elseif (method_exists($this->renderingContext, 'getRequest')) {
            $request = $this->renderingContext->getRequest();
        }
        $request = $request ?? ($GLOBALS['TYPO3_REQUEST'] ?? null);
        $frontendTypoScript = $request instanceof ServerRequestInterface ? $request->getAttribute('frontend.typoscript') : null;
        if ($frontendTypoScript === null) {
            return '';
        }
        return (string)($frontendTypoScript->getFlatSettings()[$constant] ?? '');
    }
}
