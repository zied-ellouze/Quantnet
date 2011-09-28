<?php
/**
 * TwentyTen functions and definitions
 *
 * Sets up the theme and provides some helper functions. Some helper functions
 * are used in the theme as custom template tags. Others are attached to action and
 * filter hooks in WordPress to change core functionality.
 *
 * The first function, twentyten_setup(), sets up the theme by registering support
 * for various features in WordPress, such as post thumbnails, navigation menus, and the like.
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development and
 * http://codex.wordpress.org/Child_Themes), you can override certain functions
 * (those wrapped in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before the parent
 * theme's file, so the child theme functions would be used.
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are instead attached
 * to a filter or action hook. The hook can be removed by using remove_action() or
 * remove_filter() and you can attach your own function to the hook.
 *
 * We can remove the parent theme's hook only after it is attached, which means we need to
 * wait until setting up the child theme:
 *
 * <code>
 * add_action( 'after_setup_theme', 'my_child_theme_setup' );
 * function my_child_theme_setup() {
 *     // We are providing our own filter for excerpt_length (or using the unfiltered value)
 *     remove_filter( 'excerpt_length', 'twentyten_excerpt_length' );
 *     ...
 * }
 * </code>
 *
 * For more information on hooks, actions, and filters, see http://codex.wordpress.org/Plugin_API.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * Used to set the width of images and content. Should be equal to the width the theme
 * is designed for, generally via the style.css stylesheet.
 */
if ( ! isset( $content_width ) )
	$content_width = 640;
/** Tell WordPress to run twentyten_setup() when the 'after_setup_theme' hook is run. */
add_action( 'after_setup_theme', 'twentyten_setup' );
add_theme_support('post-thumbnails');
set_post_thumbnail_size( 270, 185, true );

//function for creating custom post type
add_action( 'init', 'create_job_post' );
function create_job_post() {
  $labels = array(
    'name' => _x('Job Posts', 'post type general name'),
    'singular_name' => _x('Job Post', 'post type singular name'),
    'add_new' => _x('Add New', 'Job Post'),
    'add_new_item' => __('Add New Job Post'),
    'edit_item' => __('Edit Job Post'),
    'new_item' => __('New Job Post'),
    'view_item' => __('View Job Post'),
    'search_items' => __('Search Job Posts'),
    'not_found' =>  __('No Job Posts found'),
    'not_found_in_trash' => __('No Job Posts found in Trash'),
    'parent_item_colon' => ''
  );

  $supports = array('title', 'editor', 'custom-fields', 'revisions', 'excerpt','thumbnail','page-attributes','comments');

  register_post_type( 'job_post',
    array(
      'labels' => $labels,
      'public' => true,
      'supports' => $supports,
		'rewrite' => array('slug' => ''),
	'taxonomies' => array('category', 'post_tag') // this is IMPORTANT

 )
  );
}

$result = add_role('company', 'Company', array(
    'read' => true, // True allows that capability
    'edit_posts' => true,
    'delete_posts' => true, // Use false to explicitly deny
));

// get the "author" role object
$role = get_role( 'author' );
 
// add "company" to this role object
$role->add_cap( 'company' );

function dropdown_tag_cloud( $args = '' ) {
	$defaults = array(
		'smallest' => 8, 'largest' => 22, 'unit' => 'pt', 'number' => 45,
		'format' => 'flat', 'orderby' => 'name', 'order' => 'ASC',
		'exclude' => '', 'include' => ''
	);
	$args = wp_parse_args( $args, $defaults );

	$tags = get_tags( array_merge($args, array('orderby' => 'count', 'order' => 'DESC')) ); // Always query top tags

	if ( empty($tags) )
		return;

	$return = dropdown_generate_tag_cloud( $tags, $args ); // Here's where those top tags get sorted according to $args
	if ( is_wp_error( $return ) )
		return false;
	else
		echo apply_filters( 'dropdown_tag_cloud', $return, $args );
}

function dropdown_generate_tag_cloud( $tags, $args = '' ) {
	global $wp_rewrite;
	$defaults = array(
		'smallest' => 8, 'largest' => 22, 'unit' => 'pt', 'number' => 45,
		'format' => 'flat', 'orderby' => 'name', 'order' => 'ASC'
	);
	$args = wp_parse_args( $args, $defaults );
	extract($args);

	if ( !$tags )
		return;
	$counts = $tag_links = array();
	foreach ( (array) $tags as $tag ) {
		$counts[$tag->name] = $tag->count;
		$tag_links[$tag->name] = get_tag_link( $tag->term_id );
		if ( is_wp_error( $tag_links[$tag->name] ) )
			return $tag_links[$tag->name];
		$tag_ids[$tag->name] = $tag->term_id;
	}

	$min_count = min($counts);
	$spread = max($counts) - $min_count;
	if ( $spread <= 0 )
		$spread = 1;
	$font_spread = $largest - $smallest;
	if ( $font_spread <= 0 )
		$font_spread = 1;
	$font_step = $font_spread / $spread;

	// SQL cannot save you; this is a second (potentially different) sort on a subset of data.
	if ( 'name' == $orderby )
		uksort($counts, 'strnatcasecmp');
	else
		asort($counts);

	if ( 'DESC' == $order )
		$counts = array_reverse( $counts, true );

	$a = array();

	$rel = ( is_object($wp_rewrite) && $wp_rewrite->using_permalinks() ) ? ' rel="tag"' : '';

	foreach ( $counts as $tag => $count ) {
		//print_r($tag);
		$tag_id = $tag_ids[$tag];
		//$tag_slug = $tag_slugs[$tag];
		$tag_link = clean_url($tag_links[$tag]);
		$tag = str_replace(' ', '&nbsp;', wp_specialchars( $tag ));
		$a[] = "\t<option value='$tag_link'>$tag ($count)</option>";
	}

	switch ( $format ) :
	case 'array' :
		$return =& $a;
		break;
	case 'list' :
		$return = "<ul class='wp-tag-cloud'>\n\t<li>";
		$return .= join("</li>\n\t<li>", $a);
		$return .= "</li>\n</ul>\n";
		break;
	default :
		$return = join("\n", $a);
		break;
	endswitch;

	return apply_filters( 'dropdown_generate_tag_cloud', $return, $tags, $args );
}

