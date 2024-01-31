<?php

namespace KayStrobach\Themes\EventListener;

use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\AfterTemplatesHaveBeenDeterminedEvent;

class AfterTemplatesHaveBeenDeterminedEventListener
{
    public function __invoke(AfterTemplatesHaveBeenDeterminedEvent $event): void
    {
        $rows = $event->getTemplateRows();
        $event->setTemplateRows($rows);
    }
}
