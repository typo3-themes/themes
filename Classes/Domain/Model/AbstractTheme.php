<?php

namespace KayStrobach\Themes\Domain\Model;

use KayStrobach\Themes\Utilities\ApplicationContext;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class AbstractTheme.
 *
 * @todo get rid of getExtensionname, use EXT:extname as theme name to avoid conflicts in the database
 */
class AbstractTheme extends AbstractEntity
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var array
     */
    protected $author = [];

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $extensionName;

    /**
     * @var string
     */
    protected $version = '';

    /**
     * @var string
     */
    protected $previewImage;

    /**
     * @var string
     */
    protected $pathTyposcript;

    /**
     * @var string
     */
    protected $pathTyposcriptConstants;

    /**
     * @var string
     */
    protected $pathTsConfig;

    /**
     * @var array
     */
    protected $metaInformation = [];

    /**
     * Constructs a new Theme.
     *
     * @param string $extensionName
     * @api
     */
    public function __construct($extensionName)
    {
        $this->extensionName = $extensionName;
    }

    /**
     * Returns the title.
     *
     * @return string
     *
     * @api
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the description.
     *
     * @return string
     *
     * @api
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the previewImage.
     *
     * @return string
     *
     * @api
     */
    public function getPreviewImage()
    {
        // We need to use a real image file path, because in case of using a file
        // reference, a non admin backend user might not have access to the storage!
        $previewImage = GeneralUtility::getFileAbsFileName($this->previewImage);
        $previewImage = PathUtility::getAbsoluteWebPath($previewImage);
        // Since 8.7.x we need to prefix with EXT:
        $replacement = '/typo3conf/ext/';
        if (substr($previewImage, 0, strlen($replacement)) === $replacement) {
            $previewImage = str_replace('/typo3conf/ext/', 'EXT:', $previewImage);
        }
        return $previewImage;
    }

    /**
     * @return array
     */
    public function getAllPreviewImages()
    {
        return [
            [
                'file'    => $this->getPreviewImage(),
                'caption' => '',
            ],
        ];
    }

    /**
     * Returns the previewImage.
     *
     * @return string
     *
     * @api
     */
    public function getExtensionName()
    {
        return $this->extensionName;
    }

    /**
     * @return array
     */
    public function getMetaInformation()
    {
        return $this->metaInformation;
    }

    /**
     * @param array $metaInformation
     */
    public function setMetaInformation($metaInformation)
    {
        $this->metaInformation = $metaInformation;
    }

    /**
     * Returns the version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return array
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Returns the previewImage.
     *
     * @return string
     *
     * @api
     */
    public function getManualUrl()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getTypoScriptConfig()
    {
        $typoScriptConfig = '';
        $typoScriptConfigAbsPath = $this->getTypoScriptConfigAbsPath();
        if (file_exists($typoScriptConfigAbsPath) && is_file($typoScriptConfigAbsPath)) {
            $typoScriptConfig = file_get_contents($typoScriptConfigAbsPath);
        }



        return $typoScriptConfig;
    }

    /**
     * @return string
     */
    public function getTypoScriptConfigAbsPath()
    {
        return $this->pathTsConfig;
    }

    /**
     * @return string
     */
    public function getTypoScriptAbsPath()
    {
        return $this->pathTyposcript;
    }

    /**
     * @return string
     */
    public function getTypoScriptConstantsAbsPath()
    {
        return $this->pathTyposcriptConstants;
    }

    /**
     * returns the relative path of the theme.
     *
     * @return string
     */
    public function getRelativePath()
    {
        if (ExtensionManagementUtility::isLoaded($this->getExtensionName())) {
            return PathUtility::stripPathSitePrefix(ExtensionManagementUtility::extPath($this->getExtensionName()));
        }

        return '';
    }

    /**
     * Includes static template records (from static_template table) and static template files (from extensions) for the input template record row.
     *
     * @param array $params Array of parameters from the parent class.  Includes idList, templateId, pid, and row.
     * @param \TYPO3\CMS\Core\TypoScript\TemplateService $pObj Reference back to parent object, t3lib_tstemplate or one of its subclasses.
     * @param array $extensions Array of additional TypoScript for extensions
     * @param array $features Array of additional TypoScript for features
     *
     * @return void
     */
    public function addTypoScriptForFe(&$params, TemplateService &$pObj, $extensions=[], $features=[])
    {
        // @codingStandardsIgnoreStart
        $themeItem = [
            'constants'           => @is_file($this->getTypoScriptConstantsAbsPath()) ? GeneralUtility::getUrl($this->getTypoScriptConstantsAbsPath()) : '',
            'config'              => @is_file($this->getTypoScriptAbsPath()) ? GeneralUtility::getUrl($this->getTypoScriptAbsPath()) : '',
            'include_static'      => '',
            'include_static_file' => '',
            'title'               => 'themes:'.$this->getExtensionName(),
            'uid'                 => md5($this->getExtensionName()),
        ];
        // @codingStandardsIgnoreEnd

        $themeItem['constants'] .= $this->getBasicConstants($params['pid']);
        $themeItem['constants'] .= LF.$this->getTypoScriptForLanguage($params, $pObj);

        $pObj->processTemplate(
            $themeItem,
            $params['idList'].',ext_themes'.str_replace('_', '', $this->getExtensionName()),
            $params['pid'],
            'ext_themes'.str_replace('_', '', $this->getExtensionName()),
            $params['templateId']
        );
        //
        // Additional TypoScript for extensions
        if (count($extensions) > 0) {
            foreach ($extensions as $extension) {
                $themeItem = $this->getTypoScriptDataForProcessing($extension, 'extension');
                $pObj->processTemplate(
                    $themeItem,
                    $params['idList'].',ext_theme'.str_replace('_', '', $this->getExtensionName()),
                    $params['pid'], 'ext_theme'.str_replace('_', '', $this->getExtensionName()),
                    $params['templateId']
                );
            }
        }
        //
        // Additional TypoScript for features
        if (count($features) > 0) {
            foreach ($features as $feature) {
                $themeItem = $this->getTypoScriptDataForProcessing($feature, 'feature');
                $pObj->processTemplate(
                    $themeItem,
                    $params['idList'].',ext_theme'.str_replace('_', '', $this->getExtensionName()),
                    $params['pid'], 'ext_theme'.str_replace('_', '', $this->getExtensionName()),
                    $params['templateId']
                );
            }
        }
    }

    /**
     * @param $key string Key of the Extension or Feature
     * @param $type string Typ can be either extension or feature.
     * @return array
     */
    protected function getTypoScriptDataForProcessing($key, $type='extension')
    {
        $relPath = '';
        $keyParts = explode('_', $key);
        $extensionKey = GeneralUtility::camelCaseToLowerCaseUnderscored($keyParts[0]);
        $extensionPath = ExtensionManagementUtility::extPath($extensionKey);
        if ($type === 'feature') {
            $relPath = $extensionPath . 'Configuration/TypoScript/Features/' . $keyParts[1] . '/';
        } elseif ($type === 'extension') {
            $relPath = $extensionPath . 'Resources/Private/Extensions/' . $keyParts[1] . '/TypoScript/';
        }
        $themeItem = [
            'constants' => '',
            'config' => '',
            'include_static' => '',
            'include_static_file' => '',
            'title' => 'themes:' . $this->getExtensionName() . ':' . $relPath,
            'uid' => md5($this->getExtensionName() . ':' . $relPath),
        ];
        $setupFile = GeneralUtility::getFileAbsFileName($relPath . 'setup.txt');
        if (file_exists($setupFile)) {
            $themeItem['config'] = file_get_contents($setupFile);
        } else {
            $setupFile = GeneralUtility::getFileAbsFileName($relPath . 'setup.typoscript');
            if (file_exists($setupFile)) {
                $themeItem['config'] = file_get_contents($setupFile);
            }
        }
        $constantsFile = GeneralUtility::getFileAbsFileName($relPath . 'constants.txt');
        if (file_exists($constantsFile)) {
            $themeItem['constants'] = file_get_contents($constantsFile);
        } else {
            $constantsFile = GeneralUtility::getFileAbsFileName($relPath . 'constants.typoscript');
            if (file_exists($constantsFile)) {
                $themeItem['constants'] = file_get_contents($constantsFile);
            }
        }
        return $themeItem;
    }

    /**
     * @param array $params
     * @param \TYPO3\CMS\Core\TypoScript\TemplateService $pObj
     *
     * @return string
     */
    public function getTypoScriptForLanguage(&$params, &$pObj)
    {
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_language');
        $queryBuilder->select('sys_language.*', 'static_languages.lg_name_local', 'static_languages.lg_name_en', 'static_languages.lg_collate_locale')
            ->from('sys_language')
            ->from('static_languages')
            ->where(
                $queryBuilder->expr()->eq(
                    'sys_language.static_lang_isocode', 'static_languages.uid'
                )
            );
        /** @var  \Doctrine\DBAL\Driver\Statement $statement */
        $languages = $queryBuilder->execute();
        $outputBuffer = '';
        $languageUids = [];
        $key = 'themes.languages';
        if ($languages->rowCount()>0) {
            while ($language = $languages->fetch()) {
                $languageUids[] = $language['uid'];
                $buffer = '[globalVar = GP:L='.$language['uid'].']'.LF;
                $buffer .= $key.'.current {'.LF;
                $buffer .= ' uid = '.$language['uid'].LF;
                $buffer .= ' label = '.$language['title'].LF;
                $buffer .= ' labelLocalized = '.$language['lg_name_local'].LF;
                $buffer .= ' labelEnglish = '.$language['lg_name_en'].LF;
                $buffer .= ' flag = '.$language['flag'].LF;
                $buffer .= ' isoCode = '.$language['lg_collate_locale'].LF;
                $buffer .= ' isoCodeShort = '.array_shift(explode('_', $language['lg_collate_locale'])).LF;
                $buffer .= ' isoCodeHtml = '.str_replace('_', '-', $language['lg_collate_locale']).LF;
                $buffer .= '} '.LF;
                $buffer .= '[global]'.LF;
                $outputBuffer .= $buffer;
            }
            $outputBuffer .= $key.'.available='.implode(',', $languageUids).LF;
        } else {
            $outputBuffer .= $key.'.available='.LF;
        }
        return $outputBuffer;
    }

    /**
     * Returns the basic TypoScript constants
     * @param $pid
     * @return string
     */
    protected function getBasicConstants($pid)
    {
        $buffer = '';
        $buffer .= LF.'themes.relativePath = '.$this->getRelativePath();
        $buffer .= LF.'themes.name = '.$this->getExtensionName();
        $buffer .= LF.'themes.templatePageId = '.$pid;
        $buffer .= LF.'themes.mode.context = '.ApplicationContext::getApplicationContext();
        $buffer .= LF.'themes.mode.isDevelopment = '.(int) ApplicationContext::isDevelopmentModeActive();
        $buffer .= LF.'themes.mode.isProduction = '.(int) !ApplicationContext::isDevelopmentModeActive();
        return $buffer;
    }
}
