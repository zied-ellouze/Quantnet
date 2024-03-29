<?php
/**
 * The loop that displays posts.
 *
 * The loop displays the posts and the post content.  See
 * http://codex.wordpress.org/The_Loop to understand it and
 * http://codex.wordpress.org/Template_Tags to understand
 * the tags used in it.
 *
 * This can be overridden in child themes with loop.php or
 * loop-template.php, where 'template' is the loop context
 * requested by a template. For example, loop-index.php would
 * be used if it exists and we ask for the loop with:
 * <code>get_template_part( 'loop', 'index' );</code>
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
?>

<?php if(is_home()) {
  $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
  query_posts('cat=-75&orderby=date&order=DESC&paged='.$paged);
 } ?>
<?php /* If there are no posts to display, such as an empty archive page */ ?>
<?php if ( ! have_posts() ) : ?>
    <div id="post-0" class="post error404 not-found">
        <h1 class="entry-title"><?php _e( 'Not Found', 'twentyten' ); ?></h1>
        <div class="entry-content">
                <p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'twentyten' ); ?></p>
                <?php get_search_form(); ?>
        </div><!-- .entry-content -->
    </div><!-- #post-0 -->
<?php endif; ?>
<?php
    /* Start the Loop.
     *
     * In Twenty Ten we use the same loop in multiple contexts.
     * It is broken into three main parts: when we're displaying
     * posts that are in the gallery category, when we're displaying
     * posts in the asides category, and finally all other posts.
     *
     * Additionally, we sometimes check for whether we are on an
     * archive page, a search page, etc., allowing for small differences
     * in the loop on each template without actually duplicating
     * the rest of the loop that is shared.
     *
     * Without further ado, the loop:
     */ ?>
    <?php while ( have_posts() ) : the_post(); ?>
    <?php /* How to display posts of the Gallery format. The gallery category is the old way. */ ?>
	<?php if ( ( function_exists( 'get_post_format' ) && 'gallery' == get_post_format( $post->ID ) ) || in_category( _x( 'gallery', 'gallery category slug', 'twentyten' ) ) ) : ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyten' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
                    <div class="entry-meta">
                            <?php twentyten_posted_on(); ?>
                    </div><!-- .entry-meta -->
                    <div class="entry-content">
                        <?php if ( post_password_required() ) : ?>
			    <?php the_content(); ?>
                        <?php else : ?>
                            <?php
                                $images = get_children( array( 'post_parent' => $post->ID, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'orderby' => 'menu_order', 'order' => 'ASC', 'numberposts' => 999 ) );
                                if ( $images ) :
                                $total_images = count( $images );
                                $image = array_shift( $images );
                                $image_img_tag = wp_get_attachment_image( $image->ID, 'thumbnail' );
                            ?>
                            <div class="gallery-thumb">
                                    <a class="size-thumbnail" href="<?php the_permalink(); ?>"><?php echo $image_img_tag; ?></a>
                            </div><!-- .gallery-thumb -->
                            <p>
                                <em>
                                    <?php printf( _n( 'This gallery contains <a %1$s>%2$s photo</a>.', 'This gallery contains <a %1$s>%2$s photos</a>.', $total_images, 'twentyten' ),
                                        'href="' . get_permalink() . '" title="' . sprintf( esc_attr__( 'Permalink to %s', 'twentyten' ), the_title_attribute( 'echo=0' ) ) . '" rel="bookmark"',
                                        number_format_i18n( $total_images )
                                    ); ?>
                                </em>
                            </p>
			<?php endif; ?>
                        <?php the_excerpt(); ?>
                        <?php endif; ?>
		     </div><!-- .entry-content -->
                     <div class="entry-utility">
                        <?php if ( function_exists( 'get_post_format' ) && 'gallery' == get_post_format( $post->ID ) ) : ?>
                            <a href="<?php echo get_post_format_link( 'gallery' ); ?>" title="<?php esc_attr_e( 'View Galleries', 'twentyten' ); ?>"><?php _e( 'More Galleries', 'twentyten' ); ?></a>
                            <span class="meta-sep">|</span>
                        <?php elseif ( in_category( _x( 'gallery', 'gallery category slug', 'twentyten' ) ) ) : ?>
                            <a href="<?php echo get_term_link( _x( 'gallery', 'gallery category slug', 'twentyten' ), 'category' ); ?>" title="<?php esc_attr_e( 'View posts in the Gallery category', 'twentyten' ); ?>"><?php _e( 'More Galleries', 'twentyten' ); ?></a>
                            <span class="meta-sep">|</span>
                        <?php endif; ?>
                            <span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'twentyten' ), __( '1 Comment', 'twentyten' ), __( '% Comments', 'twentyten' ) ); ?></span>
                            <?php edit_post_link( __( 'Edit', 'twentyten' ), '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
                     </div><!-- .entry-utility -->
		</div><!-- #post-## -->
                <?php /* How to display posts of the Aside format. The asides category is the old way. */ ?>
                <?php  //print_r($serv);
         elseif ( ( function_exists( 'get_post_format' ) && 'aside' == get_post_format( $post->ID ) ) || in_category( _x( 'asides', 'asides category slug', 'twentyten' ) )  ) : ?>
                <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <?php if ( is_archive() || is_search() ) : // Display excerpts for archives and search. ?>
                        <div class="entry-summary">
                                <?php the_excerpt(); ?>
                        </div><!-- .entry-summary -->
                    <?php else : ?>
                    <div class="entry-content">
                            <?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?>
                    </div><!-- .entry-content -->
                    <?php endif; ?>
                    <div class="entry-utility">
                            <?php twentyten_posted_on(); ?>
                            <span class="meta-sep">|</span>
                            <span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'twentyten' ), __( '1 Comment', 'twentyten' ), __( '% Comments', 'twentyten' ) ); ?></span>
                            <?php edit_post_link( __( 'Edit', 'twentyten' ), '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
                    </div><!-- .entry-utility -->
		</div><!-- #post-## -->
                <?php /* How to display all other posts. */ ?>
                <?php else :
                    //for single.php
                    if(is_single()) {?>
                        <?php if ( function_exists('yoast_breadcrumb') ) {
                        yoast_breadcrumb('<p id="breadcrumbs">','</p>');
                        } ?>
                        <?php
			//for job post description page
			$servrs = explode('/', $_SERVER[REQUEST_URI]);	 
			if($servrs[2] == '') //for getting all the posts other than that of job posts
                   {
			$comments = $wpdb->get_row("SELECT comment_count as count FROM wp_posts WHERE ID = '$post->ID'");
			$commentcount = $comments->count;
			if($commentcount == 1): $commenttext = ''; endif;
			if($commentcount > 1 || $commentcount == 0): $commenttext = ''; endif;
			$fullcomments = $commentcount.' '.$commenttext;
			 ?>
                  <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h2 class="entry-title"><?php the_title(); ?></h2>
                        <div class="post-meta" id="post-meta-single">
                            <div class="post-comment">
                                <span>
                                    <?php
                                    global $wpdb;
                                    $postID = get_the_ID();
                                    $title = get_the_title();
                                    $comments = $wpdb->get_row("SELECT comment_count as count FROM wp_posts WHERE ID = '$postID'");
                                    $commentcount = $comments->count;
                                    if($commentcount == 1): $commenttext = ''; endif;
                                    if($commentcount > 1 || $commentcount == 0): $commenttext = ''; endif;
                                    $fullcomments = $commentcount.' '.$commenttext;
                                    echo $fullcomments;  ?>
                                </span>
                            </div>
                            <div class="printlink"><?php if(function_exists('wp_print')) { print_link(); } ?> <?php// if(function_exists('wp_email')) { email_link(); } ?>  </div>
                            <div class='st_sharethis' st_title='<?php echo  get_the_title($postID); ?>' st_url='<?php echo get_option('home')."/".$_SERVER[REQUEST_URI]; ?>' displayText='Share'></div>
                            <div class="fblike"><fb:like href="<?php the_permalink(); ?>" width="50" height="31"  layout="button_count" show_faces="false"></fb:like></div>
                            <div class="twitterlike"><iframe class="twitter-share-button" allowtransparency="true" frameborder="0" scrolling="no"
src="http://platform.twitter.com/widgets/tweet_button.html?url=<?php the_permalink()?>&amp;via=quantnet&amp;text=<?php the_title();?>&amp;count=horizontal" width="106" height="21"></iframe></div>
                            <div class="posted-on"><em>Posted on <?php the_time('M jS, Y'); ?></em></div>
			</div><!-- post-meta -->
			<div class="entry-content">
                            <?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?>
                            <?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'twentyten' ), 'after' => '</div>' ) ); ?>
			</div><!-- .entry-content -->
			<div class="post-meta" id="post-meta-single">
                            <div class="post-comment">
                                <span>
                                    <?php
                                    global $wpdb;
                                    $postID = get_the_ID();
                                    $title = get_the_title();
                                    $comments = $wpdb->get_row("SELECT comment_count as count FROM wp_posts WHERE ID = '$postID'");
                                    $commentcount = $comments->count;
                                    if($commentcount == 1): $commenttext = ''; endif;
                                    if($commentcount > 1 || $commentcount == 0): $commenttext = ''; endif;
                                    $fullcomments = $commentcount.' '.$commenttext;
                                    echo $fullcomments;  ?>
                                </span>
                            </div>
                            <div class="printlink"><?php if(function_exists('wp_print')) { print_link(); } ?> <?php// if(function_exists('wp_email')) { email_link(); } ?>  </div>
                            <div class='st_sharethis' st_title='<?php echo  get_the_title($postID); ?>' st_url='<?php echo get_option('home')."/".$_SERVER[REQUEST_URI]; ?>' displayText='Share'>
                                <script charset="utf-8" type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script><script type="text/javascript">stLight.options({publisher:'wp.640ecdaa-678d-4a4f-bafc-bea7c872c750'});var st_type='wordpress3.0.1';</script>
                            </div>
                            <div class="fblike"><fb:like href="<?php the_permalink(); ?>" width="50" height="31"  layout="button_count" show_faces="false"></fb:like></div>
                            <div class="twitterlike"><iframe class="twitter-share-button" allowtransparency="true" frameborder="0" scrolling="no"
