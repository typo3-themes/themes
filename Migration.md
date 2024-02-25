# Themes Migration

## Version 12.0.0




## Version 9.1.0

### Language configuration by site configuration

This version uses the information of the site configuration for configuring the language menu. This means you need a well configured site configuration and you can get rid of some old TypoScript configuration.


Which configuration can be removed from your theme:

*   The `availableLanguages` setting must be completely removed. This setting/parameter is not longer available in the LanguageMenu widget. This configuration is solved simply by activating or deactivating languages in your site configuration.
*   The `currentLanguageUid` setting is no longer required and will be fetched immediately from the *TSFE*.
*   The default values for `defaultLanguageIsoCodeShort`, `defaultLanguageLabel` and `defaultLanguageFlag` are no longer required and will be read from the site configuration.

### Extension names of theme extensions

For performance reasons Themes tries to read only extensions which extension key starts with *theme_*. If you've named your theme extension with an different name, you need to modify the extension configuration (you might change or remove the *themeExtensionsStartWith* setting).

### TypoScript file extensions

The TypoScript file extension must be *typoscript* - for example *setup.typoscript*, *constants.typoscript*, *tsconfig.typoscript* - otherwise Themes won't load show up your theme!

### Constants-ViewHelper

The constants view helper is removed for achieving a better performance and less complexity. For migration just pass your constants by using `plugin.tx_themes.settings.`
