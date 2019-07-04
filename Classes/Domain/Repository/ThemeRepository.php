<?php

namespace KayStrobach\Themes\Domain\Repository;

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

use KayStrobach\Themes\Domain\Model\AbstractTheme;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TypoScript\ExtendedTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;

/**
 * Class ThemeRepository.
 *
 * The Repository of available themes
 */
class ThemeRepository implements RepositoryInterface, SingletonInterface
{
    /**
     * Objects of this repository.
     *
     * @var array
     */
    protected $addedObjects = [];

    /**
     * @return void
     */
    public function __construct()
    {
        // Hook to recognize themes, this is the magic point, why it's possible to support so many theme formats and types.
        if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['KayStrobach\\Themes\\Domain\\Repository\\ThemeRepository']['init'])) {
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['KayStrobach\\Themes\\Domain\\Repository\\ThemeRepository']['init'])) {
                $hookParameters = [];
                foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['KayStrobach\\Themes\\Domain\\Repository\\ThemeRepository']['init'] as $hookFunction) {
                    GeneralUtility::callUserFunction($hookFunction, $hookParameters, $this);
                }
            }
        }
    }

    /**
     * Adds an object to this repository.
     *
     * @param \KayStrobach\Themes\Domain\Model\AbstractTheme $object The object to add
     * @return void
     * @api
     */
    public function add($object)
    {
        $this->addedObjects[$object->getExtensionName()] = $object;
    }

    /**
     * Removes an object from this repository.
     *
     * @param \KayStrobach\Themes\Domain\Model\AbstractTheme $object The object to remove
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @return void
     * @api
     */
    public function remove($object)
    {
        throw new Exception('The method ' . __FUNCTION__ . ' is not implemented');
    }

    /**
     * Replaces an object by another.
     *
     * @param AbstractTheme $existingObject The existing object
     * @param AbstractTheme $newObject The new object
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @return void
     * @api
     */
    public function replace($existingObject, $newObject)
    {
        throw new Exception('The method ' . __FUNCTION__ . ' is not implemented');
    }

    /**
     * Replaces an existing object with the same identifier by the given object.
     *
     * @param AbstractTheme $modifiedObject The modified object
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @return void
     * @api
     */
    public function update($modifiedObject)
    {
        throw new Exception('The method ' . __FUNCTION__ . ' is not implemented');
    }

    /**
     * Returns all objects of this repository add()ed but not yet persisted to the storage layer.
     *
     * @return array An array of objects
     */
    public function getAddedObjects()
    {
        return $this->addedObjects;
    }

    /**
     * Returns an array with objects remove()d from the repository that had been persisted to the storage layer before.
     *
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @return void
     */
    public function getRemovedObjects()
    {
        throw new Exception('The method ' . __FUNCTION__ . ' is not implemented');
    }

    /**
     * Returns all objects of this repository.
     *
     * @return array An array of objects, empty if no objects found
     * @api
     */
    public function findAll()
    {
        return array_values($this->addedObjects);
    }

    /**
     * Returns the total number objects of this repository.
     *
     * @return int The object count
     * @api
     */
    public function countAll()
    {
        return count($this->addedObjects);
    }

    /**
     * Removes all objects of this repository as if remove() was called for all of them.
     *
     * @return void
     * @api
     */
    public function removeAll()
    {
        $this->addedObjects = [];
    }

    /**
     * Finds an object matching the given identifier.
     *
     * @param int $uid The identifier of the object to find
     * @return AbstractTheme The matching object if found, otherwise NULL
     * @api
     */
    public function findByUid($uid)
    {
        if ((is_array($this->addedObjects)) && (array_key_exists($uid, $this->addedObjects))) {
            return $this->addedObjects[$uid];
        }
    }

    /**
     * @param mixed $uid
     * @return AbstractTheme
     */
    public function findByIdentifier($uid)
    {
        return $this->findByUid($uid);
    }

    /**
     * @param int $pid id of the Page
     * @return mixed
     */
    public function findByPageId($pid)
    {
        $template = GeneralUtility::makeInstance(ExtendedTemplateService::class);
        $template->tt_track = 0;
        $template->init();
        $templateRow = $template->ext_getFirstTemplate($pid);
        return $this->findByUid($templateRow['tx_themes_skin']);
    }

    /**
     * @param int $pid
     * @return \KayStrobach\Themes\Domain\Model\Theme
     */
    public function findByPageOrRootline($pid)
    {
        $rootline = BackendUtility::BEgetRootLine($pid);
        foreach ($rootline as $page) {
            $theme = $this->findByPageId($page['uid']);
            if ($theme !== null) {
                return $theme;
            }
        }
    }

    /**
     * Sets the property names to order the result by per default.
     * Expected like this:
     * array(
     *  'foo' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING,
     *  'bar' => Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING
     * ).
     *
     * @param array $defaultOrderings The property names to order by
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @return void
     * @api
     */
    public function setDefaultOrderings(array $defaultOrderings)
    {
        throw new Exception('The method ' . __FUNCTION__ . ' is not implemented');
    }

    /**
     * Sets the default query settings to be used in this repository.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $defaultQuerySettings The query settings to be used by default
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @return void
     * @api
     */
    public function setDefaultQuerySettings(QuerySettingsInterface $defaultQuerySettings)
    {
        throw new Exception('The method ' . __FUNCTION__ . ' is not implemented');
    }

    /**
     * Returns a query for objects of this repository.
     *
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     * @return void
     * @api
     */
    public function createQuery()
    {
        throw new Exception('The method ' . __FUNCTION__ . ' is not implemented');
    }
}
