<?php

namespace KayStrobach\Themes\ViewHelpers\Variable;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * @author Thomas Deuling <typo3@coding.ms>, coding.ms
 */
class PopVariantViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('name', 'string', 'Name', true);
    }

    /**
     * Pop the variant with the variable in $name.
     *
     * @return void
     */
    public function render()
    {
        $name = $this->arguments['name'];

        if (false === $this->templateVariableContainer->exists('themes')) {
            return;
        } else {
            $themes = $this->templateVariableContainer->get('themes');
            if (isset($themes['variants'])) {
                $value = '';
                if (isset($themes['variants']['css'])) {
                    if (isset($themes['variants']['css'][$name])) {
                        $value = $themes['variants']['css'][$name];
                        unset($themes['variants']['css'][$name]);
                    }
                }
                if (isset($themes['variants']['css2key'])) {
                    if (isset($themes['variants']['css2key'][$value])) {
                        unset($themes['variants']['css2key'][$value]);
                    }
                }
                $themes['variants']['key2css'] = $themes['variants']['css'];
                $themes['variants']['cssClasses'] = implode(' ', $themes['variants']['css']);
                // Write back
                $this->templateVariableContainer->remove('themes');
                $this->templateVariableContainer->add('themes', $themes);
            }
        }
    }
}
