<?php
namespace KayStrobach\Themes\ViewHelpers\Widget;

/**
 * Provides a Language Menu
 *
 * @author Thomas Deuling <typo3@coding.ms>
 * @package themes
 */
class LanguageMenuViewHelper extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetViewHelper {

    /**
     * @var \KayStrobach\Themes\ViewHelpers\Widget\Controller\LanguageMenuController
     * @inject
     */
    protected $controller;

    /**
     * @param \KayStrobach\Themes\ViewHelpers\Widget\Controller\LanguageMenuController $controller
     * @return void
     */
    public function injectController(\KayStrobach\Themes\ViewHelpers\Widget\Controller\LanguageMenuController $controller) {
        $this->controller = $controller;
    }

    /**
     * Language Repository
     *
     * @var \SJBR\StaticInfoTables\Domain\Repository\LanguageRepository
     * @inject
     */
    protected $languageRepository;

    /**
     * @param \SJBR\StaticInfoTables\Domain\Repository\LanguageRepository $languageRepository
     * @return void
     */
    public function injectLanguageRepository(\SJBR\StaticInfoTables\Domain\Repository\LanguageRepository $languageRepository) {
        $this->languageRepository = $languageRepository;
    }

    /**
     * @param string $availableLanguages the available languages in the current theme
     * @param int $currentLanguageUid the selected language uid
     * @param string $defaultLanguageIsoCodeShort the default language ISO code (short)
     * @param string $defaultLanguageLabel the the default language label
     * @return string
     */
    public function render($availableLanguages='', $currentLanguageUid=0, $defaultLanguageIsoCodeShort='en', $defaultLanguageLabel='English') {
        return $this->initiateSubRequest();
    }

}
?>