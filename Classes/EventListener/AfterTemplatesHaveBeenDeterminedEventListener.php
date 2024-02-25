<?php

namespace KayStrobach\Themes\EventListener;

use KayStrobach\Themes\Domain\Model\Theme;
use KayStrobach\Themes\Domain\Repository\ThemeRepository;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\AfterTemplatesHaveBeenDeterminedEvent;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
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
                    $theme->buildTypoScript($row);
                    //
                    // Re-insert collected constants and setup TypoScript
                    $rows[$rowIndex]['constants'] = $theme->getConstants() . PHP_EOL . $rows[$rowIndex]['constants'];
                    $rows[$rowIndex]['config'] = $theme->getSetup() . PHP_EOL . $rows[$rowIndex]['config'];
                }
            }
        }
        $event->setTemplateRows($rows);
    }
}