add_filter('pre_get_posts', 'query_post_type');
function query_post_type($query) {
  if(is_category() || is_tag()) {
    $post_type = get_query_var('post_type');
	if($post_type)
	    $post_type = $post_type;
	else
	    $post_type = array('post','job_post','nav_menu_item');
    $query->set('post_type',$post_type);
	return $query;
    }
}

//function for gtting tags from a particular category
function get_category_tags($args) {
	global $wpdb;
	$tags = $wpdb->get_results
	("
		SELECT DISTINCT terms2.term_id as tag_id, terms2.name as tag_name, terms2.slug as tag_link
		FROM
			wp_posts as p1
			LEFT JOIN wp_term_relationships as r1 ON p1.ID = r1.object_ID
			LEFT JOIN wp_term_taxonomy as t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id
			LEFT JOIN wp_terms as terms1 ON t1.term_id = terms1.term_id,

			wp_posts as p2
			LEFT JOIN wp_term_relationships as r2 ON p2.ID = r2.object_ID
			LEFT JOIN wp_term_taxonomy as t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id
			LEFT JOIN wp_terms as terms2 ON t2.term_id = terms2.term_id
		WHERE
			t1.taxonomy = 'category' AND p1.post_status = 'publish' AND terms1.term_id IN (".$args['categories'].") AND
			t2.taxonomy = 'post_tag' AND p2.post_status = 'publish'
			AND p1.ID = p2.ID
		ORDER by tag_name
	");
	//$content .= "<ul>";
	$content .= "<select name='tag-dropdn' onchange='document.forms[\"form\"].submit();' > <option value=''>--Tags--</option>";
	foreach ($tags as $tag) {
		$tag_link = get_option('home').'/tag/'.$tag->tag_link;
		//$tag = str_replace(' ', '&nbsp;', wp_specialchars( $tag ));
		$content .= "\t<option value='$tag->tag_link'>$tag->tag_name</option>";

	//$content .= "<li><a href='".get_option(home).'/tag/'.$tag->tag_link."'>$tag->tag_name</a></li>";
	}
	$content .= "</select>";
	//$content .= "</ul>";
	echo $content;
}

function ngg_excerpt(){
//get the post content
$content_data = get_the_content();
//extract shortcode from content
preg_match("/\[ngg([^}]*)\]/", $content_data ,$matches);
$results = $matches[1];
//if shortcode exists in content
if (!empty($results)){
//extract gallery id from shortcode
$gallery_id = preg_replace("/[^0-9]/", '', $matches[1]);
//make sure that NextGen is loaded
if (function_exists(nggShowGallery)){
//output gallery, showing only 4 images
echo nggShowGallery( $gallery_id, null , 4 );
}
}
}

function preview_text($TEXT, $LIMIT, $TAGS = 0, $AFTER) {

    // TRIM TEXT
    $TEXT = trim($TEXT);
	//echo $TEXT;
    // STRIP TAGS IF PREVIEW IS WITHOUT HTML
    if ($TAGS == 0) $TEXT = preg_replace('/\s\s+/', ' ', strip_tags($TEXT));

    // IF STRLEN IS SMALLER THAN LIMIT RETURN
    if (strlen($TEXT) < $LIMIT) return $TEXT;

    if ($TAGS == 0){ $substr=substr($TEXT, 0, $LIMIT);
        $next_char=substr($TEXT,$LIMIT,1); // 
        $space_pos=strrpos($substr,' ',0);
        $complete_word=substr($TEXT,0,$space_pos+1);
if($next_char==" "){
return $substr. " ...". "<a href='".get_permalink()."' title='Read More'>$AFTER</a>";
}
else {
return $complete_word. " ...". "<a href='".get_permalink()."' title='Read More'>$AFTER</a>";
}
 
  } else {

        $COUNTER = 0;
        for ($i = 0; $i<= strlen($TEXT); $i++) {

            if ($TEXT{$i} == "<") $STOP = 1;

            if ($STOP != 1) {

                $COUNTER++;
            }
            if ($TEXT{$i} == ">") $STOP = 0;
            $RETURN .= $TEXT{$i};

            if ($COUNTER >= $LIMIT && $TEXT{$i} == " ") break;
        }
$per=wurl();
//return $RETURN . "..." ."<a href='".get_permalink()."' title='Read More'>$AFTER</a>";
    }
}


function catch_that_image() {
  global $post, $posts;
  $first_img = '';
  ob_start();
  ob_end_clean();
  $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
  $first_img = $matches [1] [0];

  if(empty($first_img)){ //Defines a default image
    //$first_img = "/images/default.jpg";
  }
  else 
  {
 	 $explodepoint = explode(".",$first_img);
     $count = count($explodepoint);
     $size = "-300x198";
     $explodepoint[$count-2]= $explodepoint[$count-2]."".$size;
     $thumb_img = implode(".",$explodepoint);
	 if(is_home()) {
   ?>
  <div class="featured-img" ><img src="<?php echo  $first_img; //for showing thumbnail ?>"  /></div>
 <?php } else { ?>
 <div class="featured-img" ><img src="<?php echo  $first_img; //for showing thumbnail ?>" width="270"  /></div>
<?php } }
  return $first_img;
}

if ( ! function_exists( 'twentyten_setup' ) ):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 *
 * To override twentyten_setup() in a child theme, add your own twentyten_setup to your child theme's
 * functions.php file.
 *
 * @uses add_theme_support() To add support for post thumbnails and automatic feed links.
 * @uses register_nav_menus() To add support for navigation menus.
 * @uses add_custom_background() To add support for a custom background.
 * @uses add_editor_style() To style the visual editor.
 * @uses load_theme_textdomain() For translation/localization support.
 * @uses add_custom_image_header() To add support for a custom header.
 * @uses register_default_headers() To register the default custom header images provided with the theme.
 * @uses set_post_thumbnail_size() To set a custom post thumbnail size.
 *
 * @since Twenty Ten 1.0
 */
function twentyten_setup() {

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// Post Format support. You can also use the legacy "gallery" or "asides" (note the plural) categories.
	add_theme_support( 'post-formats', array( 'aside', 'gallery' ) );

	// This theme uses post thumbnails
	add_theme_support( 'post-thumbnails' );

	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// Make theme available for translation
	// Translations can be filed in the /languages/ directory
	load_theme_textdomain( 'twentyten', TEMPLATEPATH . '/languages' );

	$locale = get_locale();
	$locale_file = TEMPLATEPATH . "/languages/$locale.php";
	if ( is_readable( $locale_file ) )
		require_once( $locale_file );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => __( 'Primary Navigation', 'twentyten' ),
	) );

	// This theme allows users to set a custom background
	add_custom_background();

	// Your changeable header business starts here
	if ( ! defined( 'HEADER_TEXTCOLOR' ) )
		define( 'HEADER_TEXTCOLOR', '' );

	// No CSS, just IMG call. The %s is a placeholder for the theme template directory URI.
	if ( ! defined( 'HEADER_IMAGE' ) )
		define( 'HEADER_IMAGE', '%s/images/headers/path.jpg' );

	// The height and width of your custom header. You can hook into the theme's own filters to change these values.
	// Add a filter to twentyten_header_image_width and twentyten_header_image_height to change these values.
	define( 'HEADER_IMAGE_WIDTH', apply_filters( 'twentyten_header_image_width', 940 ) );
	define( 'HEADER_IMAGE_HEIGHT', apply_filters( 'twentyten_header_image_height', 198 ) );

	// We'll be using post thumbnails for custom header images on posts and pages.
	// We want them to be 940 pixels wide by 198 pixels tall.
	// Larger images will be auto-cropped to fit, smaller ones will be ignored. See header.php.
	set_post_thumbnail_size( HEADER_IMAGE_WIDTH, HEADER_IMAGE_HEIGHT, true );

	// Don't support text inside the header image.
	if ( ! defined( 'NO_HEADER_TEXT' ) )
		define( 'NO_HEADER_TEXT', true );

	// Add a way for the custom header to be styled in the admin panel that controls
	// custom headers. See twentyten_admin_header_style(), below.
	add_custom_image_header( '', 'twentyten_admin_header_style' );

	// ... and thus ends the changeable header business.

	// Default custom headers packaged with the theme. %s is a placeholder for the theme template directory URI.
	register_default_headers( array(
		'berries' => array(
			'url' => '%s/images/berries.jpg',
			'thumbnail_url' => '%s/images/berries-thumbnail.jpg',
			/* translators: header image description */
			'description' => __( 'Berries', 'twentyten' )
		),
		'cherryblossom' => array(
			'url' => '%s/images/cherryblossoms.jpg',
			'thumbnail_url' => '%s/images/cherryblossoms-thumbnail.jpg',
			/* translators: header image description */
			'description' => __( 'Cherry Blossoms', 'twentyten' )
		),
		'concave' => array(
			'url' => '%s/images/concave.jpg',
			'thumbnail_url' => '%s/images/concave-thumbnail.jpg',
			/* translators: header image description */
			'description' => __( 'Concave', 'twentyten' )
		),
		'fern' => array(
			'url' => '%s/images/fern.jpg',
			'thumbnail_url' => '%s/images/fern-thumbnail.jpg',
			/* translators: header image description */
			'description' => __( 'Fern', 'twentyten' )
		),
		'forestfloor' => array(
			'url' => '%s/images/forestfloor.jpg',
			'thumbnail_url' => '%s/images/forestfloor-thumbnail.jpg',
			/* translators: header image description */
			'description' => __( 'Forest Floor', 'twentyten' )
		),
		'inkwell' => array(
			'url' => '%s/images/inkwell.jpg',
			'thumbnail_url' => '%s/images/inkwell-thumbnail.jpg',
			/* translators: header image description */
			'description' => __( 'Inkwell', 'twentyten' )
		),
		'path' => array(
			'url' => '%s/images/path.jpg',
			'thumbnail_url' => '%s/images/path-thumbnail.jpg',
			/* translators: header image description */
			'description' => __( 'Path', 'twentyten' )
		),
		'sunset' => array(
			'url' => '%s/images/sunset.jpg',
			'thumbnail_url' => '%s/images/sunset-thumbnail.jpg',
			/* translators: header image description */
			'description' => __( 'Sunset', 'twentyten' )
		)
	) );
}
endif;

