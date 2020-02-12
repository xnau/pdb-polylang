=== PDb4PLL ===
Contributors: Pierre Fischer
Stable tag: 1.0.0
Tested up to: 5.3
Requires at least: 4.6

This plugin enables a multilingual operative mode of the participants-database plugin with Polylang.

== Description ==

The plugin PLL4PDb enables the plugin participants-database to run in a multilingual environment where polylang is the multilingual plugin being used.

With PLL4PDb participants-database will be able to:
	- have as many versions of its "special pages" as languages managed by polylang,
	- provide the user with the possibility of entering parameter strings in the backend in different langages and display those strings according the current (or default) language set by polylang.
	
1- Pages in different languages
===============================
In a website using participants-database and polylang, you are able to have as many "thanks page", as many "single record pages", as many "participant record pages" and as many "private link request pages" as languages you set up in polylang.
For instance, in a trilingual site you will have the "thanks page" in english, the "thanks page" in french and the one in german.
PLL4PDb enables participants-database to run in such configurations and to deal with the various versions of all the above "special pages".

In the backend of participants-database, where the plugin asks you for entering the slugs of those pages, just take any of your "thanks page" for the "thanks page", any of your "single record pages" for the "single record page" and so on.
Don't worry. PLL4PDb will make sure that participants-database always selects the right page when issuing a link to the special pages here above : this link will always be the one pointing to the appropriate page in the current langage set by polylang or, when this one is not defined, by the default language of polylang. 

2- Multilingual strings
=======================
PLL4PDb will also enable the management of the various translations of each of the strings entered in the backend of participants-database as well as the automatic selection of the appropriate translation when such strings are displayed.

Unlike what happens with other plugins when they are used with polylang and for which the translations of strings are stored and managed by polylang itself, the translations of the strings entered in the backend of participants-database are jointly managed by this plugin and PLL4PDb.
With PLL4PDb, each string entered in the backend of participants-database (and stored by it) is treated as a multilingual string, ie a string that may contain several display values.

A multilingual string is a string that may contain one or several language-dependant substrings which are displayed only for a given value of the current (or default) language as set by polylang. Its syntax is very close to the one used by the multilingual plugin QTranslate-X :
	
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
		
More precisely, the way PLL4PDb processes each multilingual string before it is displayed is the following:
	a- When the "current language" has been set by PLL (always in the frontend, sometimes in the backend):
		all the substrings corresponding to a language different from the current one are removed before
		the string is displayed; headers [:xx] are also removed.
		
	b- When there is no current language defined by polylang (this may happen in the backend only, when polylang is configured to "Show all languages"):
		the "default language" of polylang is taken instead of the "current language" to process the string as in a-.
		
Notices :
	1- A string without any language dependant substring is always displayed without any modification
	2- Multilingual string that are displayed in the backend of participants database to be edited by the user are also always displayed without any modification.


Other examples
--------------
With PLL4PDb, multilingual strings may also be used to enter complex parameters, such as the option associated with a dropdown field element defining a field of the database :
	"[:en]Yes[:fr]Oui[:]::1, [:en]No[:fr]Non[:]::0" 
defines a field whose stored value is 0 or 1 depending of the selected display value (0 stored when "No" in english or "Non" in french is selected, 1 stored when "Yes" or "Oui is selected).
		

Warning (Note to software developpers of participants-database)
---------------------------------------------------------------
According to the above syntax of a multilingual string, a language dependant substring in such a string can terminate with the end of the whole string. This simplifies the input of multilingual strings for the user.
	For instance entering "[:en]House[:fr]Maison" is simpler and quicker than entering "[:en]House[:fr]Maison[:]"
	
Nevertheless it must be noted that this simplification is only acceptable if all the translations performed by PLL4PDb apply to parameters whose value is a multilingual string as it was input by the user. A translation request with a parameter consisting of a multilingual string concatenated to another string could produce incorrect and unpredictable results.
	For instance, with $mls being a multilingual string whose value is "[:en]House[:fr]Maison" we shouldn't write:
		echo apply_filters('pdb-translate_string','<div>'.$mls.'</div>')
	But we must write instead:
		echo '<div>'.apply_filters('pdb-translate_string',$mls).'</div>')
	to avoid incorrect results in the case the language is english.

Note
----
In other plugins that are compatible with polylang, the translations of the strings are managed by polylang itself. In the backend, polylang provides a list of all strings defined by such a plugin with already associated (or missing) translations. Translations are entered in the configuration part of polylang.
We haven't chosen that approach with participants-database and have preferred using multilingual strings. One of the advantage of our approach is that the user is always in the appropriate context when he enters a string and its various translations. Nevertheless our approach may become painful when the strings become very long, in particular when there are a lot of languages to deal with.
Any comment or suggestion is welcome and should be sent to erpiu@oxilo.eu
