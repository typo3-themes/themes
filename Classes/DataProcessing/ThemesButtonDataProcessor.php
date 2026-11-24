<?php

declare(strict_types=1);

namespace KayStrobach\Themes\DataProcessing;

/***************************************************************
 *
 * Copyright notice
 *
 * (c) 2019 TYPO3 Themes-Team <team@typo3-themes.org>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\LinkHandling\TypoLinkCodecService;
use Doctrine\DBAL\Result;
use PDO;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * DataProcessor for Fluid Styled Content.
 *
 * @author Thomas Deuling <typo3@coding.ms>
 */
class ThemesButtonDataProcessor implements DataProcessorInterface
{
    public function __construct(private readonly \TYPO3\CMS\Core\Database\ConnectionPool $connectionPool)
    {
    }
    /**
     * Process data for the Themes icons.
     *
     * @param ContentObjectRenderer $cObj The content object renderer, which contains data of the content element
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     *
     * @return array the processed data as key/value store
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        $processedData['themes']['buttons'] = [];
        $uid = (int)($processedData['data']['uid'] ?? 0);
        if ($uid > 0) {
            /** @var ConnectionPool $connectionPool */
            $connectionPool = $this->connectionPool;
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_themes_buttoncontent');
            $queryBuilder->select('*')
                ->from('tx_themes_buttoncontent')
                ->where(
                    $queryBuilder->expr()->eq(
                        'tt_content',
                        $queryBuilder->createNamedParameter((int)$processedData['data']['uid'], \TYPO3\CMS\Core\Database\Connection::PARAM_INT)
                    )
                )
                ->orderBy('sorting');
            /** @var Result $statement */
            $statement = $queryBuilder->executeQuery();
            while ($row = $statement->fetchAssociative()) {
                $link = [];
                $link['uid'] = $row['uid'];
                $link['text'] = $row['linktext'];
                $link['link'] = $row['linktarget'];
                $link['linkParameter'] = GeneralUtility::makeInstance(TypoLinkCodecService::class)->decode(
                    $row['linktarget']
                );
                $link['title'] = $row['linktitle'];
                $link['icon'] = '';
                if ($row['icon'] != '') {
                    $setup = $cObj->getRequest()->getAttribute('frontend.typoscript')->getSetupArray();
                    $link['icon'] = $setup['lib.']['icons.']['cssMap.'][$row['icon']];
                }
                $processedData['themes']['buttons'][] = $link;
            }
        }
        return $processedData;
    }
}
