<?php
/**
 * The template for displaying Author Archive pages.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
get_header(); ?>
<div id="container">
    <div id="content" role="main">
        <?php
        /* Queue the first post, that way we know who
         * the author is when we try to get their name,
         * URL, description, avatar, etc.
         *
         * We reset this later so we can run the loop
         * properly with a call to rewind_posts().
         */
        if ( have_posts() )
                the_post();
        ?>
        <?php // If a user has filled out their description, show a bio on their entries.
            if ( get_the_author_meta( 'description' ) ) : ?>
            <div class="stoy-shadow">
                 <div class="ourstory">
                    <div id="author-avatar">
                            <?php echo get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'twentyten_author_bio_avatar_size', 60 ) ); ?>
                    </div><!-- #author-avatar -->
                    <div id="author-description">
                            <h2 class="entry-title"><?php printf( __( '%s', 'twentyten' ), get_the_author() ); ?></h2>
                            <?php the_author_meta( 'description' ); ?>
                    </div><!-- #author-descriptio-->
                    <div class="clear"></div>
                 </div>
             </div>
        <?php endif; ?>
        <?php
	/* Since we called the_post() above, we need to
	 * rewind the loop back to the beginning that way
	 * we can run the loop properly, in full.
	 */
	rewind_posts();
	/* Run the loop for the author archive page to output the authors posts
	 * If you want to overload this in a child theme then include a file
	 * called loop-author.php and that will be used instead.
	 */
	 get_template_part( 'loop', 'author' );
        ?>
    </div><!-- #content -->
</div><!-- #container -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>
