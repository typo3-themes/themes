# Themes Change-Log



### 2019-05-23  Thomas Deuling  <typo3@coding.ms>

*   [TASK] Remove YAML parse, because it's already shipped by TYPO3 itself.
*   [TASK] Rise version for TYPO3 9.5 only.
*   [TASK] Remove ext_icon.png.
*   [TASK] Remove ext_autoload.php.
*   [TASK] Remove locallang_db.xml.
*   [TASK] Remove deprecated doc/manual.sxw.
*   [TASK] Remove command controller for creating Font Awesome 4 configuration - keep Themes simple!
*   [TASK] PHP code cleanup and remove @inject annotations.
*   [TASK] Refactor of the language menu widget.
*   [TASK] Remove static info tables dependency.
*   [TASK] Backend information for variants, behaviour and responsives are only visible if user is admin and debug mode is enabled.
*   [BREAKING] Remove logic for setup.txt/constants.txt/tsconfig.txt - only *.typoscript files will be recognized.
*   [BREAKING] Remove constant view helper including cache logic - please pass your constants by plugin settings for achieving better performance.



### 2018-10-29  Thomas Deuling  <typo3@coding.ms>

*   [FEATURE] Adding a files data processor for getting more file link information.

### 2018-09-19  Thomas Deuling  <typo3@coding.ms>

*   [BUGFIX] Fixing MySQL database definition for sys_template table.



## 2018-09-17  Release of version 8.7.6

### 2018-09-17  Thomas Deuling  <typo3@coding.ms>

*   [FEATURE] Adding data processor for header link in content elements.



## 2018-08-09  Release of version 8.7.5

### 2018-08-09  Thomas Deuling  <typo3@coding.ms>

*   [BUGFIX] Fixing adding page TypoScript for Theme features and extensions, when using extension templates.


## 2018-09-07  Release of version 8.7.4

### 2018-08-07  Thomas Deuling  <typo3@coding.ms>

*   [BUGFIX] Fixing theme selection icons in sys_template.



## 2018-07-14  Release of version 8.7.3

### 2018-07-13  Thomas Deuling  <typo3@coding.ms>

*   [BUGFIX] Fixing adding page TypoScript for Theme features and extensions.



## 2018-07-11  Release of version 8.7.2

### 2018-07-11  Thomas Deuling  <typo3@coding.ms>

*   [FEATURE] Adding new feature for selecting Theme features/extensions in order minimize loaded configuration by using the Theme. More information in Documentation/ExtensionsAndFeaturesInThemes.rst.

### 2015-08-02  Thomas Deuling  <typo3@coding.ms>

*   [TASK] Adding new content element wizard for buttoncontent
