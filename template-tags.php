<?php
/**
 * Series Template Tags
 *
 * Several functions (template tags) exist within this file, which can be used 
 * within the theme.  These are pretty standard things we generally see used
 * with categories and tags.
 *
 * WordPress already has numerous built-in functions that handle most of the work 
 * required here.  As opposed to rewriting a ton of code, we should make use of those 
 * pre-coded functions as much as possible.  Reference the WordPress files 
 * /wp-inclues/taxonomy.php and /wp-includes/category-template.php when looking 
 * for things you can do.
 *
 * Get a term by the term slug:
 * $term = get_term_by( 'slug', $term_slug, 'series' );
 *
 * Get a term's description:
 * $description = term_description( $term_id, 'series' );
 *
 * Get the series terms for a post:
 * $terms = get_the_term_list( $post->ID, 'series', $before, $sep, $after );
 *
 * Display the series terms for a post:
 * the_terms( $post->ID, 'series', $before, $sep, $after )
 *
 * Get all series terms:
 * $terms = get_terms( 'series', $args );
 *
 * Get a specific series term:
 * $term = get_term( $series, 'series', $output, $filter );
 *
 * Get a specific series term link:
 * $link = get_term_link( $series, 'series' );
 *
 * Display or retrieve page title for series archive:
 * $title = single_series_title($prefix, $display );
 *
 * Retrieve series data given a series ID or series object:
 * $series = get_series( $series, $output, $filter );
 *
 * Retrieve series name based on series ID:
 * $seriesname = get_the_series_by_ID( $series_ID );
 *
 * Update series structure to old pre 2.3 from new taxonomy structure.
 * _make_series_compat( $series );
 *
 * @package Series
 */

/**
 * Update series structure to old pre 2.3 from new taxonomy structure.
 *
 * This function was added for the taxonomy support to update the new series
 * structure with the old series one. This will maintain compatibility with
 * plugins and themes which depend on the old key or property names.
 *
 * The parameter should only be passed a variable and not create the array or
 * object inline to the parameter. The reason for this is that parameter is
 * passed by reference and PHP will fail unless it has the variable.
 *
 * There is no return value, because everything is updated on the variable you
 * pass to it. This is one of the features with using pass by reference in PHP.
 *
 * @since 2.3.0
 * @access private
 *
 * @param array|object $series series Row object or array
 */
function _make_series_compat( &$series ) {
	if ( is_object( $series ) ) {
		$series->series_ID = &$series->term_id;
		$series->series_count = &$series->count;
		$series->series_description = &$series->description;
		$series->series_name = &$series->name;
		$series->series_nicename = &$series->slug;
		$series->series_parent = &$series->parent;
	} elseif ( is_array( $series ) && isset( $series['term_id'] ) ) {
		$series['series_ID'] = &$series['term_id'];
		$series['series_count'] = &$series['count'];
		$series['series_description'] = &$series['description'];
		$series['series_name'] = &$series['name'];
		$series['series_nicename'] = &$series['slug'];
		$series['series_parent'] = &$series['parent'];
	}
}

/**
 * Retrieve series data given a series ID or series object.
 *
 * If you pass the $series parameter an object, which is assumed to be the
 * series row object retrieved the database. It will cache the series data.
 *
 * If you pass $series an integer of the series ID, then that series will
 * be retrieved from the database, if it isn't already cached, and pass it back.
 *
 * If you look at get_term(), then both types will be passed through several
 * filters and finally sanitized based on the $filter parameter value.
 *
 * The series will converted to maintain backwards compatibility.
 *
 * @since 1.5.1
 * @uses get_term() Used to get the series data from the taxonomy.
 *
 * @param int|object $series series ID or series row object
 * @param string $output Optional. Constant OBJECT, ARRAY_A, or ARRAY_N
 * @param string $filter Optional. Default is raw or no WordPress defined filter will applied.
 * @return mixed series data in type defined by $output parameter.
 */
function &get_series( $series, $output = OBJECT, $filter = 'raw' ) {
	$series = get_term( $series, 'series', $output, $filter );
	if ( is_wp_error( $series ) )
		return $series;

	_make_series_compat( $series );

	return $series;
}

/**
 * Retrieve series name based on series ID.
 *
 * @since 0.71
 *
 * @param int $series_ID series ID.
 * @return string series name.
 */
function get_the_series_by_ID( $series_ID ) {
	$series_ID = (int) $series_ID;
	$series = &get_series( $series_ID );
	if ( is_wp_error( $series ) )
		return $series;
	return $series->name;
}

/**
 * Display or retrieve page title for series archive.
 *
 * This is useful for series template file or files, because it is optimized
 * for series page title and with less overhead than {@link wp_title()}.
 *
 * It does not support placing the separator after the title, but by leaving the
 * prefix parameter empty, you can set the title separator manually. The prefix
 * does not automatically place a space between the prefix, so if there should
 * be a space, the parameter value will need to have it at the end.
 *
 * @since 0.71
 *
 * @param string $prefix Optional. What to display before the title.
 * @param bool $display Optional, default is true. Whether to display or retrieve title.
 * @return string|null Title when retrieving, null when displaying or failure.
 */
function single_series_title($prefix = '', $display = true ) {
   global $wp_query;

   $taxonomy = get_query_var('taxonomy');
   $series_slug = get_query_var($taxonomy);
	if ( !empty($series_slug) ) {
      $term = get_term_by( 'slug', $series_slug, $taxonomy );
      if ( !$term === false ) {
         $my_series_name = apply_filters('single_series_title', get_the_series_by_ID($term->term_id));
         if ( !empty($my_series_name) ) {
            if ( $display ) {
               echo $prefix.strip_tags($my_series_name);
            } else {
               return strip_tags($my_series_name);
            }
         }
      }
   } //else if ( is_tag() ) {
      //return single_tag_title($prefix, $display);
   //}
}

