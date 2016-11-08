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
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\ContentObject\Exception\ContentRenderingException;

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
     * @param ContentObjectRenderer $cObj                       The content object renderer, which contains data of the content element
     * @param array                 $contentObjectConfiguration The configuration of Content Object
     * @param array                 $processorConfiguration     The configuration of this processor
     * @param array                 $processedData              Key/value store of processed data (e.g. to be passed to a Fluid View)
     *
     * @throws ContentRenderingException
     *
     * @return array the processed data as key/value store
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData)
    {
        $db = $this->getDb();
        $processedData['themes']['buttons'] = array();
        $where = 'tt_content=' . (int)$processedData['data']['uid'] . ' AND deleted=0 AND hidden=0'; 
        $result = $db->exec_SELECTquery('*', 'tx_themes_buttoncontent', $where, '', 'sorting');
        while ($row = $db->sql_fetch_assoc($result)) {
            $link = array();
            $link['uid'] = $row['uid'];
            $link['text'] = $row['linktext'];
            $link['target'] = $row['linktarget'];
            $link['targetPageUid'] = (int)$link['target'];
            $link['title'] = $row['linktitle'];
            $link['icon'] = '';
            if ($row['icon'] != '') {
                $setup = $this->getFrontendController()->tmpl->setup;
                $link['icon'] = $setup['lib.']['icons.']['cssMap.'][$row['icon']];
            }
            $processedData['themes']['buttons'][] = $link;
        }
        $db->sql_free_result($res);
        return $processedData;
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDb()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