if ( ! function_exists( 'twentyten_admin_header_style' ) ) :
/**
 * Styles the header image displayed on the Appearance > Header admin panel.
 *
 * Referenced via add_custom_image_header() in twentyten_setup().
 *
 * @since Twenty Ten 1.0
 */
function twentyten_admin_header_style() {
?>
<style type="text/css">
    /* Shows the same border as on front end */
    #headimg {
            border-bottom: 1px solid #000;
            border-top: 4px solid #000;
    }
    /* If NO_HEADER_TEXT is false, you would style the text with these selectors:
            #headimg #name { }
            #headimg #desc { }
    */
</style>
<?php
}
endif;

/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 *
 * To override this in a child theme, remove the filter and optionally add
 * your own function tied to the wp_page_menu_args filter hook.
 *
 * @since Twenty Ten 1.0
 */
function twentyten_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'twentyten_page_menu_args' );

/**
 * Sets the post excerpt length to 40 characters.
 *
 * To override this length in a child theme, remove the filter and add your own
 * function tied to the excerpt_length filter hook.
 *
 * @since Twenty Ten 1.0
 * @return int
 */
function twentyten_excerpt_length( $length ) {
	return 40;
}
add_filter( 'excerpt_length', 'twentyten_excerpt_length' );

/**
 * Returns a "Continue Reading" link for excerpts
 *
 * @since Twenty Ten 1.0
 * @return string "Continue Reading" link
 */
