<?php

class Tx_Themes_Domain_Repository_ThemeRepository implements Tx_Extbase_Persistence_RepositoryInterface, t3lib_Singleton {
	protected $ignoredExtensions = array(
		'themes',
		'skinselector_content',
		'skinpreview',
		'templavoila',
		'piwik',
		'piwikintegration',
		'templavoila_framework',
		'be_acl',
		'sitemgr',
		'sitemgr_template',
		'sitemgr_fesettings',
		'sitemgr_fe_notfound',
		'cal',
		'extension_builder',
		'coreupdate',
		'contextswitcher',
		'extdeveval',
		'powermail',
		'kickstarter',
		'tt_news',
		'dyncss',
		'dyncss_less',
		'dyncss_scss',
		'dyncss_turbine',
		'static_info_tables',
		'realurl',
	);

	/**
	 * Objects of this repository
	 *
	 * @var array
	 */
	protected $addedObjects;

	function __construct() {
		// exclude extensions, which are not worth to check them
		$extensionsToCheck = array_diff(
			explode(',', t3lib_extMgm::getEnabledExtensionList()),
			explode(',', t3lib_extMgm::getRequiredExtensionList()),
			$this->ignoredExtensions,
			scandir(PATH_typo3 . 'sysext')
		);

		// check extensions, which are worth to check
		foreach($extensionsToCheck as $extensionName) {
			$extPath = t3lib_extMgm::extPath($extensionName);
			if(file_exists($extPath . 'Configuration/Theme') && file_exists($extPath . 'Configuration/Theme/setup.ts')) {
				$this->add(new Tx_Themes_Domain_Model_Theme($extensionName));
			}
		}

		// hook to include themes, which do not follow the convention
		if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Tx_Themes_Domain_Repository_ThemeRepository']['init']))	{
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Tx_Themes_Domain_Repository_ThemeRepository']['init'])) {
				$hookParameters = array();
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Tx_Themes_Domain_Repository_ThemeRepository']['init'] as $hookFunction)	{
					t3lib_div::callUserFunction($hookFunction, $hookParameters, $this);
				}
			}
		}
	}

	/**
	 * Adds an object to this repository.
	 *
	 * @param Tx_Themes_Domain_Model_Theme $object The object to add
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
	 * @param object $object The object to remove
	 * @return void
	 * @api
	 */
	public function remove($object)
	{
		// TODO: Implement remove() method.
	}

	/**
	 * Replaces an object by another.
	 *
	 * @param object $existingObject The existing object
	 * @param object $newObject The new object
	 * @return void
	 * @api
	 */
	public function replace($existingObject, $newObject)
	{
		// TODO: Implement replace() method.
	}

	/**
	 * Replaces an existing object with the same identifier by the given object
	 *
	 * @param object $modifiedObject The modified object
	 * @api
	 */
	public function update($modifiedObject)
	{
		// TODO: Implement update() method.
	}

	/**
	 * Returns all objects of this repository add()ed but not yet persisted to
	 * the storage layer.
	 *
	 * @return array An array of objects
	 */
	public function getAddedObjects()
	{
		return $this->addedObjects;
	}

	/**
	 * Returns an array with objects remove()d from the repository that
	 * had been persisted to the storage layer before.
	 *
	 * @return array
	 */
	public function getRemovedObjects()
	{
		// TODO: Implement getRemovedObjects() method.
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
	 * @return integer The object count
	 * @api
	 */
	public function countAll()
	{
		return count($this->addedObjects);
	}

	/**
	 * Removes all objects of this repository as if remove() was called for
	 * all of them.
	 *
	 * @return void
	 * @api
	 */
	public function removeAll()
	{
		$this->addedObjects = array();
	}

	/**
	 * Finds an object matching the given identifier.
	 *
	 * @param int $uid The identifier of the object to find
	 * @return Tx_Themes_Domain_Model_Theme The matching object if found, otherwise NULL
	 * @api
	 */
	public function findByUid($uid)
	{
		if(array_key_exists($uid, $this->addedObjects)) {
			return $this->addedObjects[$uid];
		} else {
			return NULL;
		}
		/*
		foreach($this->addedObjects as $theme) {
			if($theme->getExtensionName() === $uid) {
				return $theme;
			}
		}
		return NULL;*/
	}

	/**
	 * @param $pid id of the Page
	 * @return mixed
	 */
	public function findByPageId($pid) {
		$template = t3lib_div::makeInstance("t3lib_tsparser_ext");
		$template->tt_track = 0;
		$template->init();
		$templateRow = $template->ext_getFirstTemplate($pid);
		return $this->findByUid($templateRow['tx_themes_skin']);
	}

	public function findByPageOrRootline($pid) {
		$rootline = t3lib_BEfunc::BEgetRootLine($pid);
		foreach($rootline as $page) {
			$theme = $this->findByPageId($page['uid']);
			if($theme !== NULL) {
				return $theme;
			}
		}
		return NULL;
	}

	/**
	 * Sets the property names to order the result by per default.
	 * Expected like this:
	 * array(
	 *  'foo' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING,
	 *  'bar' => Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING
	 * )
	 *
	 * @param array $defaultOrderings The property names to order by
	 * @return void
	 * @api
	 */
	public function setDefaultOrderings(array $defaultOrderings)
	{
		// TODO: Implement setDefaultOrderings() method.
	}

	/**
	 * Sets the default query settings to be used in this repository
	 *
	 * @param Tx_Extbase_Persistence_QuerySettingsInterface $defaultQuerySettings The query settings to be used by default
	 * @return void
	 * @api
	 */
	public function setDefaultQuerySettings(Tx_Extbase_Persistence_QuerySettingsInterface $defaultQuerySettings)
	{
		// TODO: Implement setDefaultQuerySettings() method.
	}

	/**
	 * Returns a query for objects of this repository
	 *
	 * @return Tx_Extbase_Persistence_QueryInterface
	 * @api
	 */
	public function createQuery()
	{
		// TODO: Implement createQuery() method.
	}
}