<?php
/**
 * Plugin Name: Series Drop-down
 * Plugin URI: 
 * Description: Creates a new taxonomy called "series" that allows you to tie posts together in a series, displaying members with a widget.
 * Version: 0.1
 * Author: Catherine Ross
 * Author URI: http://pandammonium.org/
 *
 * This plug-in is based heavily on the Series plug-in by Justin Tadlock.
 * The difference is that this version of it has the option to display the
 * posts in a series as a drop-down list as well as an ordinary list.
 *
 * Series Drop-down is a plugin created to allow users to easily link posts together
 * by using a custom WordPress taxonomy. By using a taxonomy, we're
 * making use of the built-in WordPress functions.  This lets WordPress
 * do all the dirty work, while we just sit back and enjoy adding posts to
 * a series.  This can be particularly useful if you write several posts 
 * spanning the same topic and want them tied together in some way.
 *
 * @copyright 2010
 * @version 0.1
 * @author Catherine Ross
 * @link 
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package Series
 */

/**
 * Yes, we're localizing the plugin.  This partly makes sure non-English
 * users can use it too.  To translate into your language use the
 * en_EN.po file as as guide.  Poedit is a good tool to for translating.
 * @link http://poedit.net
 *
 * @since 0.1
 */
load_plugin_textdomain( 'series', false, '/series' );

/**
 * Make sure we get the correct directory.
 * @since 0.1
 */
if ( !defined( 'WP_CONTENT_DIR' ) )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( !defined( 'WP_PLUGIN_DIR' ) )
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

/**
 * Define constant paths to the plugin folder.
 * @since 0.1
 */
define( SERIES_DIR, WP_PLUGIN_DIR . '/series-dropdown' );

/**
 * Load required files.
 * @since 0.1
 */
require_once( SERIES_DIR . '/template-tags.php' );
require_once( SERIES_DIR . '/shortcodes.php' );

/**
 * Creates our taxonomy when 'init' is fired.
 * @since 0.1
 */
add_action( 'init', 'create_series_taxonomy', 0 );

/**
 * Register series widgets.
 * @since 0.1
 */
add_action( 'widgets_init', 'series_register_widgets' );

/**
 * Register the series taxonomy.
 * @uses register_taxonomy()
 *
 * @since 0.1
 */
function create_series_taxonomy() {
	$structure = get_option( 'permalink_structure' );
	if ( empty( $structure ) )
		$args = array( 'hierarchical' => false, 'label' => __('Series', 'series'), 'query_var' => 'series', 'rewrite' => false );
	else
		$args = array( 'hierarchical' => false, 'label' => __('Series', 'series'), 'query_var' => 'series', 'rewrite' => array( 'slug' => 'series' ) );

	register_taxonomy( 'series', 'post', $args );
}

/**
 * Register the Series plugin widgets.
 * @uses register_widget() Registers individual widgets.
 * @link http://codex.wordpress.org/WordPress_Widgets_Api
 *
 * @since 0.1
 */
function series_register_widgets() {
//	require_once( SERIES_DIR . '/series-dropdown-widget.php' );
	require_once( SERIES_DIR . '/series-dropdown-related-widget.php' );
//	register_widget( 'Series_Dropdown_Widget_Series' );
	register_widget( 'Series_Dropdown_Widget_Related' );
}

?>