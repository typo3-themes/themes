==============================
themes.configuration.container
==============================

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
