<?php

namespace KayStrobach\Themes\DataProcessing;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\ContentObject\Exception\ContentRenderingException;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;

/**
 * DataProcessor for Fluid Styled Content.
 *
 * @author Thomas Deuling <typo3@coding.ms>
 */
class ThemesButtonDataProcessor implements DataProcessorInterface
{
    /**
     * Process data for the Themes icons.
     *
     * @param ContentObjectRenderer $cObj The content object renderer, which contains data of the content element
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     *
     * @throws ContentRenderingException
     *
     * @return array the processed data as key/value store
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ) {
        $processedData['themes']['buttons'] = array();
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_themes_buttoncontent');
        $queryBuilder->select('*')
            ->from('tx_themes_buttoncontent')
            ->where(
                $queryBuilder->expr()->eq(
                    'tt_content',
                    $queryBuilder->createNamedParameter((int)$processedData['data']['uid'], \PDO::PARAM_INT)
                )
            )
            ->orderBy('sorting');
        /** @var  \Doctrine\DBAL\Driver\Statement $statement */
        $statement = $queryBuilder->execute();
        while ($row = $statement->fetch()) {
            $link = array();
            $link['uid'] = $row['uid'];
            $link['text'] = $row['linktext'];
            $link['link'] = $row['linktarget'];
            $link['linkParameter'] = GeneralUtility::makeInstance(TypoLinkCodecService::class)->decode($row['linktarget']);
            $link['title'] = $row['linktitle'];
            $link['icon'] = '';
            if ($row['icon'] != '') {
                $setup = $this->getFrontendController()->tmpl->setup;
                $link['icon'] = $setup['lib.']['icons.']['cssMap.'][$row['icon']];
            }
            $processedData['themes']['buttons'][] = $link;
        }
        return $processedData;
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
