# Extensions and features in Themes

A Theme can support a lot of different extensions and features. The problem might be, that your theme often loads lot of extension and feature stuff, that isn't required in every usecase. Therefore we added an additional selection for such extension and feature stuff in the Sys-Template.


## Registering Extensions and Features

You can register new supported extensions and features by using Page-TypoScript of your Theme.

```
TCEFORM {
	sys_template {
		# Define some supported features
		tx_themes_features.addItems {
			ThemeBootstrap4_GoogleAnalytics = Google-Analytics
			ThemeBootstrap4_Piwik = Piwik
		}
		# Define some extensions
		tx_themes_extensions.addItems {
			ThemeBootstrap4_News = News
			ThemeBootstrap4_Fahrzeugsuche = Fahrzeugsuche
		}
	}
}
```

As you can see, we have two sections - `tx_themes_features` for the features and `tx_themes_extensions` for the extensions. This script simply adds some selection items in your TypoScript Root-Template Record. In this record you are now able to select the extensions and features as you need. Because this selections are based on the Root-Template of each website root, you are able to provide these settings only for the related website tree.

## Providing Extension and Feature configuration in your Theme

### Providing Features

For providing a feature in your theme, you simply need the following three files:

1.	`Configuration/PageTS/Features/IconsFontAwesome4/tsconfig.typoscript`
2.	`Configuration/TypoScript/Features/IconsFontAwesome4/constants.typoscript`
3.	`Configuration/TypoScript/Features/IconsFontAwesome4/setup.typoscript`

These files would need the following registration:

```
TCEFORM {
	sys_template {
		tx_themes_features.addItems {
			ThemeBootstrap4_IconsFontAwesome4 = Icons: FontAwesome 4
		}
	}
}
```

The registration key `ThemeBootstrap4_IconsFontAwesome4` explained:

*	`ThemeBootstrap4` represents the Theme extension key (upper camel case)
*	`IconsFontAwesome4` represents the feature folder within the Theme

### Providing Extensions

For providing an extension in your theme, you simply need the following three files:

1.	`Resources/Private/Extensions/News/PageTS/tsconfig.typoscript`
2.	`Resources/Private/Extensions/News/TypoScript/constants.typoscript`
3.	`Resources/Private/Extensions/News/TypoScript/setup.typoscript`

These files would need the following registration:

```
TCEFORM {
	sys_template {
		tx_themes_extensions.addItems {
			ThemeBootstrap4_News = News
		}
	}
}
```

The registration key `ThemeBootstrap4_IconsFontAwesome4` explained:

*	`ThemeBootstrap4` represents the Theme extension key (upper camel case)
*	`News` represents the extension folder within the Theme (based in Resources/Private/Extensions/)
