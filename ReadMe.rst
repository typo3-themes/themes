.. image:: https://badge.waffle.io/typo3-themes/themes.png?label=ready&title=Ready 
 :target: https://waffle.io/typo3-themes/themes
 :alt: 'Stories in Ready'
.. include:: Documentation/Index.rst


========================================================================================================================
Themes
========================================================================================================================


About the THEMES Project
------------------------

The THEMES project was started with the idea in mind to provide a standardized way how the frontend output of TYPO3 should be created.
So the approach is similar to the Wordpress or Drupal world, where we have standardized datastructures and renderers since ages.

To achieve that goal and to be highly flexible configurable at the same time we build a set of extensions which provides the needed functionality:

+---------------------+-------------------------------------------------+ 
| Extensionkey        | purpose of this extension                       | 
+=====================+=================================================+ 
| themes              | UI in the backend for theme switching           |
+---------------------+-------------------------------------------------+ 
| themes_gridelements | provides datastructures to allow easy switching |
+---------------------+-------------------------------------------------+ 
| theme_bootstrap     | base package for bootstrap 3.x                  |
+---------------------+-------------------------------------------------+ 
| theme_foundation    | base package for foundation                     |
+---------------------+-------------------------------------------------+ 
| dyncss*             | less and scss rendering                         |
+---------------------+-------------------------------------------------+ 
| t3jquery            | standardized way of including javascript        |
+---------------------+-------------------------------------------------+ 

Having the base packages and also ready made themes like:

* theme_bootstrap_slate
* theme_bootstrap_mosaic
* theme_zurbink

It's easy and fast to start a new web project with less effort.

Combined with TYPO3 core features like distributions you can start the first structure of a webpage in minutes instead of hours.

Advantages for Developers
-------------------------

* Configuration can be put in a VCS as all is managed in Files
* Default set of backendlayouts
* if standard is kept easily layout is easily without data loss
* flexible and efficient to use
* often we used FLUID instead of complex TypoScript
* customizeable
* works with all 12 column grid frameworks we know like:

  + bootstrap
  + foundation
  + yaml

Advantages for integrators
--------------------------

* local testing possible
* atomic deployment as extension
* no TypoScript in the Database
* shared licenses:

  + please see a list of community shared licenses on https://github.com/typo3-themes/themes/wiki/Bought-Theme-Licenses

 
Advantages for Editors
----------------------

* easy to use
* flexible
* ...

========================================================================================================================
More resources
========================================================================================================================

typo3-themes.org
----------------

On www.typo3-themes.org you can view a list of THEMES available in the TYPO3 Extension Repository and soon agancies and certified integrators will be able to sell their own THEMES via that Plattform.

TYPO3 Theming und Distribution
------------------------------

This is the big documentation about themes, written by Thomas Deuling, Jo Hasenau and Kay Strobach.
The book is written in german, but we plan to release an english version taking care of the current changes and CMS 7 soon.


========================================================================================================================
Hint
========================================================================================================================

Please take a look into Documentation/Index.rst if there is no documentation above this line.

This is a current github limitation :(

https://github.com/github/markup/issues/172

========================================================================================================================
Build Status
========================================================================================================================

The following image indicates the build status.
Currently it's failing due to some codingstyle issues, to tackle them, the builds are tested with phpcs and the
TYPO3 codesniffers, stay tuned we will tackle them all!

.. image:: https://travis-ci.org/typo3-themes/themes.svg
   :target: https://travis-ci.org/typo3-themes/themes