function twentyten_continue_reading_link() {
	//return ' <a href="'. get_permalink() . '">' . __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'twentyten' ) . '</a>';
}

/**
 * Replaces "[...]" (appended to automatically generated excerpts) with an ellipsis and twentyten_continue_reading_link().
 *
 * To override this in a child theme, remove the filter and add your own
 * function tied to the excerpt_more filter hook.
 *
 * @since Twenty Ten 1.0
 * @return string An ellipsis
 */
function twentyten_auto_excerpt_more( $more ) {
	return ' &hellip;' . twentyten_continue_reading_link();
}
add_filter( 'excerpt_more', 'twentyten_auto_excerpt_more' );

/**
 * Adds a pretty "Continue Reading" link to custom post excerpts.
 *
 * To override this link in a child theme, remove the filter and add your own
 * function tied to the get_the_excerpt filter hook.
 *
 * @since Twenty Ten 1.0
 * @return string Excerpt with a pretty "Continue Reading" link
 */
function twentyten_custom_excerpt_more( $output ) {
	if ( has_excerpt() && ! is_attachment() ) {
		$output .= twentyten_continue_reading_link();
	}
	return $output;
}
add_filter( 'get_the_excerpt', 'twentyten_custom_excerpt_more' );

/**
 * Remove inline styles printed when the gallery shortcode is used.
 *
 * Galleries are styled by the theme in Twenty Ten's style.css. This is just
 * a simple filter call that tells WordPress to not use the default styles.
 *
 * @since Twenty Ten 1.2
 */
add_filter( 'use_default_gallery_style', '__return_false' );

/**
 * Deprecated way to remove inline styles printed when the gallery shortcode is used.
 *
 * This function is no longer needed or used. Use the use_default_gallery_style
 * filter instead, as seen above.
 *
 * @since Twenty Ten 1.0
 * @deprecated Deprecated in Twenty Ten 1.2 for WordPress 3.1
 *
 * @return string The gallery style filter, with the styles themselves removed.
 */
function twentyten_remove_gallery_css( $css ) {
	return preg_replace( "#<style type='text/css'>(.*?)</style>#s", '', $css );
}
// Backwards compatibility with WordPress 3.0.
if ( version_compare( $GLOBALS['wp_version'], '3.1', '<' ) )
	add_filter( 'gallery_style', 'twentyten_remove_gallery_css' );

