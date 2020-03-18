<?php

/**
 * PDb Polylang Adapter
 *
 * @package           WordPress
 * @author            Pierre Fischer, xnau
 * @copyright         2020 Pierre Fischer
 * @license           GPL3
 * @version           1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       PDb Polylang Adapter
 * Description:       Allows the plugin Participants Database to be used in a multilingual environment managed by Polylang.
 * Version:           1.0.0
 * Requires at least: 5.3
 * Requires PHP:      5.6
 * Author:            Pierre Fischer
 * License:           GPL3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       pdb-polylang-adapter
 * Domain Path:       /languages
 */
/* Description : See readme.txt file

  Additional description:

  This plugin enables the plugin Participants Database to run in a multilingual environment with Polylang.
  With PDb Polylang Adapter, supported languages are still defined by Polylang. Selection of the current language is also still done by Polylang.
  Nevertheless translations of strings aren't managed by Polylang but jointly by Participants Database and this plugin.

  PDb Polylang Adapter is responsible for filtering:
  - strings to be displayed
  - and page IDs
  according to the current language defined by Polylang (or default language when the current one is not defined).

  It defines two filters attached to the two filter hooks 'pdb-translate_string' and 'pdb-lang_page_id' used by Participants Database :

  1 - Filter attached to 'pdb-translate_string'
  This filter will process its input string which is supposed to be a multilingual string.
  A multilingual string is a string that may contain various substrings that should be displayed only
  for given languages.

  With PDb Polylang Adapter the format of a multilingual string is close to the one used by QTranslate-X:

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

  More precisely, with the plugin PDb Polylang Adapter a dynamic string of Participants Database is displayed as follows :
  a- When the string doesn't contain any language dependant substring it isn't modified and is displayed as it is.

  b- The "current language" set by Polylang is selected. When there is no such current language - which may happen in the back end
  when polylang wants to "Show all languages" -, the so called "default language" of Polylang is selected instead.
  Then all the substrings corresponding to a language different from the selected one are removed before
  the string is displayed; the headers [:xx] are also removed.

  2- Filter attached to 'pdb-lang_page_id'
  With Polylang each "logical page" of a website is in fact a set of several pages, one for each language supported.
  The filter attached to 'pdb-lang_page_id' receives the page Id of p as input: it returns the page Id of q, where q
  is the page of the set of p that corresponds to the current language defined by Polylang or, when this one is not defined, by the
  default language of Polylang.

  Warning (Note to software developpers of Participants Database)
  ---------------------------------------------------------------
  According to the above syntax of a multilingual string, a language dependant substring in such a string can terminate with the end of the whole string. This simplifies the input of multilingual strings for the user.
  For instance entering "[:en]House[:fr]Maison" is simpler and quicker than entering "[:en]House[:fr]Maison[:]"

  Nevertheless it must be noted that this simplification is only acceptable if all the translations performed by PDb Polylang Adapter apply to parameters whose value is a multilingual string as it was input by the user. A translation request with a parameter consisting of a multilingual string concatenated to another string could produce incorrect and unpredictable results.
  For instance, with $mls being a multilingual string whose value is "[:en]House[:fr]Maison" we shouldn't write:
  echo apply_filters('pdb-translate_string','<div>'.$mls.'</div>')
  But we must write instead:
  echo '<div>'.apply_filters('pdb-translate_string',$mls).'</div>')
  to avoid incorrect results in the case the language is english.

 */

// exit if accessed directly
if ( !defined( 'ABSPATH' ) )
  exit;

class PDb_Polylang_Adapter {

  public function __construct()
  {
    /*
     * Participants Database places a handler on this filter at priority 20, so 
     * this one will be applied before that and essentially override it
     */
    add_filter( 'pdb-translate_string', array($this, 'translate_string'), 10 );
    add_filter( 'pdb-lang_page_id', array($this, 'language_page_id') );
  }

  /**
   * sets the multilingual page id
   * 
   * @param int $in_id requested page id
   * @return int the language selected page id
   */
  public function language_page_id( $in_id )
  {
    $lang = pll_current_language( 'slug' );

    if ( $lang === false )
      $lang = pll_default_language( 'slug' );

    return pll_get_post( $in_id, $lang );
  }

  /**
   * from a multilingual string provides the string corresponding to the current 
   * (or default) polylang language
   * 
   * @param string $in_string the multilingual string
   * @return string
   */
  public function translate_string( $in_string )
  {
    // paramter must be a string
    if ( !is_string( $in_string ) ) {
      if ( PDB_DEBUG ) {
        ob_start();
        var_dump($in_string);
        error_log(__METHOD__.' non-string parameter sent to pdb-translate_string filter: ' . ob_get_clean() );
//        error_log(__METHOD__.' trace: '.print_r(wp_debug_backtrace_summary(),1));
      }
      return $in_string;
    }
    
    if ( strpos( $in_string, '[:' ) === false )
      // not a multilingual string
      $translation = $in_string;
    else {
      $lang = pll_current_language( 'slug' ); // current language set by polylang

      if ( $lang === false ) {
        // May happen in the back end - select the default language in that case
        $lang = pll_default_language( 'slug' );
      }

      /*
       * Keep the substrings set for the selected language, get rid of the ones 
       * set for other languages and replace all '[:xx]' with '[:]'.At the end 
       * remove all the remaining '[:]'
       * a string with no language-dependant substring remains unchanged
       */
      $translation = preg_replace(
              array('/\[:' . $lang . '\](([^\[]|\[[^:])*)/s', '/\[:[a-z][a-z]\]([^\[]|\[[^:])*/s', '/\[:\]/'),
              array('[:]$1', '[:]', ''),
              $in_string );
    }

    return $translation;
  }

}

// initialize plugin only after all plugins are loaded
add_action( 'plugins_loaded', 'pdb_polylang_adapter_initialize' );

function pdb_polylang_adapter_initialize()
{
  /**
   * Check for Polylang and Participants Database before initializing
   */
  if ( function_exists( 'pll_current_language' ) && class_exists( 'Participants_Db' ) ) {
    // Load translations
    load_plugin_textdomain( 'pdb-polylang-adapter', false, basename( rtrim( dirname( __FILE__ ), '/' ) ) . '/languages' );
    // and instantiate the plugin class
    new PDb_Polylang_Adapter();
  } else {

    add_action( 'admin_notices', 'pdb_polylang_adapter_error' );
    add_action( 'admin_init', 'pdb_polylang_adapter_deactivate_plugin' );
  }
}

function pdb_polylang_adapter_deactivate_plugin()
{
  deactivate_plugins( plugin_basename( __FILE__ ) );
}

function pdb_polylang_adapter_error()
{
  echo '<div class="notice notice-error is-dismissible"><p><span class="dashicons dashicons-warning"></span>' . __( 'PDb Polylang Adapter requires both the Polylang and Participants Database plugins. At least one of them is missing. PDb Polylang Adapter has been auto-deactivated.', 'pdb-polylang-adapter' ) . '</p></div>';
  if ( isset( $_GET['activate'] ) ) {
    unset( $_GET['activate'] );
  }
}
