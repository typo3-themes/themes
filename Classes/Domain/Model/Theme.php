<?php

namespace KayStrobach\Themes\Domain\Model;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\TypoScript\TemplateService;

/**
 * Class Theme.
 *
 * the theme model object
 */
class Theme extends AbstractTheme
{
    /**
     * Constructs a new Theme.
     *
     * @param $extensionName
     * @throws \Exception
     * @api
     */
    public function __construct($extensionName)
    {
        parent::__construct($extensionName);
        if (ExtensionManagementUtility::isLoaded($extensionName, false)) {
            // set needed path variables
            $path = ExtensionManagementUtility::extPath($this->getExtensionName());
            //
            // Get TypoScript setup (setup.txt | setup.typoscript)
            if (file_exists($path . 'Configuration/TypoScript/setup.txt')) {
                $this->pathTyposcript = $path . 'Configuration/TypoScript/setup.txt';
            } else {
                $this->pathTyposcript = $path . 'Configuration/TypoScript/setup.typoscript';
            }
            //
            // Get TypoScript constants (constants.txt | constants.typoscript)
            if (file_exists($path . 'Configuration/TypoScript/constants.txt')) {
                $this->pathTyposcriptConstants = $path . 'Configuration/TypoScript/constants.txt';
            } else {
                $this->pathTyposcriptConstants = $path . 'Configuration/TypoScript/constants.typoscript';
            }
            //
            // Get TypoScript tsconfig (tsconfig.txt | tsconfig.typoscript)
            if (file_exists($path . 'Configuration/PageTS/tsconfig.txt')) {
                $this->pathTsConfig = $path . 'Configuration/PageTS/tsconfig.txt';
            } else {
                $this->pathTsConfig = $path . 'Configuration/PageTS/tsconfig.typoscript';
            }
            $this->importExtEmConf();
            if (is_file(ExtensionManagementUtility::extPath($this->getExtensionName()) . 'Meta/Screenshots/screenshot.png')) {
                $this->previewImage = ExtensionManagementUtility::siteRelPath($this->getExtensionName()) . 'Meta/Screenshots/screenshot.png';
            } else {
                $this->previewImage = ExtensionManagementUtility::siteRelPath('themes') . 'Resources/Public/Images/screenshot.gif';
            }
            $yamlFile = ExtensionManagementUtility::extPath($this->getExtensionName()) . 'Meta/theme.yaml';
            if (file_exists($yamlFile)) {
                if (version_compare(TYPO3_version, '8.7', '<')) {
                    if (class_exists('\Symfony\Component\Yaml\Yaml')) {
                        $this->metaInformation = \Symfony\Component\Yaml\Yaml::parse($yamlFile);
                    } else {
                        throw new \Exception('No Yaml Parser!');
                    }
                } else {
                    $yamlSource = GeneralUtility::makeInstance('TYPO3\\CMS\\Form\\Mvc\\Configuration\\YamlSource');
                    $this->metaInformation = $yamlSource->load(array($yamlFile));
                }
            } else {
                throw new \Exception('No Yaml meta information found!');
            }
        }
    }

    /**
     * abstract the extension meta data import.
     *
     * @return void
     */
    protected function importExtEmConf()
    {
        // @codingStandardsIgnoreStart
        $EM_CONF = array();
        /** @var string $_EXTKEY */
        $_EXTKEY = $this->extensionName;
        include ExtensionManagementUtility::extPath($this->getExtensionName()) . 'ext_emconf.php';
        // @codingStandardsIgnoreEnd
        $this->title = $EM_CONF[$this->getExtensionName()]['title'];
        $this->description = $EM_CONF[$this->getExtensionName()]['description'];
        $this->version = $EM_CONF[$this->getExtensionName()]['version'];
        $this->author['name'] = $EM_CONF[$this->getExtensionName()]['author'];
        $this->author['email'] = $EM_CONF[$this->getExtensionName()]['author_email'];
        $this->author['company'] = $EM_CONF[$this->getExtensionName()]['author_company'];
    }

    /**
     * Returns an array of preview images
     * @return array
     */
    public function getAllPreviewImages()
    {
        $buffer = $this->metaInformation['screenshots'];
        if(count($buffer) > 0) {
            foreach($buffer as $key => $image) {
                // We need to use a real image file path, because in case of using a file
                // reference, a non admin backend user might not have access to the storage!
                $previewImage = GeneralUtility::getFileAbsFileName($image['file']);
                $previewImage = PathUtility::getAbsoluteWebPath($previewImage);
                $buffer[$key]['file'] = $previewImage;
            }
        }
        return $buffer;
    }

    /**
     * Return the TypoScript Config from the related file.
     *
     * @return string
     */
    public function getTypoScriptConfig()
    {
        if (file_exists($this->getTypoScriptConfigAbsPath()) && is_file($this->getTypoScriptConfigAbsPath())) {
            return file_get_contents($this->getTypoScriptConfigAbsPath());
        }
        return '';
    }

    /**
     * Calculates the relative path to the theme directory for frontend usage.
     *
     * @return string
     */
    public function getRelativePath()
    {
        if (ExtensionManagementUtility::isLoaded($this->getExtensionName())) {
            return ExtensionManagementUtility::siteRelPath($this->getExtensionName());
        }
        return '';
    }

    /**
     * Includes static template records (from static_template table) and static template files (from extensions) for the input template record row.
     *
     * @param array  $params Array of parameters from the parent class.  Includes idList, templateId, pid, and row.
     * @param object $pObj   Reference back to parent object, t3lib_tstemplate or one of its subclasses.
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

        // @todo resources Path / private Path
        $themeItem['constants'] .= LF.'themes.resourcesPrivatePath = '.$this->getRelativePath().'Resources/Private/';
        $themeItem['constants'] .= LF.'themes.resourcesPublicPath = '.$this->getRelativePath().'Resources/Public/';
        $themeItem['constants'] .= $this->getBasicConstants($params['pid']);
        $themeItem['constants'] .= LF.$this->getTypoScriptForLanguage($params, $pObj);

        $pObj->processTemplate(
            $themeItem,
            $params['idList'].',ext_theme'.str_replace('_', '', $this->getExtensionName()),
            $params['pid'], 'ext_theme'.str_replace('_', '', $this->getExtensionName()),
            $params['templateId']
        );
        //
        // Additional TypoScript for extensions
        if(count($extensions) > 0) {
            foreach($extensions as $extension) {
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
        if(count($features) > 0) {
            foreach($features as $feature) {
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

}
