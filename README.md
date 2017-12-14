# WPML Category and Tag Migration Assistant
This utility assists with completing **missing post categories** and **tags** (*taxonomies*) within [WordPress](https://www.wordpress.org/) after migrating from [qTranslate](https://wordpress.org/plugins/qtranslate/) or [qTranslate-X](https://wordpress.org/plugins/qtranslate-x/) to [WPML](https://wpml.org/) (*WordPress Multilingual Plugin*).

## Prerequisites
Make sure to install the [qTranslate X Cleanup and WPML Import](https://wordpress.org/plugins/qtranslate-to-wpml-export/) plugin within WordPress and consult the following instruction in order to import qTranslate/qTranslate-X translations: https://wpml.org/documentation/related-projects/qtranslate-importer/

I also assume that you already translated all taxonomies (*categories and tags*) names and followed this naming schema ``slugName-languageShortcode`` (*2 letter [ISO 639-1 code](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes), e.g. ``en``  or ``de``*).

Example:
German slug: ``linux``
English slug: ``linux-**en**``

![Translation example](https://raw.githubusercontent.com/stdevel/wpml-migration-assistant/master/wpml_assistant/translation.jpg "Translation example")

## How it works
This utility basically does the following:
1. It iterates through all posts of the given source language and discovers associated categories and tags and translations
2. In addition, it tries to find translations of linked taxonomies - this requires that you already translated all taxonomies and followed the postfix ``slugName-languageShortcode``
3. Afterwards it maps detected translations to translated taxonomies and increases counters

The progress is displayed in a table - so you can check-out and verify translated information. If everything is finished, you should see outputs like "``Already in category #xxx``" and "``Already has tag #xxx``" next to every translated post.

## Installation
Simply copy the ``wpml_assistant`` folder to your web server, e.g. via SFTP or SSH. Access the folder with your web browser and fill the form with the following information:
* Database server hostname
* WordPress database name
* Username
* Password
* Source language (*2 letter ISO 639-1 code, e.g. ``en`` or ``de``*)
* Target language (*2 letter code*)

**Disclaimer:** I also assume that you have created a **valid backup** of your WordPress database. This is an ugly script (*you really don't want to see the source code*) I wrote in a hurry just to get shit done - so things might go wrong on your installation. So - don't blame me for living on the edge: you have been warned. 🤷

![My database is broken](https://raw.githubusercontent.com/stdevel/wpml-migration-assistant/master/wpml_assistant/databaenerys.jpg "My database is broken!")
