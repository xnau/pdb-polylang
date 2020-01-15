=== PDb4PLL ===
Contributors: Pierre Fischer
Tested up to: 5.3.2
Requires at least: 5.0
License: GPLv3
License URI: https://wordpress.org/about/gpl/

This plugin enables a multilingual operative mode of the participants-database plugin with Polylang.

== Description ==

The plugin PLL4PDb enables the plugin participants-database to run in a multilingual environment where polylang is the multilingual plugin being used.

With PLL4PDb and polylang you will be able to turn participants-database in a multilingual plugin. The benefits will be:
	- the support of versions in different languages of the various pages used by participants-database,
	- the management of the translations of each string entered in the backend.
	
1- Pages in different languages
===============================
In a website using participants-database and polylang, you are able to have as many "thanks page", as many "single record pages", as many "participant record pages" and as many "private link request pages" as languages you set up in polylang.
For instance, in a trilingual site you will have the "thanks page" in english, the "thanks page" in french and the one in german.
PLL4PDb enables participants-database to run in such configurations and to deal with the various versions of all the above pages.

In the backend of participants-database, where the plugin asks you for entering the slugs of those pages, just take any of your "thanks page" for the "thanks page", any of your "single record pages" for the "single record page" and so on.
Don't worry. PLL4PDb will make sure that participants-database always selects the right page when issuing a link to the special pages here above : this link will always be the one to the appropriate page in the current langage defined by polylang.

2- Multilingual strings
=======================
PLL4PDb will also manage the translations of all the strings used to configure the backend of participants-database or to set up the database.

With PLL4PDb, each string entered in the backend will be treated as a multilingual string. A multilingual string is a string that contains several values to be displayed in different languages.

The syntax of a multilingual string is the following:

		[:x1]String value 1[:x2]String value 2[:x3]....
where 
  x1, x2... are language slugs (ie 2 letters codes of languages) or an empty string,
  [:xi] introduces the value (up to the next [:xx] or the end of the whole string) that will be displayed when the current language is xi,
  [:] is a special case which introduces a default display value when there is no translation in the multilingual string for the current language.
	
Example : "[:fr]Maison[:de]Haus[:en]House[:]Casa"
	
	This string  will be displayed in the frontend as "Maison" when the current language is french(fr), as "House" when the current language is english (en) and as "Casa" when the current language is none of french, german or english.

More precisely, the way PLL4PDb processes each multilingual string before it is displayed is the following:
	- the value displayed is the value introduced by [:xc] when
		- the string contains such a value and the current language is xc,
	- the value displayed is the one introduced by [:] when
		- the string contains such a default value and no value for the current language,
	- the value displayed is the entire string when 
		- there is no current language defined (see below),
		- or the string is incorrect, ie contains no [:xi] header,
		- or the string contains no header for the current language and no default header ([:]).
	
		
Recommendation
--------------
In the backend of participants-database it is possible to select the language in which all the strings entered as parameters are displayed. This is usefull to show the "french configuration" of your plugin or the english one or ...
Polylang also provides a "Show all languages" mode in the backend where the strings are displayed in all available languages.
In this mode, the multilingual strings are displayed entirely. We highly recommend to select this mode each time you enter or modify a string in the backend. If you don't, just after you have entered a multilingual string, the string displayed as a feedback of your input will possibly be just a part of it, which can be quite confusing.

Special case
------------
When a string entered in the backend of participants-database is supposed to be a structured one following a particular syntax, it may contain several multilingual substrings. For instance the following string is a valid option associated to a dropdown field element, in a multilingual environment where two languages are used (english and french).

	"[:en]Yes[:fr]Oui::1, [:en]No[:fr]Non::0"

This string contains two multilingual strings, one for each of the "value titles" it consists of.

Note
----
In other plugins that are compatible with polylang, the translations of the strings are managed by polylang itself. In the backend, polylang provides a list of all strings defined by such a plugin with already associated (or missing) translations. Translationsranslations are entered in the configuration part of polylang.
We haven't chosen that approach with participants-database and have preferred using multilingual strings. One of the advantage of our approach is that the user is always in the appropriate context when he enters a string and its various translations. Nevertheless there might be a drawback when the strings become very long.

For information, the syntax of our multilingual strings is similar to the one used by the multilingual plugin Q-translate-X.

Any comment or suggestion is welcome.

== Changelog ==

= 1.1 =
initial release