/**
 * Produces a feed link for an individual series.
 *
 * @since 0.1
 */
function get_series_feed_link( $series_id, $feed = '' ) {
	$series_id = (int) $series_id;

	$series = get_series( $series_id );

	if ( empty( $series ) || is_wp_error( $series ) )
		return false;

	if ( empty( $feed ) )
		$feed = get_default_feed();

	$permalink_structure = get_option( 'permalink_structure' );

	if ( '' == $permalink_structure ) {
		$link = trailingslashit( get_option( 'home' ) ) . "?feed=$feed&amp;series=" . $series_id;
	} else {
		$link = get_series_link( $series_id );
		if( $feed == get_default_feed() )
			$feed_link = 'feed';
		else
			$feed_link = "feed/$feed";

		$link = trailingslashit( $link ) . user_trailingslashit( $feed_link, 'feed' );
	}

	$link = apply_filters( 'series_feed_link', $link, $feed );

	return $link;
}

/**
 * Checks if the current page is a series archive.  This function
 * will return true|false depending on whether the value is true.
 * If a $slug is entered, we'll check against that.  Otherwise, we 
 * only check if the current page is a series.
 *
 * @since 0.1
 */
function is_series( $slug = false ) {
	global $wp_query;

	$tax = $wp_query->get_queried_object();
	$taxonomy = get_query_var( 'taxonomy' );

	if ( $slug && $slug == $tax->slug )
		return true;
	elseif ( $slug && $slug !== $tax->slug )
		return false;

	if ( $taxonomy == 'series' )
		return true;

	return false;
}

/**
 * Check if the current post is within any of the given series.
 *
 * The given series are checked against the post's series' term_ids, names and slugs.
 * Series given as integers will only be checked against the post's series' term_ids.
 *
 * @uses is_object_in_term()
 *
 * @since 0.1
 * @param int|string|array $series. Series ID, name or slug, or array of said.
 * @param int|post object Optional.  Post to check instead of the current post.
 * @return bool True if the current post is in any of the given series.
 */
function in_series( $series, $_post = null ) {
	if ( empty( $series ) )
		return false;

	if ( $_post )
		$_post = get_post( $_post );
	else
		$_post =& $GLOBALS['post'];

	if ( !$_post )
		return false;

	$r = is_object_in_term( $_post->ID, 'series', $series );

	if ( is_wp_error( $r ) )
		return false;

	return $r;
}

/**
 * Displays a list of posts by series ID.
 * $args['series'] must be input for the list to work.
 *
 * @uses get_posts() Grabs an array of posts to loop through.
 *
 * @since 0.1
 * @param array $args
 * @return string $out
 */
function series_list_posts( $args = array() ) {
	global $post;

	$defaults = array(
		'series' => '',		// Series args
		'link_current' => false,
		'order' => 'DESC',		// get_posts() args
		'orderby' => 'ID',
		'post_type' => 'post',
		'exclude' => '',
		'include' => '',
		'numberposts' => -1,
      'display' => 'list', // or 'drop-down'
		'echo' => true,
	);

	$args = apply_filters( 'series_list_posts_args', $args );

	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	if ( !$series )
   {
      if ( strcmp( $args['display'] == 'drop-down' ) == 0 )
      {
		   echo '<option>' . _e('No series term was input.', 'series') . '</option>';
		   return false;
      }
      else // default = 'list'
      {
		   echo '<li>' . _e('No series term was input.', 'series') . '</li>';
		   return false;
      }
	}

	$series_posts = get_posts( $args );

	if ( !$series_posts )
   {
      if ( strcmp( $args['display'], 'drop-down' ) == 0 )
      {
		   echo '<option>' . _e('No posts in this series.', 'series') . '</option>';
		   return false;
      }
      else // default = 'list'
      {
         echo '<li>' . _e('No posts in this series.', 'series') . '</li>';
         return false;
      }
	}

	foreach ( $series_posts as $serial ) :

      if ( strcmp( $args['display'], 'drop-down' ) == 0 )
      {
         if ( $serial->ID == $post->ID && !$link_current )
            $out .= '<option class="current-post">' . $serial->post_title . '</option>';

         else
            $out .= '<option value="' . get_permalink( $serial->ID ) . '">' . $serial->post_title . '</option>';
      }
      else // default = 'list'
      {
         if ( $serial->ID == $post->ID && !$link_current )
            $out .= '<li class="current-post">' . $serial->post_title . '</li>';

         else
            $out .= '<li><a href="' . get_permalink( $serial->ID ) . '" title="' . wp_specialchars( $serial->post_title, 1 ) . '">' . $serial->post_title . '</a></li>';
      }

	endforeach;

	if ( $echo )
		echo $out;
	else
		return $out;
}

/**
 * Displays a list of posts related to the post by the first series.
 * @uses series_list_posts() Lists the posts in the series.
 *
 * @since 0.1
 * @param array $args See series_list_posts() for arguments.
 * @return string
 */
function series_list_related( $args = array() ) {
	global $post;

	$series = get_the_terms( $post->ID, 'series' );

	if ( !$series )
		return '';
	else
		$series = reset( $series );

	$args['series'] = $series->slug;

	if ( $args['echo'] )
		echo series_list_posts( $args );
	else
		return series_list_posts( $args );
}

?>