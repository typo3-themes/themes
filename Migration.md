# Themes Migration

## Version 9.1.0

### Language configuration by site configuration

This version uses the information of the site configuration for configuring the language menu. This means you need a well configured site configuration and you can get rid of some old TypoScript configuration.


Which configuration can be removed from your theme:

*   The `availableLanguages` setting must be completely removed. This setting/parameter is not longer available in the LanguageMenu widget. This configuration is solved simply by activating or deactivating languages in your site configuration.
*   The `currentLanguageUid` setting is no longer required and will be fetched immediately from the *TSFE*.
*   The default values for `defaultLanguageIsoCodeShort`, `defaultLanguageLabel` and `defaultLanguageFlag` are no longer required and will be read from the site configuration.
