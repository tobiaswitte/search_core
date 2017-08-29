.. highlight:: bash
.. _helpers:

Helpers
=======

Helpers are little scripts or classes that help with common work regarding search integrations.

The following helper are currently provided:

.. _helpers_dictionary_openthesaurus_to_synonyms:

Dictionary - Openthesaurus to synonyms
--------------------------------------

This helper will convert text files downloaded from https://www.openthesaurus.de/about/download into
usable synonym format for Elasticsearch and Solr.

The helper is available as command::

    ./typo3/cli_dispatch.phpsh extbase dictionary:openthesaurustosynonyms --language de typo3temp/ext/search_core/synonyms.txt

Useful to auto generate synonyms without need to manage them on your own.
