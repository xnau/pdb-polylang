=== PDb Polylang Adapter ===
Contributors: Pierre Fischer
Stable tag: 1.0.0
Requires: 5.3
Tested up to: 5.3.2
Tags: Participants Database, multilingual, Polylang
Requires at least: 4.6
Requires PHP: 5.6
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Allows the plugin Participants Database to be used in a multilingual environment managed by the plugin Polylang.

== Description ==

The plugin PDb Polylang Adapter is an add-on to the plugin Participants Database that allows it to be used in a multilingual environment based on Polylang.  With PDb Polylang Adapter you will be able to describe the various fields of your database in several languages and generate user interfaces to access this database according to the language set by Polylang.

With Pdb Polylang Adapter the plugin Participants Database will be able to:
	- deal with as many versions of its "special pages" as languages managed by "Polylang",
	- provide the user with the possibility of entering parameter strings in the backend in different langages and display those strings according the current (or default) language set by Polylang.
	
1- Pages in different languages
===============================
Polylang is a multilingual plugin of the class "one language per post". Therefore, when using Polylang and Participants Database, you will have as many "thanks page", as many "single record pages", as many "participant record pages" and as many "private link request pages" as languages you set up in Polylang.
For instance, in a trilingual site you will have the "thanks page" in english, the "thanks page" in french and the one in german.
In such configurations, the plugin Pdb Polylang Adapter will allow Participants Database to run without any problem and to manage all the language dependant versions of all its "special pages".

In the backend of Participants Database, where the plugin asks you for entering the slug of a special page A, for instance the slug of the "thanks page", just enter the slug of any of your special pages A, for instance the slug of the "thanks page" in german.
Don't worry. Pdb Polylang Adapter will make sure that Participants Database always selects the right page when issuing a link to a special page : this link will always be the one pointing to the appropriate page in the current langage set by Polylang or, when this one is not defined, by the default language of Polylang. 

2- Multilingual strings
=======================
The PDB Polylang Adapter will also enable the management of the various translations of each of the strings entered in the backend of the plugin Participants Database as well as the automatic selection of the appropriate translation when such strings are displayed.

Unlike what happens with other plugins when they are used with Polylang and for which the translations of strings are stored and managed by Polylang itself, the translations of the strings entered in the backend of Participants Database are jointly managed by this plugin and the PDB Polylang Adapter.
With PDb Polylang Adapter, each string entered in the backend of Participants Database (and stored by it) is treated as a multilingual string, ie a string that may contain several display values.

A multilingual string is a string that may contain one or several language-dependant substrings which are displayed only for a given value of the current (or default) language as set by Polylang. Its syntax is very close to the one used by the multilingual plugin QTranslate-X :
	
	Language-neutral text[:x1]Text when language is x1[:x2]Text when x2[:]another language-neutral text[:x1]another text when x1.....
where 
  x1, x2... are language slugs (ie 2 letters codes of languages) or an empty string,
  [:xi] introduces a substring (up to the next [:xx], [:] or the end of the whole string) 
  that will be displayed only when the current language is xi,
  [:] is a special case which is used to terminate a language dependant substring.
	
Examples :
	[:fr]Maison à[:de]Haus in[:en]House in[:]Paris
	will be displayed :
		"Maison à Paris" when the current language is fr
		"Haus in Paris" when the current language is de
		and "House in Paris" when the current language is en.
		
More precisely, the way PDb Polylang Adapter processes each multilingual string before it is displayed is the following:
	a- When the "current language" has been set by Polylang (always in the frontend, sometimes in the backend):
		all the substrings corresponding to a language different from the current one are removed before
		the string is displayed; headers [:xx] are also removed.
		
	b- When there is no current language defined by Polylang (this may happen in the backend only, when Polylang is configured to "Show all languages"):
		the "default language" of Polylang is taken instead of the "current language" to process the string as in a-.
		
Notices :
	1- A string without any language dependant substring is always displayed without any modification
	2- Multilingual string that are displayed in the backend of Participants Database to be edited by the user are also always displayed without any modification.


Other examples
--------------
With PDb Polylang Adapter, multilingual strings may also be used to enter complex parameters, such as the option associated with a dropdown field element defining a field of the database :
	"[:en]Yes[:fr]Oui[:]::1, [:en]No[:fr]Non[:]::0" 
defines a field whose stored value is 0 or 1 depending of the selected display value (0 stored when "No" in english or "Non" in french is selected, 1 stored when "Yes" or "Oui" is selected).
		
Note
----
In other plugins that are able to run with polylang, the translations of the strings are managed by Polylang itself. In the backend, Polylang provides a list of all strings defined by such a plugin with already associated (or missing) translations. Translations are entered in the configuration part of Polylang.
We haven't chosen that approach with Participants Database and have preferred using multilingual strings. One of the advantage of our approach is that the user is always in the appropriate context when he enters a string and its various translations. Nevertheless our approach may become painful when the strings become very long, in particular when there are a lot of languages to deal with.
Any comment or suggestion is welcome and should be sent to erpiu@oxilo.eu or support@xnau.com

== Installation ==

1. Make sure the plugins Polylang and Participants Database (version 1.9.5.9 or higher) are already installed and activated.
2. Upload the plugin files to the `/wp-content/plugins/pdb-polylang-adapter` directory, or install the plugin through the WordPress plugins screen directly.
3. Activate the plugin through the 'Plugins' screen in WordPress

== Changelog ==

= 1.0.0 =
* Initial version

== Upgrade Notice ==


