<?php

namespace KayStrobach\Themes\EventListener;

use KayStrobach\Themes\Domain\Model\Theme;
use KayStrobach\Themes\Domain\Repository\ThemeRepository;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\AfterTemplatesHaveBeenDeterminedEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AfterTemplatesHaveBeenDeterminedEventListener
{
    public function __invoke(AfterTemplatesHaveBeenDeterminedEvent $event): void
    {
        $rows = $event->getTemplateRows();

        foreach ($rows as $rowIndex => $row) {
            if (isset($row['tx_themes_skin']) && trim($row['tx_themes_skin']) !== '') {
                $themeIdentifier = $row['tx_themes_skin'];
                /** @var ThemeRepository $themeRepository */
                $themeRepository = GeneralUtility::makeInstance(ThemeRepository::class);
                /** @var Theme $theme */
                $theme = $themeRepository->findByUid($themeIdentifier);
                if ($theme !== null) {
                    $themeFeatures = GeneralUtility::trimExplode(
                        ',',
                        $row['tx_themes_features'],
                        true
                    );



                    //
                    // Prepend theme TypoScript constants and setup
                    $constants = $theme->getBasicConstants($row['pid']);
                    $constants .= PHP_EOL . $theme->getTypoScriptForLanguage();
                    $constants .= $this->getFileContent($theme->getTypoScriptConstantsAbsPath());
                    $setup = $this->getFileContent($theme->getTypoScriptAbsPath());



                    $extensions = GeneralUtility::trimExplode(
                        ',',
                        $row['tx_themes_extensions'],
                        true
                    );
                    foreach ($extensions as $extension) {
                    }


                    $rows[$rowIndex]['constants'] .= $constants;
                    $rows[$rowIndex]['config'] .= $setup;
                }
            }
        }


        $event->setTemplateRows($rows);
    }

    protected function getFileContent(string $file): string
    {
        $content = '# File not found: ' . $file;
        if (file_exists($file)) {
            $content = file_get_contents($file);
        }
        return $content . PHP_EOL;
    }
}
