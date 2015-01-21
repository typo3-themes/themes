..  Content substitution
    ...................................................
    Hint: following expression |my_substition_value| will be replaced when rendering doc.

.. |author| replace:: Thomas Deuling <typo3@coding.ms>, Jo Hasenau <info@cybercraft.de>, Kay Strobach <typo3themes@kay-strobach.de>
.. |extension_key| replace:: themes
.. |extension_name| replace:: THEMES
.. |version| replace:: 1.0.0
.. |version_typo3| replace:: TYPO3 6.2.x
.. |time| date:: %Y-%m-%d %H:%M

..  Custom roles
    ...................................................
    After declaring a role like this: ".. role:: custom", the document may use the new role like :custom:`interpreted text`. 
    Basically, this will wrap the content with a CSS class to be styled in a special way when document get rendered.
    More information: http://docutils.sourceforge.net/docs/ref/rst/roles.html

.. role:: code
.. role:: typoscript
.. role:: typoscript(code)
.. role:: ts(typoscript)
.. role:: php(code)
