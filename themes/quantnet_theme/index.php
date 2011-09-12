<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
 get_header(); ?>

 <div id="container">
        <div id="content" role="main" class="<?php if(is_home()) echo 'myhome'; ?>">
        <!--Code for showing featured post-->
        <?php query_posts('cat=1&showposts=1&orderby=date&order=DESC'); ?>
            <?php while ( have_posts() ) : the_post();
                global $wpdb;
                $postID = get_the_ID();
                $comments = $wpdb->get_row("SELECT comment_count as count FROM wp_posts WHERE ID = '$postID'");
                $commentcount = $comments->count;
                if($commentcount == 1): $commenttext = 'Discuss'; endif;
                if($commentcount > 1 || $commentcount == 0): $commenttext = ''; endif;
                $fullcomments = $commentcount.' '.$commenttext;
            ?>
            <div class="stoy-shadow">
                <div class="ourstory">
                    <h1>Story Of The Day</h1>
                    <div class="story-img"> <?php //getting thumbnail
				if(has_post_thumbnail()) {
					the_post_thumbnail('thumbnail'); //for featured thumbail
					} else {
					catch_that_image(); //for first post image
					}
				 ?></div>
                    <div class="story-content">
                        <h2 class="entry-title"><?php the_title(); ?></h2>
                        <p>
                        <?php //the_excerpt();
                        $text = get_the_content();;
                        echo preview_text($text, '60', $TAGS = 0, $AFTER)?></p>
                        <a href="<?php the_permalink();?>" class="fullstory">Read the full story</a>
                    </div>
                    <div class="clear"></div>
                    <div class="post-meta">
                        <div class="post-comment">
                            <span><?php echo $fullcomments; ?></span>
                        </div>
                        <div class="fblike"> <fb:like href="<?php the_permalink(); ?>" width="50" height="31"  layout="button_count" show_faces="false"></fb:like></div>
                        <div class="posted-on"><em>Posted on <?php the_date('M jS, Y'); ?></em></div>
                    </div><!-- .entry-meta -->
                </div>
            </div>
        <?php endwhile; ?>
        <?php wp_reset_query(); ?>
        <?php
        /* Run the loop to output the posts.
         * If you want to overload this in a child theme then include a file
         * called loop-index.php and that will be used instead.
         */
         get_template_part( 'loop', 'index' );
        ?>
        <?php //wp_pagenavi(); ?>
        </div><!-- #content -->
 </div><!-- #container -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>
