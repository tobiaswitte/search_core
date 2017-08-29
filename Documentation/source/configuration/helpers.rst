.. highlight:: typoscript
.. _configuration_options_helpers:

Helpers
=======

Helpers are configured below ``plugin.tx_searchcore.settings.helpers``::

    plugin {
        tx_searchcore {
            settings {
                helpers {
                    // ...
                }
            }
        }
    }

.. _synonyms_target:

dictionary.converter.synonyms.<language>.target
-----------------------------------------------

    Defines the target file to write output to for generated synonyms for defined language.

    Example::

        dictionary.converter.synonyms.de.target = typo3temp/ext/search_core/synonyms.de.txt
