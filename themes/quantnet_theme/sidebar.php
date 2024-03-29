<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
?>
<div id="primary" class="widget-area" role="complementary">
        <?php if(is_page('About Us') || is_page('Contact Us') || is_page('Advertising Opportunities') || is_page('Write For Us')) {?>
            <div class="side-nav"><ul><?php wp_list_pages('include=981,614,4,506&title_li='); ?></ul></div>
        <?php } else { ?>
        <?php if(is_single()) { ?>
            <div class="authbio">
                <div class="autherinfo">
                    <h5><?php the_author(); ?></h5>
                    <?php  $postID = get_the_ID();
                    $post_id = get_post($postID, ARRAY_A);
                    $desc = get_the_author_meta('description');?>
                     <?php if(userphoto_exists($post_id[post_author])) { ?>
                    <div class="autherimg">
                         <?php  userphoto($post_id[post_author]);?>
                    </div>
                    <?php } ?>
                    <?php if(!empty($desc)) { ?>
                    <div class="autherdec">
                            <?php echo $desc; ?>
                            <br clear="right"/>
                            <a href="<?php bloginfo('url'); ?>/author/<?=get_the_author_meta('nicename')?>" class="readmore">Read More</a>
                    </div>
                    <?php } ?>
                 </div>
                 <ul>
                    <h5>Blogs <span>(<a href="<?php bloginfo('url'); ?>/author/<?=get_the_author_meta('nicename')?>">view all</a>)</span></h5>
                    <?php query_posts('author='.$post_id[post_author].'&showposts=5');?>
                    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                     <li>
                       <a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link: <?php the_title(); ?>"><?php the_title(); ?></a>
                     </li>
                     <?php endwhile; else: ?>
                     <p><?php _e('No posts by this author.'); ?></p>
                 </ul>
                 <?php endif;wp_reset_query(); ?>
            </div>
        <?php } ?>
        <!--showing author's bio, and his 5 latest posts-->
        <!--showing latest Reviews-->
        <div class="reviews-section">
            <h2 class='widget-title'>LATEST REVIEWS <span>(<a href="<?php bloginfo('url'); ?>/reviews/">view all</a>)</span></h2>
            <!--for showing two latest posts-->
             <ul>
                <?php //showing first two latest reviews
					$wpsc_query = new WP_Query( 
						array(
								'post_type'=>'quantnet_review',
								'post_status'=>'publish',
								'showposts' =>2,
								'orderby'=>'date',
								'order'=>'DESC'
									)
								);
				?>
                    <?php  if($wpsc_query->have_posts()) : ?>
                    <?php foreach($wpsc_query->get_posts() as $post) : setup_postdata($post);
							$rating = get_post_meta($post->ID, 'rating', true); 
							//print_r($post);
							if($rating <= '1') {  $class = "class = 'rating_1 latestrating'";}
							else if ($rating <= '2') {  $class = "class = 'rating_2 latestrating'";}
							else if ($rating <= '3') {  $class = "class = 'rating_3 latestrating'";}
							else if ($rating <= '4') {  $class = "class = 'rating_4 latestrating'";}
							else if ($rating <= '5') {  $class = "class = 'rating_5 latestrating'";}
							else if ($rating <= '6') { $class = "class = 'rating_6 latestrating'";}
							else if ($rating <= '7') {  $class = "class = 'rating_7 latestrating'";}
							else if ($rating <= '8') {  $class = "class = 'rating_8 latestrating'";}
							else if ($rating <= '9') {  $class = "class = 'rating_9 latestrating'";}
							else if ($rating <= '10') {  $class = "class = 'rating_10 latestrating'";}
                           
                            ?>
                        	<li <?=$class?> >
                                <?php $parent = $post->post_parent;?>
								 <a href="<?php echo get_permalink($parent); ?>" rel="bookmark" title="Permanent Link: <?php echo get_the_title($parent); ?>"><?php echo get_the_title($parent); ?></a>
                               
                                <small><?php the_time('F jS, Y'); ?></small>
                                <span class="rating" ><?=$rating;?></span>
                                <?php  ?>
                         </li>
                     <?php endforeach; else: ?>
                     <p><?php _e('No posts by this author.'); ?></p>
                <?php endif; ?>
                <?php //showing 3 latest reviews other than the first two
						$wpsc_query = new WP_Query(
						array(
								'post_type'=>'quantnet_review',
								'post_status'=>'publish',
								'showposts' =>3,
								'orderby'=>'date',
								'order'=>'DESC',
								'offset'=> '2'
									)
								); 
				?>
                    <?php  if($wpsc_query->have_posts()) : ?>
                    <?php foreach($wpsc_query->get_posts() as $post) : setup_postdata($post);
							$ratings = get_post_meta($post->ID, 'rating', true); 
							
                            if($ratings <= '1') {  $class = "class = 'rating_1 '";}
                            else if ($ratings <= '2') {  $class = "class = 'rating_2 '";}
                            else if ($ratings <= '3') {  $class = "class = 'rating_3 '";}
                            else if ($ratings <= '4') {  $class = "class = 'rating_4 '";}
                            else if ($ratings <= '5') {  $class = "class = 'rating_5 '";}
                            else if ($ratings <= '6') { $class = "class = 'rating_6 '";}
                            else if ($ratings <= '7') {  $class = "class = 'rating_7 '";}
                            else if ($ratings <= '8') {  $class = "class = 'rating_8 '";}
                            else if ($ratings <= '9') {  $class = "class = 'rating_9 '";}
                            else if ($ratings <= '10') {  $class = "class = 'rating_10 '";}
					?>
					
                      	<li <?=$class?> >
                                <?php $parent = $post->post_parent;?>
								 <a href="<?php echo get_permalink($parent); ?>" rel="bookmark" title="Permanent Link: <?php echo get_the_title($parent); ?>"><?php echo get_the_title($parent); ?></a>
                               
                                <small><?php the_time('F jS, Y'); ?></small>
                                <span class="rating" ><?=$ratings;?></span>
                                <?php  ?>
                         </li>
                     <?php endforeach;?>
                <?php endif; ?>
            </ul>
         </div>
        <!--showing Big Banner Ads-->
        <ul id="banner-add">
            <?php
            if ( ! dynamic_sidebar( 'secondary-widget-area' ) ) : ?>
            <?php endif; // end secondary widget area ?>
        </ul>
        <?php if (!is_home()){ ?>
        <ul id="banner-add">
            <?php
            if ( ! dynamic_sidebar( 'first-footer-widget-area' ) ) : ?>
            <?php endif; // end secondary widget area ?>
        </ul>
        <?php } ?>
        <!--showing Latest Comments-->
        <div class="latestcomments">
            <h2 class='widget-title'>LATEST COMMENTS</h2>
            <ul>
                <?php query_posts('orderby=comment_count&showposts=4');
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
                        <li><span class="commentcount"><?=$commentcount;?></span> <span class="commentlink"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php echo $title; ?> </a></span></li>
                    <?php endwhile; ?>
                    <?php else : ?>
                <li>Sorry, no posts were found.</li>
                <?php endif;
                 wp_reset_query();?>
            </ul>
        </div>
        <?php if (!is_home()){ ?>
            <!--showing latest posts for today, this month, overall-->
            <div class="popular-section">
                <h2 class='widget-title'>LATEST ON QUANTNET</h2>
                <?php include(TEMPLATEPATH.'/latest_posts.php'); ?>
             </div>
        <?php } ?>
        <!--showing popular posts for viewed, emailed, commented-->
        <div class="popular-section">
            <h2 class='widget-title'>POPULAR ON QUANTNET</h2>
            <?php include(TEMPLATEPATH.'/popular-section.php'); ?>
         </div>
        <!--showing Banner Ads-->
        <ul id="single-add">
            <?php
            if ( ! dynamic_sidebar( 'primary-widget-area' ) ) : ?>
            <?php endif; // end primary widget area ?>
        </ul>
          <!-- poll widget-->
        <div class="pollarea">
            <?php if (function_exists('vote_poll') && !in_pollarchive()): //for showing poll ?>

                    <h2 class="widget-title">Polls</h2>
                    <?php get_poll();?>
                    <?php //display_polls_archive_link(); ?>

            <?php endif; ?>
        </div>
        <!-- poll widget-->
         <?php } ?>
</div><!-- #primary .widget-area -->


