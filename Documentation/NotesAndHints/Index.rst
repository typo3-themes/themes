Notes
=====

EXT:gridelements (Notes)
------------------------

EXT:gridelements/Classes/Backend/LayoutSetup.php

#denied elements
tx_gridelements.setup.<id>.excludeLayoutIds

#TSConfig winning
tx_gridelements.setup.<id>.overruleRecords

tx_gridelements.setup.tabs4 {
	# nur toplevel
	topLevelLayout = 1
	# xml des flexforms!
	flexformDS = FILE:EXT: ...

	icon =

	title = Tabs4 Title

	description = ich bin die tolle beschreibung

	frame = 12352345

	config {
		backend_layout {
			colCount = 2
			rowCount = 2
			rows {
				1 {
					columns {
						1 {
							name = 0x0
							colspan = 2
							colPos = 0
						}
					}
				}
				2 {
					columns {
						1 {
							name = 0x1
							colPos = 2
						}
						2 {
							name = 1x1
							colPos = 1
						}
					}
				}
			}
		}
	}
}

Backendlayouts
--------------

hide some layouts:
options.backendLayout.exclude = default_1, my_extension__headerLayout 

Dataprovider interface
TYPO3\CMS\Backend\View\BackendLayout\DataProviderInterface 

registrierung
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['BackendLayoutDataProvider']
[$_EXTKEY] = 'Classname';


Header Hook für PageModule
--------------------------
\TYPO3\CMS\Backend\Controller\PageLayoutController
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']
['drawHeaderHook'];
callUserFunction

extbase templateRootPaths
-------------------------
plugin.tx_simpleblog { 
	view { 
		templateRootPath = EXT:simpleblog/Resources/Private/Templates/ 
		templateRootPath >  templateRootPaths { 
			10 = fileadmin/simpleblog/templates 
			20 = fileadmin/special/simpleblog/templates
		} 
	}
}
 