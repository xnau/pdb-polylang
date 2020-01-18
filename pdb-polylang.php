<?php

/**
 * PLL4PDb
 *
 * @package           PLL4PDb
 * @author            Pierre Fischer
 * @copyright         2020 Pierre Fischer
 * @license           GPL3
 * @version           1.1
 *
 * @wordpress-plugin
 * Plugin Name:       PLL4PDb
 * Description:       Enables the plugin participants-database (PDb) to run in a multilingual environment with polylang (PLL). Requires the Polylang plugin and Participants Database version 1.9.5.5 or higher.
 * Version:           1.1
 * Requires at least: 5.2
 * Requires PHP:      5.6
 * Author:            Pierre Fischer
 * License:           GPL3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 */
/* Desciption : See readme.txt file

  Additional description:

  This plugin enables the plugin participants-database (PDb) to run in a multilingual environment with polylang (PLL).
  With PLL4PDb, supported languages are still defined by PLL. Selection of the current language is also still done by PLL.
  Nevertheless translations of strings aren't managed by PLL but jointly by participants-database and PLL4PDb.

  PLL4PDb is responsible for filtering:
  - strings to be displayed
  - and page IDs
  according to the current language defined by polylang.

  It defines two filters attached to the two filter hooks 'pdb-translate_string' and 'pdb-lang_page_id' used by participants-database :

  1 - Filter attached to 'pdb-translate_string'
  This filter will process its input string which is supposed to be a multilingual string.
  A multilingual string is a string that defines various display values for various languages.

  With PLL4PDb the format of a multilingual string is the following :
  [:x1]String value 1[:x2]String value 2[:x3]....
  where
  x1, x2... are language slugs (ie 2 letters codes of languages) or an empty string,
  [:xi] introduces the string (up to the next [:xx] or the end of the whole string)
  that will be displayed when the current language is xi,
  [:] is a special case which introduces a default display value when there is no translation in the
  multilingual string for the current language.

  Examples :
  [:fr]Maison[:de]Haus[:en]House[:es]Casa

  With PLL4PDb a dynamic string of PDb will be displayed as follows :
  a- When PLL has defined no current language, the whole string will be displayed.
  No current language is namely set when PLL wants to enable the display of strings in all available languages,
  for instance in the backend.
  PLL4PDb operates according to this rule.

  b- When a current language has been set by PLL,
  if the string is a multilingual string and contains a display value for the current language,
  this value is displayed.
  Otherwise, if the string is a multilingual string and contains a default display value, this default
  value is displayed.
  Otherwise the whole string is displayed.

  2- Filter attached to 'pdb-lang_page_id'
  With polylang each "logical page" of a website is in fact a set of several pages, one for each language supported.
  The filter attached to 'pdb-lang_page_id' receives the page Id of p as input: it returns the page Id of q, where q
  is the page of the set of p that corresponds to the current language defined by polylang.

 */

// exit if accessed directly
if ( !defined( 'ABSPATH' ) )
  exit;

class PLL4PDb {

  public function __construct()
  {
    add_filter( 'pdb-translate_string', array($this, 'translate_string') );
    add_filter( 'pdb-lang_page_id', array($this, 'language_page_id') );

    if ( !defined( 'PDB_MULTILINGUAL' ) ) {
      define( 'PDB_MULTILINGUAL', true );
    }
  }

  /**
   * sets the multilingual page id
   * 
   * @param int $in_id requested page id
   * @return int the language selected page id
   */
  public function language_page_id( $in_id )
  {
    if ( pll_current_language( 'slug' ) == '' )
      $out_id = $in_id;
    else
      $out_id = pll_get_post( $in_id );
    return $out_id;
  }

  /**
   * provides the currently selected language from a multilingual string
   * 
   * @param string $in_string the multilingual string
   * @return string
   */
  public function translate_string( $in_string )
  {
    $cur_lang = pll_current_language( 'slug' );

    if ( strpos( $in_string, '[:' ) === false ) {

      // not a multilingual string
      $translation = $in_string;
    } else { // s modifier is used with PCRE's to allow strings split over several lines
      $translation = preg_filter( '/.*\[:' . $cur_lang . '\](([^\[]|\[[^:])*)(\[:.*|$)/s', '$1', $in_string );

      if ( $translation == '' ) { /* There is no translation for the language - Search for a default value */
        $translation = preg_filter( '/.*\[:\](([^\[]|\[[^:])*)(\[:.*|$)/s', '$1', $in_string );

        if ( $translation == '' )
        /* There is no default value - Return the whole string */
          $translation = $in_string;
      }
    }

    return $translation;
  }

}

// initialize after Particiants Database is initialized
if ( class_exists( 'Participants_Db' ) ) {
  pll4pdb_initialize();
} else {
  add_action( 'participants-database_activated', 'pll4pdb_initialize' );
}

function pll4pdb_initialize()
{
  /**
   * Check for Polylang before instantiating the plugin class
   */
  if ( function_exists( 'pll_current_language' ) ) {

    new PLL4PDb();
  } else {

    add_action( 'admin_notices', 'pll4pdb_pll_missing_error' );
    add_action( 'admin_init', 'pll4pdb_deactivate_plugin' );
  }
}

function pll4pdb_deactivate_plugin()
{
  deactivate_plugins( plugin_basename( __FILE__ ) );
}

function pll4pdb_pll_missing_error()
{
  echo '<div class="notice notice-error is-dismissible"><p><span class="dashicons dashicons-warning"></span>' . __( 'The PLL4PDb plugin requres the PolyLang plugin. The Plugin has been auto-deactivated.', 'pll4pdb' ) . '</p></div>';
  if ( isset( $_GET['activate'] ) ) {
    unset( $_GET['activate'] );
  }
}
