<?php
/**
Template Name: Blog
 * The template for displaying blog posts.
 *
 * This is the template that displays all posts from blog category.
 
 */
get_header(); ?>
<div id="container">
  <div id="content" role="main">
         <?php $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        if(is_page('Education'))//for checking Education page
         {
           query_posts( 'cat=18&orderby=date&order=DESC&paged='.$paged ); //showing posts from Education category
         }
		 if(is_page('Jobs'))//for checking Jobs page
         {
           query_posts( 'cat=23&orderby=date&order=DESC&paged='.$paged ); //showing posts from Education category
         }
        if(is_page('Blog')) //for checking Blog page
         {
           query_posts( 'cat=51&orderby=date&order=DESC&paged='.$paged );  //showing posts from Blog category
         }
        if(is_page('Career')) //for checking Career page
         {
           query_posts( 'cat=37&orderby=date&order=DESC&paged='.$paged ); //showing posts from Quant Career category
         }
        while ( have_posts() ) : the_post(); ?>
            <div id="post" <?php post_class(); ?>>
                <h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyten' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
                <div class="entry-summary">
                <div class="featured-img"><?php //getting thumbnail
				if(has_post_thumbnail()) {
					the_post_thumbnail('thumbnail'); //for featured thumbail
					} else {
					catch_that_image(); //for first post image
					}
				 ?></div>
                <?php the_excerpt(); ?>
                </div><!-- .entry-summary -->
                <?php $tags_list = get_the_tag_list( '', ', ' ); if ( $tags_list ):?>
                 <div class="tag-links"><?php printf( __( '<span class="%1$s">Tags:</span> %2$s', 'twentyten' ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list ); ?></div>
                <?php endif; ?>
                <div class="post-meta">
                    <div class="post-comment">
                       <?php global $wpdb;
                        $postID = get_the_ID();
                        $title = get_the_title();
                        $comments = $wpdb->get_row("SELECT comment_count as count FROM wp_posts WHERE ID = '$postID'");
                        $commentcount = $comments->count;
                        if($commentcount == 1): $commenttext = ''; endif;
                        if($commentcount > 1 || $commentcount == 0): $commenttext = ''; endif;
                        $fullcomments = $commentcount.' '.$commenttext;?>
                                    <span><a rel="nofollow" href="<?php the_permalink(); ?>#comments"><?php echo $fullcomments;  ?></a></span>
                    </div>
                    <div class="fblike"><fb:like profile_id="" href="<?php the_permalink(); ?>" width="50" height="31"  layout="button_count" show_faces="false"></fb:like></div>
                    <div class="posted-on"><em>Posted On <?php the_date('M jS, Y'); ?></em></div>
                </div>
	     </div><!--post-->
         <?php endwhile;?>
	 <?php /* Display navigation to next/previous pages when applicable */ ?>
        <?php if(function_exists('wp_pagenavi')) { wp_pagenavi(); } else { ?>
            <div id="nav-below" class="navigation">
                    <div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'twentyten' ) ); ?></div>
                    <div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?></div>
            </div><!-- #nav-below -->
        <?php } ?>
        <?php wp_reset_query();?>
    </div><!-- #content -->
</div><!-- #container -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>
