.. include:: ../../_IncludedDirectives.rst

========
Variants
========

With *variants* you're able to define different variants of a content element.

Backend
=======
Backend-Definitions are located in the file ``themes.content.variants.pagets`` which must be saved in ``theme_bootstrap_slate/Configuration/PageTS/Library`` directory.


Frontend
========
Frontend-Definitions are located in the file ``lib.content.cssMap.variants.setupts`` which must be saved in ``theme_bootstrap_slate/Configuration/PageTS/Library`` directory.


Suggestions for internal identifier
===================================
In order to receive a fully switchable Theme, you should use generally identifier for your Theme variants. Here you will find a set of already used identifier and their usecases:

========================== =============================================================================================
Identifier                 Description
========================== =============================================================================================
framed                     Framed (for example a *well* in Bootstrap)
framed-small               Framed (small, for example a *well-sm* in Bootstrap)
framed-large               Framed (large, for example a *well-lg* in Bootstrap)
page-header                Page-Header
wide-screen                Wide-Screen (for example a *jumbotron* in Bootstrap)
alert-success              Alert-Success
alert-info                 Alert-Info
alert-warning              Alert-Warning
alert-danger               Alert-Error
font-color-primary         Uses the primary color for defined text parts within the content element
font-color-secondary       Uses the secondary color for defined text parts within the content element
background-color-primary   Uses the primary color as background-color for defined areas within the content element
background-color-secondary Uses the secondary color as background-color for defined areas within the content element
========================== =============================================================================================
