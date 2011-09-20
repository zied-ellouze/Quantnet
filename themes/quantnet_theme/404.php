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
					<?php query_posts('orderby=comment_count&posts_per_page=4');
            		if ( have_posts() ) : while ( have_posts() ) : the_post();
                global $wpdb;
                $postID = get_the_ID();
                $title = get_the_title();
                $comments = $wpdb->get_row("SELECT comment_count as count FROM wp_posts WHERE ID = '$postID'");
                $commentcount = $comments->count;
                if($commentcount == 1): $commenttext = ''; endif;
                if($commentcount > 1 || $commentcount == 0): $commenttext = ''; endif;
                $fulltitle = $title.' ('.$commentcount.' '.$commenttext.')';
                 ?>

                <li><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php echo $title; ?> </a></li>
                <?php endwhile; ?>
                <?php else : ?>
                <li>Sorry, no posts were found.</li>
            <?php endif;
            wp_reset_query();?>
                    </ul>
                </div>
                <div class="errorlinks" id="articles">
                    <h2>Latest Articles</h2>
                    <ul>
                        <?php query_posts('orderby=date&posts_per_page=4');
            		if ( have_posts() ) : while ( have_posts() ) : the_post();
                global $wpdb;
                $postID = get_the_ID();
                $title = get_the_title();
                 ?>

                <li><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php echo $title; ?> </a></li>
                <?php endwhile; ?>
                <?php else : ?>
                <li>Sorry, no posts were found.</li>
            <?php endif;
            wp_reset_query();?>
                    </ul>
                </div>
                <div class="clear"></div>
         </div>
    </div>
    <div id="header" class="headernone">
        <a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home" class="logo">
            <img src="<?php bloginfo('template_url'); ?>/images/logo.png" />
        </a><!-- Logo -->
        <div class="access">
                <?php wp_nav_menu( array( 'container_class' => 'menu-header', 'theme_location' => 'primary' ) ); ?>
               <div class="search-bar">
            <form action="<?php bloginfo('home'); ?>" id="searchform" method="get" role="search">
                   <input type="text" id="s" name="s" value="Search" onblur="if (this.value == '') {this.value = 'Search';}" onfocus="if (this.value == 'Search') {this.value = '';}" >
                    <input type="submit" value="Search" id="searchsubmit" class="searchbtn">
            </form>
            </div>
        </div><!-- #access -->
        <div class="clear"></div>
    </div><!-- #header -->
</div>
<script type="text/javascript">
    // focus on search field after it has loaded
    document.getElementById('s') && document.getElementById('s').focus();
</script>