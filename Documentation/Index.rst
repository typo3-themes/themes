========================================================================================================================
Documentation for Themes
========================================================================================================================

:Author: Kay Strobach
:Mail:   typo3@kay-strobach.de


.. contents:: Table of Contents



Understanding Themes
====================

Basicly the EXT:Themes allows to include a static stylesheet into a so called "root template". This way it's easily
possible to allow multiple updateable website themes in a single TYPO3 instance.

As all the themes are packaged as TYPO3 extensions updates can be easily deployed.


Technical Background of Themeselector in EXT:Themes
===================================================

Technically this is solved with some hooks, extbase repositories and one XClass to include the modified TSConfig.


Structure of a theme
--------------------

A theme is basicly a set of TYPOScript files stored in an extension with some additional meta data.

.. table:: Minimum set of files for a theme

   =================================  ======================================================================
     File                              Function of the file
   =================================  ======================================================================
   ext_emconf.php                      Needed for every extension in TYPO3
   ext_icon.gif True                   Icon for the extensionmanager
   Configuration/Theme/constants.ts    contains constants to easily configure a theme
   Configuration/Theme/setup.ts        contains the needed TYPOScript to render the frontend
   Configuration/Theme/tsconfig.ts     contains the PageTS to configure the pagebranch of a selected theme
   =================================  ======================================================================

Additionally there are some files, which are use the achieve some higher goals.

.. table:: Other needed files

   =================================  ======================================================================
     File                              Function of the file
   =================================  ======================================================================
   Resources/Private/*                 contains resources, which are not served to the user
   Resources/Public/*                  contains resources, which are normally served to the user
   Documentation/*                     contains theme documentation
   Configuration/t3jquery.txt          contains the t3jquery configuration
   ext_tables.php                      usually contains backwards compat stuff to use a theme standalone
   Configuration/TypoScript            used for compatibility with ext_tables.php
   =================================  ======================================================================


Make your own theme
===================

To make your own theme you have to choose a templating engine.
Additionally you may use ext:themes_builder to generate the structure with just a bunch of clicks


Compatibility
=============

