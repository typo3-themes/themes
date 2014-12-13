.. include:: ../../_IncludedDirectives.rst

====================
TypoScript-Constants
====================

**Table of content**

.. toctree::
	:maxdepth: 5
	:glob:

	Basic/Index
	Languages/Index
	
	

Default constants of the THEMES extension.

The namespace of the THEMES is named by ``themes.configuration`` and contained the following sections.

.. code-block:: typoscript

    themes.configuration {

        # basics (category theme)

        colors {
            # colors of the theme (category siteColors)
        }

        meta {
            defaults {
                # default meta data of the theme (category metaDefaults)
            }
            # meta data of the theme (category meta)
        }

        pages {
            # page ids of specific sites (category page)
        }

        container {
            # page ids of data container (category container)
        }
    }


Categories: theme, siteColors, meta, metaDefaults, page, container

Constants of the page category
------------------------------

============ ============ ====================================================
Name         Type         Label
============ ============ ====================================================
startsite    int+         Seite für die Home/Startseite
legal        int+         Seite für die AGB
privacy      int+         Datenschutz-Seite
imprint      int+         Impressum-Seite
contact      int+         Kontakt-Seite
sitemap      int+         Seite auf der die Sitemap zu finden ist
search       int+         Seite auf der sich die Suche befindet
login        int+         Seite auf der man sich einloggt
logout       int+         Seite auf der man sich ausloggt
intro        int+         Intro/Landing-Page einer Website
cookie       int+         Seite auf der man Cookies bestätigen muss
references   int+         Seite auf der die Referenzen angezeigt werden
============ ============ ====================================================


Constants of the container category
-----------------------------------

============ ============ ====================================================
Name         Type         Label
============ ============ ====================================================
frontendUser int+         Container für Frontend-User
news         int+         Container für News (bspw. die neue News-Extension)
address      int+         Container für Adressen (bspw. tt_address)
newsletter   int+         Container für gesammelte Newsletter-Adressen
menuTop      int+         Container für die Seiten des Footer-Menüs
menuFooter   int+         Container für die Seiten des Footer-Menüs
============ ============ ====================================================

.. code-block:: typoscript

    themes.configuration {
        container {
            frontendUser = 0
            news = 0
            address = 0
            newsletter = 0
            menuTop = 0
            menuFooter = 0
        }
    }



