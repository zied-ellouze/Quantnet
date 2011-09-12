<?php
/**
 * The Header for our theme.
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Quantnet Theme
 * @since Quantnet Theme
 */
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" <?php language_attributes(); ?>  xmlns:x2="http://www.w3.org/2002/06/xhtml2" xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title>
<?php
    /*
     * Print the <title> tag based on what is being viewed.
     */
    global $page, $paged;
    wp_title( '|', true, 'right' );
    // Add the blog name.
    bloginfo( 'name' );
    // Add the blog description for the home/front page.
    $site_description = get_bloginfo( 'description', 'display' );
    if ( $site_description && ( is_home() || is_front_page() ) )
         echo " | $site_description";
    // Add a page number if necessary:
    if ( $paged >= 2 || $page >= 2 )
         echo ' | ' . sprintf( __( 'Page %s', 'twentyten' ), max( $paged, $page ) );
?>
</title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php
    /* We add some JavaScript to pages with the comment form
     * to support sites with threaded comments (when in use).
     */
    if ( is_singular() && get_option( 'thread_comments' ) )
            wp_enqueue_script( 'comment-reply' );

    /* Always have wp_head() just before the closing </head>
     * tag of your theme, or you will break many plugins, which
     * generally use this hook to add elements to <head> such
     * as styles, scripts, and meta tags.
     */
    wp_head();
?>
</head>
<div id="error-wrapper">
     <div class="errorpage">
         <div class="errorcolumn">
                <h1 class="error-title"><?php _e( 'Error 404 - Page Not Found!', 'twentyten' ); ?></h1>
                <div class="error-content">
                   <?php _e( "It is looking like you have calculated something incorrectly. Don't worry here's some help to get to correct answers!", "twentyten" ); ?>
                </div>
                <div class="buttonarea">
                    <span class="span1text">If the problem persists, please</span>
                    <a href="mailto:" class="emailbtn"></a>
                    <span>or</span>
                    <a href="<?php bloginfo('url'); ?>/contact-us" class="contactbtn"></a>
                </div>
                <div class="errorlinks">
                    <h2>Most Popular</h2>
                    <ul>
                        <li><a href="#">How to get your dream quant internship?</a></li>
                        <li><a href="#">My study experience, MFE internship and job offers</a></li>
                        <li><a href="#">10 Fun Quantnet Facts You May Not Know</a></li>
                        <li><a href="#">Memories from the 2010 Quantnet Central Park Picnic</a></li>
                    </ul>
                </div>
                <div class="errorlinks" id="articles">
                    <h2>Latest Articles</h2>
                    <ul>
                        <li><a href="#">Quantnet's Best-Selling Books of 2010</a></li>
                        <li><a href="#">Rutgers to Launch Third Quant Master Program</a></li>
                        <li><a href="#">A day in the Life of a London software contractor</a></li>
                        <li><a href="#">My Journey to be a Quant</a></li>
                    </ul>
                </div>
                <div class="clear"></div>
         </div>
    </div>
    <div id="header" class="headernone">
        <a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home" class="logo">
            <img src="<?php bloginfo('template_url'); ?>/images/logo.png" />
        </a><!-- Logo -->
        <div id="access">
                <?php wp_nav_menu( array( 'container_class' => 'menu-header', 'theme_location' => 'primary' ) ); ?>
                <?php get_search_form(); ?>
        </div><!-- #access -->
        <div class="clear"></div>
    </div><!-- #header -->
</div>
<script type="text/javascript">
    // focus on search field after it has loaded
    document.getElementById('s') && document.getElementById('s').focus();
</script>