src="http://platform.twitter.com/widgets/tweet_button.html?url=<?php the_permalink()?>&amp;via=quantnet&amp;text=<?php the_title();?>&amp;count=horizontal" width="106" height="21"></iframe></div>
                             <div class="posted-on"><em>Posted on <?php the_time('M jS, Y'); ?></em></div>
                        </div><!-- post-meta -->
                        <div class="tag-links">
                            <?php $tags_list = get_the_tag_list( '', ', ' ); if ( $tags_list ): ?>
                            <?php printf( __( '<span class="%1$s">Tags: </span> %2$s', 'twentyten' ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list ); ?>
                            <?php endif; ?>
                        </div>
                        <?php related_posts(); ?>
		 </div><!-- #post-## -->
                 <?php comments_template( '', true ); ?>
		 <?php }
		 else { echo "Sorry, this post has been expired"; } ?>
                 <?php } else { //for rest of the pages other than description page ?>
		 <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyten' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
                        <?php if ( is_archive() || is_search() || is_home() ) : // Only display excerpts for archives and search. ?>
                            <div class="entry-summary">
                                    <div class="featured-img"><?php the_post_thumbnail(); //for showing thumbnail ?></div><?php the_excerpt(); ?><?php ngg_excerpt(); ?>
                            </div><!-- .entry-summary -->
                        <?php else : ?>
			<div class="entry-content">
				<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?>
				<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'twentyten' ), 'after' => '</div>' ) ); ?>
			</div><!-- .entry-content -->
                        <?php endif; ?>
                        <?php if(!is_home() ) { ?>
                            <div class="tag-links">
                                <?php
                                    $tags_list = get_the_tag_list( '', ', ' );
                                    if ( $tags_list ):
                                ?>
                                    <?php printf( __( '<span class="%1$s">Tags:</span> %2$s', 'twentyten' ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list ); ?>
                                <?php endif; ?>
                            </div>
                        <?php //if(function_exists('the_views')) { the_views(); } ?> <?php } ?>
                        <?php edit_post_link( __( 'Edit', 'twentyten' ), '<span class="edit-link">', '</span>' ); ?>
                        <?php if(!is_page()) { ?>
                            <div class="post-meta">
                                <div class="post-comment">
                                    <span>
                                        <?php
                                        global $wpdb;
                                        $postID = get_the_ID();
                                        $title = get_the_title();
                                        $comments = $wpdb->get_row("SELECT comment_count as count FROM wp_posts WHERE ID = '$postID'");
                                        $commentcount = $comments->count;
                                        if($commentcount == 1): $commenttext = ''; endif;
                                        if($commentcount > 1 || $commentcount == 0): $commenttext = ''; endif;
                                        $fullcomments = $commentcount.' '.$commenttext;
                                        echo $fullcomments; ?>
                                        <?php if(!is_page('Submit A Review for Your Program'))//for checking Submit page
                                         { ?>
                                    </span>
                                </div>
                                <div class="fblike"> <fb:like href="<?php the_permalink(); ?>" width="50" height="31"  layout="button_count" show_faces="false"></fb:like></div>
                                <div class="posted-on"><em>Posted on <?php the_time('M jS, Y'); ?></em></div>
                            </div><!-- .entry-meta -->
                            <?php } ?>
                        <?php }//condition for submit page ends here ?>
                </div><!-- #post-## -->
                <?php } //end single loop here  ?>
                <?php endif; // This was the if statement that broke the loop into three parts based on categories. ?>
            <?php endwhile; // End the loop. Whew. ?>
            <?php /* Display navigation to next/previous pages when applicable */ ?>
            <?php if(function_exists('wp_pagenavi')) { wp_pagenavi(); }  else { ?>
            <?php if (  $wp_query->max_num_pages > 1 ) : ?>
                    <div id="nav-below" class="navigation">
                            <div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'twentyten' ) ); ?></div>
                            <div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?></div>
                    </div><!-- #nav-below -->

            <?php endif; ?>
            <?php } ?>