if ( ! function_exists( 'twentyten_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own twentyten_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since Twenty Ten 1.0
 */
function twentyten_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case '' :
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<div id="comment-<?php comment_ID(); ?>">
        <div class="comment-top">
		<div class="comment-author vcard">
			<?php echo get_avatar( $comment, 40 ); ?>

		</div><!-- .comment-author .vcard -->
		<?php if ( $comment->comment_approved == '0' ) : ?>
			<em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'twentyten' ); ?></em>
			<br />
		<?php endif; ?>

		<div class="comment-meta commentmetadata">
        			<?php printf( __( '%s', 'twentyten' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
        
        <a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
			<?php
				/* translators: 1: date, 2: time */
				printf( __( '%1$s at %2$s', 'twentyten' ), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( '(Edit)', 'twentyten' ), ' ' );
			?>
		</div>
        </div>
        <!-- .comment-meta .commentmetadata -->

		<div class="comment-body"><?php comment_text(); ?></div>

		<div class="reply">
			<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
		</div><!-- .reply -->
	</div><!-- #comment-##  -->

	<?php
			break;
		case 'pingback'  :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p><?php _e( 'Pingback:', 'twentyten' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'twentyten' ), ' ' ); ?></p>
	<?php
			break;
	endswitch;
}
endif;

/**
 * Register widgetized areas, including two sidebars and four widget-ready columns in the footer.
 *
 * To override twentyten_widgets_init() in a child theme, remove the action hook and add your own
 * function tied to the init hook.
 *
 * @since Twenty Ten 1.0
 * @uses register_sidebar
 */
function twentyten_widgets_init() {
	// Area 1, located at the top of the sidebar.
	register_sidebar( array(
		'name' => __( 'Primary Widget Area', 'twentyten' ),
		'id' => 'primary-widget-area',
		'description' => __( 'The primary widget area', 'twentyten' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Area 2, located below the Primary Widget Area in the sidebar. Empty by default.
	register_sidebar( array(
		'name' => __( 'Secondary Widget Area', 'twentyten' ),
		'id' => 'secondary-widget-area',
		'description' => __( 'The secondary widget area', 'twentyten' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Area 3, located in the footer. Empty by default.
	register_sidebar( array(
		'name' => __( 'First Footer Widget Area', 'twentyten' ),
		'id' => 'first-footer-widget-area',
		'description' => __( 'The first footer widget area', 'twentyten' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Area 4, located in the footer. Empty by default.
	register_sidebar( array(
		'name' => __( 'Second Footer Widget Area', 'twentyten' ),
		'id' => 'second-footer-widget-area',
		'description' => __( 'The second footer widget area', 'twentyten' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Area 5, located in the footer. Empty by default.
	register_sidebar( array(
		'name' => __( 'Third Footer Widget Area', 'twentyten' ),
		'id' => 'third-footer-widget-area',
		'description' => __( 'The third footer widget area', 'twentyten' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Area 6, located in the footer. Empty by default.
	register_sidebar( array(
		'name' => __( 'Fourth Footer Widget Area', 'twentyten' ),
		'id' => 'fourth-footer-widget-area',
		'description' => __( 'The fourth footer widget area', 'twentyten' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
}
/** Register sidebars by running twentyten_widgets_init() on the widgets_init hook. */
add_action( 'widgets_init', 'twentyten_widgets_init' );

/**
 * Removes the default styles that are packaged with the Recent Comments widget.
 *
 * To override this in a child theme, remove the filter and optionally add your own
 * function tied to the widgets_init action hook.
 *
 * This function uses a filter (show_recent_comments_widget_style) new in WordPress 3.1
 * to remove the default style. Using Twenty Ten 1.2 in WordPress 3.0 will show the styles,
 * but they won't have any effect on the widget in default Twenty Ten styling.
 *
 * @since Twenty Ten 1.0
 */
function twentyten_remove_recent_comments_style() {
	add_filter( 'show_recent_comments_widget_style', '__return_false' );
}
add_action( 'widgets_init', 'twentyten_remove_recent_comments_style' );

if ( ! function_exists( 'twentyten_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 *
 * @since Twenty Ten 1.0
 */
function twentyten_posted_on() {
	printf( __( '<span class="%1$s">Posted </span> %2$s <span class="meta-sep">by</span> %3$s', 'twentyten' ),
		'meta-prep meta-prep-author',
		sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
			get_permalink(),
			esc_attr( get_the_time() ),
			get_the_date('M jS Y \\a\\t g:i A')
		),
		sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>',
			get_author_posts_url( get_the_author_meta( 'ID' ) ),
			sprintf( esc_attr__( 'View all posts by %s', 'twentyten' ), get_the_author() ),
			get_the_author()
		)
	);
}
endif;

if ( ! function_exists( 'twentyten_posted_in' ) ) :
/**
 * Prints HTML with meta information for the current post (category, tags and permalink).
 *
 * @since Twenty Ten 1.0
 */
function twentyten_posted_in() {
	// Retrieves tag list of current post, separated by commas.
	$tag_list = get_the_tag_list( '', ', ' );
	if ( $tag_list ) {
		$posted_in = __( 'This entry was posted in %1$s and tagged %2$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'twentyten' );
	} elseif ( is_object_in_taxonomy( get_post_type(), 'category' ) ) {
		$posted_in = __( 'This entry was posted in %1$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'twentyten' );
	} else {
		$posted_in = __( 'Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'twentyten' );
	}
	// Prints the string, replacing the placeholders.
	printf(
		$posted_in,
		get_the_category_list( ', ' ),
		$tag_list,
		get_permalink(),
		the_title_attribute( 'echo=0' )
	);
}
endif;

// remove junk from head
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'feed_links_extra', 3);

/***************************
////////////////////////////
Program Customization
////////////////////////////

@author Eric Stolz
***************************/

global $new_type_name;
$new_type_name = "quantnet_program";

if(!function_exists('quant_get_all_program_images')):
	function quant_get_all_program_images($program_id = null){
		if($program_id == null):
			return array();
		endif;
		
		$program_options = get_post_meta($program_id, 'quant_program_options', true);
		if(is_array($program_options['images'])):
			$program_images = $program_options['images'];
		else:
			$program_images = array();
		endif;
		
		$review_images = quant_get_all_review_images($program_id, 50);
		
		$images = array_merge($program_images,$review_images);
		
		return $images;
	}
endif;

if(!function_exists('quant_get_all_review_images')):
	function quant_get_all_review_images($post_id, $limit = 4){
		global $wpdb;
		$images = $wpdb->get_results("SELECT t1.id, t1.post_id, t2.lead_id, t2.field_number, t2.value FROM wp_rg_lead as t1, wp_rg_lead_detail as t2 WHERE t1.post_id = '".$post_id."' AND t1.id = t2.lead_id AND (t2.field_number = 32 || t2.field_number = 33 || t2.field_number = 34 || t2.field_number = 35 || t2.field_number = 36 || t2.field_number = 37 || t2.field_number = 38 || t2.field_number = 39 || t2.field_number = 40) LIMIT ".$limit);
		$arr_images = array();
		if(count($images) > 0):
			foreach($images as $image):
				if(stripos($image->value, "http://") !== false)
					$arr_images[] = $image->value;
			endforeach;
		endif;
		
		return $arr_images;
	}
endif;

if(is_admin())
	add_action('admin_init', 'quantnet_program_details', 1);
	
if(is_admin())
	add_action('save_post', 'quantnet_program_save');

if(!function_exists('quantnet_program_details')):
	function quantnet_program_details() {
	  add_meta_box( 
	    'quantnet_section_id',
	    __( 'Program Details', 'quantnet_details' ),
	    'quantnet_program_details_box',
	    'quantnet_program',
	    'normal',
	    'high'
	  );
	}
endif;

if(!function_exists('quant_add_post_enctype')):
	function quant_add_post_enctype() {
		echo "<script type=\"text/javascript\">
	   jQuery(document).ready(function(){
	       jQuery('#post').attr('enctype','multipart/form-data');
	       jQuery('#post').attr('encoding', 'multipart/form-data');                           
		});
		</script>"; 
	}
endif;

if(!function_exists('quantnet_program_details_box')):
	function quantnet_program_details_box(){
		// Use nonce for verification
	  wp_nonce_field( plugin_basename(__FILE__), 'myplugin_noncename' );
		$program_options = get_post_meta($_GET['post'], 'quant_program_options', true);
		//Set the form to allow file uploads
		quant_add_post_enctype();
	  ?>
	  <p>Link to wiki:</p>
	  <p><input type="text" name="quant_wiki_link" value="<?php echo $program_options['wiki_link']; ?>" size="45"></p>
	  <p>Editor Rating:</p>
	  <p><select name="quant_editor_rating"><option value="">NA</option><?php for($i=1;$i<=10;$i++){ if($program_options['editor_rating'] == $i) echo "<option value='".$i."' selected>".$i."</option>"; else echo "<option value='".$i."'>".$i."</option>"; } ?></select></p>
	  <p>Link to forum RSS feed:</p>
	  <p><input type="text" name="quant_forum_rss" value="<?php echo $program_options['forum_rss']; ?>" size="45"></p>
	  <p>Quantnet MFE Ranking:</p>
	  <p><input type="text" name="quant_mfe_ranking" value="<?php echo $program_options['mfe_ranking']; ?>" size="45"></p>
	  <p>Image Gallery</p>
	  <?php if(count($program_options['images']) > 0): ?>
	  <p><a href="" onClick="jQuery('#preview_images').toggle('slow'); return false;">View Gallery Images</a></p>
	  <div id="preview_images" style="display: none;">
	  	<?php foreach($program_options['images'] as $image): ?>
	  		<div style="margin-bottom: 10px; padding: 10px; border: 1px solid #ccc;">Delete? <input type="checkbox" name="delete_images[]" value="<?php echo $image; ?>"> <img src="<?php echo $image; ?>"></div>
	  		<div style="clear: both;"></div>
	  	<?php endforeach; ?>
	  </div>
	  <?php endif; ?>
	  <div id="program_images">
	  	<p id="program_image_single"><input type="file" name="program_image[]"></p>
	  </div>
	  <p><a href="" onClick="jQuery('#program_image_single').clone().appendTo('#program_images'); return false;">Add Image</a></p>
	  <?php
	}
endif;

if(!function_exists('quantnet_program_save')):
	function quantnet_program_save( $post_id ) {
	
	  // verify this came from the our screen and with proper authorization,
	  // because save_post can be triggered at other times
	
	  if ( !wp_verify_nonce( $_POST['myplugin_noncename'], plugin_basename(__FILE__) ) )
	      return $post_id;
	
	  // verify if this is an auto save routine. 
	  // If it is our form has not been submitted, so we dont want to do anything
	  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
	      return $post_id;

	  // Check permissions
	  if ( 'page' == $_POST['post_type'] ) 
	  {
	    if ( !current_user_can( 'edit_page', $post_id ) )
	        return $post_id;
	  }
	  else
	  {
	    if ( !current_user_can( 'edit_post', $post_id ) )
	        return $post_id;
	  }
	
	  // OK, we're authenticated: we need to find and save the data
	  
	  $current_program_options = get_post_meta($post_id, 'quant_program_options', true);
	  if(!is_array($current_program_options['images']))
	  	$current_program_options['images'] = array();
	  
	  $program_options = array(
	  	'wiki_link'=>$_POST['quant_wiki_link'],
	  	'editor_rating'=>$_POST['quant_editor_rating'],
	  	'forum_rss'=>$_POST['quant_forum_rss'],
	  	'mfe_ranking'=>$_POST['quant_mfe_ranking']
	  );
	  
	  $store_images = array();
	  
	  $images = $_FILES['program_image'];
	  
	  //If there are images to upload, do so
		if(count($images) > 0):
		  $fin_images = array();
		  foreach($images['name'] as $k=>$v):
		  	$fin_images[] = array(
		  		'name'=>$v,
		  		'type'=>$images['type'][$k],
		  		'tmp_name'=>$images['tmp_name'][$k],
		  		'size'=>$images['size'][$k]
		  	);
		  endforeach;
		  
		  foreach($fin_images as $image):
		  	$return = wp_handle_upload( $image, array('test_form'=>FALSE));
		  	if(strlen($return['url']) > 0)
		  		$store_images[] = $return['url'];
		  endforeach;
	  
	  endif;
	  
	  //Delete images that the user opted to delete from the gallery
	  if(count($_POST['delete_images']) > 0):
	  	foreach($_POST['delete_images'] as $dimage):
	  		$key = array_search($dimage,$current_program_options['images']);
	  		if($key == 0 || $key != false):
	  			unset($current_program_options['images'][$key]);
	  		endif;
	  	endforeach;
	  endif;

	  //Combine the previous images with any new images
	  $combined_store_images = array_merge($current_program_options['images'],$store_images);
	  
	  $program_options['images'] = $combined_store_images;
	  
	  update_post_meta($post_id, "quant_program_options", $program_options);
	  if(strlen(get_post_meta($post_id, "average_rating", true)) == 0):
	  	update_post_meta($post_id, "average_rating", "NA");
	  endif;
	  
	  update_post_meta($post_id, "number_of_reviews", "0");
	  quantnet_set_reviews_ranking();
		
	  return true;
	}
endif;

//Create custom post type for programs
add_action( 'init', 'quantnet_create_program_type' );

//Register the new post type
if(!function_exists('quantnet_create_program_type')):
	function quantnet_create_program_type() {
		global $new_type_name;
		register_post_type( $new_type_name,
			array(
				'labels' => array(
					'name' => __( 'Programs' ),
					'singular_name' => __( 'Programs' ),
					'add_new_item' => __('Add New Program'),
					'edit_item' => __('Edit Program'),
					'new_item' => __('New Program'),
					'view_item' => __('View Program'),
					'search_items' => __('Search Programs'),
					'not_found' => __('No programs found.'),
					
				),
			'taxonomies' => array(
				'program_tag','program_type'
			),
			'show_ui' => true, 
	    'show_in_menu' => true, 
	    'query_var' => true,
	    'rewrite' => array('slug'=>'programs'),
	    'capability_type' => 'post',
	    'has_archive' => true, 
	    'hierarchical' => false,
			'public' => true,
			'has_archive' => true,
			'exclude_from_search' => false,
			'menu_position' => '5',
			'supports' => array(
				'title','editor','custom-fields','page-attributes','post-formats','thumbnail'
			)
			)
		);
	}
endif;

//hook into the init action and call create_book_taxonomies when it fires
add_action( 'init', 'quantnet_program_type_taxonomy', 0 );

if(!function_exists('quantnet_program_type_taxonomy')):
	function quantnet_program_type_taxonomy() {
	  // Add new taxonomy, make it hierarchical (like categories)
	  $labels = array(
	    'name' => _x( 'Program Types', 'program_types' ),
	    'singular_name' => _x( 'Program Type', 'program_type' ),
	    'search_items' =>  __( 'Search Program Types' ),
	    'all_items' => __( 'All Program Types' ),
	    'parent_item' => __( 'Parent Program Type' ),
	    'parent_item_colon' => __( 'Parent Program Type:' ),
	    'edit_item' => __( 'Edit Program Type' ), 
	    'update_item' => __( 'Update Program Type' ),
	    'add_new_item' => __( 'Add New Program Type' ),
	    'new_item_name' => __( 'New Program Type' ),
	    'menu_name' => __( 'Program Type' ),
	  ); 	
	
	  register_taxonomy('program_type',array('quantnet_program'), array(
	    'hierarchical' => true,
	    'labels' => $labels,
	    'show_ui' => true,
	    'query_var' => true,
	    'rewrite' => array( 'slug' => 'program_type' ),
	  ));
	  
	  // Add new taxonomy, do not make it hierarchical (like tags)
	  $labels = array(
	    'name' => _x( 'Program Tags', 'program_tags' ),
	    'singular_name' => _x( 'Program Tag', 'program_tag' ),
	    'search_items' =>  __( 'Search Program Tags' ),
	    'all_items' => __( 'All Program Tags' ),
	    'parent_item' => __( 'Parent Program Tag' ),
	    'parent_item_colon' => __( 'Parent Program Tag:' ),
	    'edit_item' => __( 'Edit Program Tag' ), 
	    'update_item' => __( 'Update Program Tag' ),
	    'add_new_item' => __( 'Add New Program Tag' ),
	    'new_item_name' => __( 'New Program Tag' ),
	    'menu_name' => __( 'Program Tag' ),
	  ); 	
	
	  register_taxonomy('program_tag',array('quantnet_program'), array(
	    'hierarchical' => false,
	    'labels' => $labels,
	    'show_ui' => true,
	    'query_var' => true,
	    'rewrite' => array( 'slug' => 'program_tag' ),
	  ));
	}
endif;

//[gravityform id=1 name=AddReview ajax=true]
//add_action( 'the_content', 'check_for_gravity_form' );

if(!function_exists("check_for_gravity_form")):
    function check_for_gravity_form($content){
	//echo $content;
	//die();
            if(stripos($content,"[gravityform") !== false):
                    //$content = $content."<p>TRUE</p>";
                    $js = '<script type="text/javascript">var count_files=0; var total_files = jQuery("input:file").length; jQuery("input:file").each(function(){ var id = this.id; var field_id = id.replace("input_","field_"); if(count_files != 0){ jQuery("#"+field_id).attr("style","display: none;"); } if(count_files == (total_files-1)){ jQuery("#"+field_id).after("<li><p><a href=\'\' onClick=\'showNext(); return false;\' class=\'addimage\'>Add Another Image</a></p></li>"); } count_files++; }); function showNext(){ jQuery("input:file").each(function(){ var id = this.id; var field_id = id.replace("input_","field_"); if(!jQuery("#"+field_id).is(":visible")){ jQuery("#"+field_id).show("slow"); return false; } }); }</script>';
                    $content = $content.$js;
            endif;
            return $content;
    }
endif;

//add_filter( "the_content", "add_gravity_form_to_review" );

if(!function_exists('add_gravity_form_to_review')):
	function add_gravity_form_to_review($content){
		global $post, $new_type_name;

		if(is_single() && $post->post_type == $new_type_name):
			$content = $content."<p class='write-a-review'><a href='' onClick='jQuery(\"#add_program_review\").toggle(\"slow\"); return false;'>Add A Review</a></p><div id='add_program_review' style='display: none;'>[gravityform id=1 name=AddReview ajax=true]</div>";
			$content = check_for_gravity_form($content);
		endif;
			
		return $content;
	}
endif;

/*
Grab all required information for each program
*/
if(!function_exists('quantnet_review_details')):
	function quantnet_review_details($id=null){
		global $wpdb;
		if($id==null)
			return;
		$average_rating = get_post_meta($id, 'average_rating', true);
		if(strlen($average_rating) == 0)
			$average_rating = "NA";
		$program_options = get_post_meta($id, 'quant_program_options', false);
		if(strlen($program_options[0]["mfe_ranking"]) > 0):
			$mfe_ranking = $program_options[0]['mfe_ranking'];
		else:
			$mfe_ranking = "NA";
		endif;
		$reviews = get_post_meta($id, 'number_of_reviews', true);
		if($reviews=="")
			$reviews=0;
		return array_merge(array("ranking"=>get_post_meta($id, 'ranking', true),"mfe_ranking"=>$mfe_ranking,"average_rating"=>$average_rating,"total_reviews"=>$reviews),$program_options[0]);
	}
endif;

if(!function_exists('quantnet_review_latest_reviews')):
	function quantnet_review_latest_reviews($id){
		global $wpdb;
		$max_length = 45;
		$latest_reviews = $wpdb->get_results("SELECT * FROM wp_rg_lead as l, wp_rg_lead_detail as ld WHERE l.post_id = '".$id."' AND l.id = ld.lead_id AND field_number = '23' ORDER BY l.date_created ASC LIMIT 3");
		$results = array();
		if(count($latest_reviews) > 0):
			foreach($latest_reviews as $r):
				if(strlen($r->value) > $max_length):
					$title = substr($r->value,0,$max_length)."…";
				else:
					$title = $r->value;
				endif;
				$results[] = array("title"=>$title,"date"=>date("F j, Y",strtotime($r->date_created)));
			endforeach;
		endif;
		return $results;
	}
endif;

if(!function_exists('quantnet_review_calculate_rating')):
	function quantnet_review_calculate_rating($id){
		global $wpdb;
		$reviews = $wpdb->get_results("SELECT * FROM wp_rg_lead as l, wp_rg_lead_detail as ld WHERE l.post_id = '".$id."' AND l.id = ld.lead_id AND ld.field_number = '26'");
		$total = count($reviews);
		//mail('estolz@websitez.com', 'Reviews', $total);
		if($total > 0):
			foreach($reviews as $r):
				$value += (Int)$r->value;
			endforeach;
			$rating = number_format($value/$total,2);
		else:
			$rating = "NA";
		endif;
		
		return $rating;
	}
endif;

//On review submission, calculate rating of master review
add_action("gform_post_submission", "quantnet_review_calculate_rating_after_submission", 10, 2);
//On review submission, update post meta
add_action("gform_post_submission", "quantnet_review_update_post", 10, 2);

if(!function_exists('quantnet_review_update_post')):
	function quantnet_review_update_post($entry, $form){
		$post = get_post($entry["post_id"]);
		$post->post_type = "quantnet_review";
		$post->post_parent = $entry[41];
		$post->post_status = "publish";
		wp_update_post($post);
		update_post_meta($entry["post_id"], 'rating', $entry[26]);
	}
endif;

if(!function_exists('quantnet_review_calculate_rating_after_submission')):
	function quantnet_review_calculate_rating_after_submission($entry, $form){
		global $wpdb;
		//$entry[41] is the post id for the master review
		$update = $wpdb->query("UPDATE wp_rg_lead SET post_id = '".$entry[41]."' WHERE id = '".$entry["id"]."'");
		$rating = quantnet_review_calculate_rating($entry[41]);
		update_post_meta($entry[41], 'average_rating', $rating);
		$number_of_reviews = get_post_meta($entry[41], "number_of_reviews", true);
		if($number_of_reviews=="")
			$number_of_reviews=0;
		else
			$number_of_reviews = (Int)$number_of_reviews;
		$number_of_reviews = $number_of_reviews+1;
		update_post_meta($entry[41], 'number_of_reviews', $number_of_reviews);
	}
endif;

//This disables a post from being created
//add_filter("gform_disable_post_creation", "quantnet_disable_post_creation", 10, 3);

if(!function_exists('quantnet_disable_post_creation')):
	function quantnet_disable_post_creation($is_disabled, $form, $entry){
		return true;
	}
endif;

if(!function_exists('quantnet_set_reviews_ranking')):
	function quantnet_set_reviews_ranking(){
		$wpsc_query = new WP_Query( 
			array(
				'post_type'=>'quantnet_program',
				'post_status'=>'publish',
				'meta_key'=>'average_rating',
				'orderby'=>'meta_value_num',
				'order'=>'DESC'
			)
		);
		
		if($wpsc_query->have_posts()) :
			$i=1;
			foreach($wpsc_query->get_posts() as $post) :
				update_post_meta($post->ID, 'ranking', $i);
				$i++;
			endforeach;
		endif;
	}
endif;

if(!function_exists('quantnet_total_reviews')):
	function quantnet_total_reviews(){
		$wpsc_query = new WP_Query( 
			array(
				'post_type'=>'quantnet_program',
				'post_status'=>'publish',
				'nopaging'=>'true'
			)
		);
		
		return $wpsc_query->post_count;
	}
endif;

//function for displaying caption
    function fb_img_caption_shortcode($attr, $content = null) {
    // Allow plugins/themes to override the default caption template.
    $output = apply_filters('img_caption_shortcode', '', $attr, $content);
    if ( $output != '' )
    return $output;
    extract(shortcode_atts(array(
    'id' => '',
    'align' => 'alignnone',
    'width' => '',
    'caption' => ''
    ), $attr));
    if ( 1 > (int) $width || empty($caption) )
    return $content;
    if ( $id ) $id = 'id="' . $id . '" ';
    return '<dl ' . $id . 'class="wp-caption ' . $align . '" style="width: ' . (10 + (int) $width) . 'px"><dt>'
    . do_shortcode( $content ) . '</dt><dd class="wp-caption-text">' . $caption . '</dd></dl>';
    }
    add_shortcode('wp_caption', 'fb_img_caption_shortcode');
    add_shortcode('caption', 'fb_img_caption_shortcode');
?>