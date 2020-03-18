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
 *
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
