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


Make your own theme
===================

To make your own theme you have to choose a templating engine.
Additionally you may use ext:themes_builder.


Compatibility
=============

Technically this extension will be kept compatible to templavoila_framework.