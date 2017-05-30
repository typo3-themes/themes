Understanding Themes
====================

Basicly the EXT:Themes allows to include a static stylesheet into a so called "root template". This way it's easily
possible to allow multiple updateable website themes in a single TYPO3 instance.

As all the themes are packaged as TYPO3 extensions updates can be easily deployed.

Technically this is solved with some hooks, extbase repositories and one XClass to include the modified TSConfig.