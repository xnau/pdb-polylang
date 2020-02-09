<?php

/**
 * PLL4PDb
 *
 * @package           WordPress
 * @author            Pierre Fischer, xnau
 * @copyright         2020 Pierre Fischer
 * @license           GPL3
 * @version           1.2
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
  A multilingual string is a string that may contain various substrings that should be displayed only
  for given languages.

  With PLL4PDb the format of a multilingual string is close to the one used by QTranslate-X:

  Language-neutral text[:x1]Text for language x1[:x2]Text for x2[:]another language-neutral text
  where
  x1, x2... are language slugs (ie 2 letters codes of languages) or an empty string,
  [:xi] introduces a substring (up to the next [:xx], [:] or the end of the whole string)
  that will be displayed only when the current language is xi,
  [:] is a special case which might be used to terminate a language dependant substring.

  Example :
  "[:fr]Maison à[:de]Haus in[:en]House in[:]Paris[:fr] Oui[:en] Yeah[:de]Hopla[:]!"
  will be displayed as "Maison à Paris Oui!" when the current language is fr
  as "Haus in Paris Hopla!" when the current language is de
  and as "House in Paris Yeah!" when the current language is en.

  More precisely, with PLL4PDb a dynamic string of PDb is displayed as follows :
  a- When PLL has defined no current language,
  the string isn't modified and is displayed as it is.
  No current language is namely set when PLL wants to enable the display of strings in all available languages,
  for instance in the backend.

  b- When a current language has been set by PLL,
  all the substrings corresponding to a language different from the current one are removed before
  the string is displayed; the headers [:xx] are also removed.
  Notice:
  According to the above process, normal strings (those that contain no language-dependant substrings)
  are always displayed entirely.

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
    /* 
     * Participants Database places a handler on this filter at priority 20, so 
     * this one will be applied before that and essentially override it
     */
    add_filter( 'pdb-translate_string', array($this, 'translate_string'), 10 );
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
    if ( pll_current_language( 'slug' ) == '' ) {
      
      $out_id = $in_id;
    } else {
      
      $out_id = pll_get_post( $in_id );
    }
    
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
    $lang = pll_current_language( 'slug' );

    if ( $lang === '' || strpos( $in_string, '[:' ) === false ) {

      // not a multilingual string
      $translation = $in_string;
    } else {

      /*
       * Keep the substrings set for the current language, get rid of the ones 
       * set for other languages and replace all '[:xx]' with '[:]'.At the end 
       * remove all the remaining '[:]'
       * a string with no language-dependant substring remains unchanged
       */
      $translation = preg_replace(
              array( '/\[:' . $lang . '\](([^\[]|\[[^:])*)/s', '/\[:..\]([^\[]|\[[^:])*/s', '/\[:\]/' ),
              array( '[:]$1', '[:]', '' ),
              $in_string );
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
