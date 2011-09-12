<?php
/**
Template Name: Full Review
 * The template for displaying review posts for a particular program.
 *
 * This is the template that displays all review posts for a particular program.
 
 */

get_header();

?>

<script src="http://www.google.com/jsapi?key=ABQIAAAA1z2R5qvV2le8XlHGBMQW_BT2p0lhzy-giP_0zK-c010Lfxp0GhRrBN2wCdM7TBNC0YRkYDixL7chxg" type="text/javascript">
</script>
<script type="text/javascript">
google.load("jquery", "1"); 

</script>
<script type="text/javascript">
//<![CDATA[
function showSlidingDiv(val){
//alert("#slidingDiv"+val);
$("#slidingDiv"+val).animate({"height": "toggle"}, { duration: 1000 });
}
//]]>
</script>
		<div id="container">
			<div id="content" role="main">
			<?php $ur = explode("?",$_SERVER['REQUEST_URI']);
			//echo "<pre>"; print_r($ur); echo "</pre>";
			$strval = rawurldecode($ur[1]);
			 ?>
			<?php
			 //query for rest of the cases
			$order = " ORDER BY $wpdb->posts.post_date DESC";
		
			$total = "SELECT * FROM $wpdb->posts
				LEFT JOIN $wpdb->postmeta ON($wpdb->posts.ID = $wpdb->postmeta.post_id)
				LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
				LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
				LEFT JOIN $wpdb->terms ON($wpdb->terms.term_id = $wpdb->term_taxonomy.term_id)
				WHERE $wpdb->terms.name = 'Review'
				AND $wpdb->term_taxonomy.taxonomy = 'category'
				AND $wpdb->posts.post_status = 'publish'
				AND $wpdb->posts.post_type = 'post'
				AND $wpdb->postmeta.meta_key = 'program_name' 
				AND $wpdb->postmeta.meta_value = '$strval' $order";
			
					
			$totalposts = $wpdb->get_results($total, OBJECT);

			$ppp = intval(get_query_var('posts_per_page'));

			$wp_query->found_posts = count($totalposts);

			$wp_query->max_num_pages = ceil($wp_query->found_posts / $ppp);

			$on_page = intval(get_query_var('paged'));	

			if($on_page == 0){ $on_page = 1; }		

			$offset = ($on_page-1) * $ppp;
			
			
				$wp_query->request = "SELECT * FROM $wpdb->posts
				LEFT JOIN $wpdb->postmeta ON($wpdb->posts.ID = $wpdb->postmeta.post_id)
				LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
				LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
				LEFT JOIN $wpdb->terms ON($wpdb->terms.term_id = $wpdb->term_taxonomy.term_id)
				WHERE $wpdb->terms.name = 'Review'
				AND $wpdb->term_taxonomy.taxonomy = 'category'
				AND $wpdb->posts.post_status = 'publish'
				AND $wpdb->posts.post_type = 'post'
				AND $wpdb->postmeta.meta_key = 'program_name' AND $wpdb->postmeta.meta_value = '$strval' $order LIMIT $ppp OFFSET $offset";
			
		
			$pageposts = $wpdb->get_results($wp_query->request, OBJECT);
			 if ($pageposts): ?>
 			 <?php global $post; 
			$i = 1;			
				?>
 			 <?php foreach ($pageposts as $post): ?>
    			<?php setup_postdata($post); ?>
			<?php 
				$pr_name = get_post_meta($post->ID, 'program_name',true);				
				 $tot = mysql_query("SELECT count(*) as count FROM $wpdb->posts
				LEFT JOIN $wpdb->postmeta AS programtype ON(
				$wpdb->posts.ID = programtype.post_id
				AND programtype.meta_key = 'program_name'
				)
				LEFT JOIN $wpdb->postmeta AS reviewrate ON(
				$wpdb->posts.ID = reviewrate.post_id
				AND reviewrate.meta_key = 'review_rate'
				)
				LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
				LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
				WHERE $wpdb->term_taxonomy.term_id = 74
				AND $wpdb->term_taxonomy.taxonomy = 'category'
				AND $wpdb->posts.post_status = 'publish'
				AND programtype.meta_value ='$pr_name' 
				"); $row = mysql_fetch_array($tot);  //print_r($row);?>
 			 <div id="post" <?php post_class(); ?>>
			<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyten' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>

			<div class="entry-meta">
				<?php twentyten_posted_on(); ?>
			</div><!-- .entry-meta -->

	
			<div class="entry-summary">
			<div align="left">
			<?php $photos = get_post_meta($post->ID, 'review_photo1', true); 
				$photos2 = get_post_meta($post->ID, 'review_photo2', true);			
			//for showing thumbnail
			if(!empty($photos))
			{ ?>
				<img src="<?=$photos?>" height="198" />
			<?php }
			else if(!empty($photos2)) {	?>
				<img src="<?=$photos2?>" height="198" />		
			<?php }
			 ?>
			</div>
			<?php the_content(); ?>
			</div><!-- .entry-summary -->
			<!--for showing custom fields values-->
			<a href="#" onClick="showSlidingDiv('<?=$i?>'); return false;">more >></a>
			<div id ="slidingDiv<?=$i?>" style="display:none;">
			 Fill this space with really interesting content that you can <a href="#" onClick="showSlidingDiv('<?=$i?>'); return false;">less >></a>
			</div>
	
			<div class="clear"></div>
			<div><iframe class="twitter-share-button" allowtransparency="true" frameborder="0" scrolling="no"
src="http://platform.twitter.com/widgets/tweet_button.html?url=<?php the_permalink()?>&amp;via=quantnet&amp;text=<?php the_title();?>&amp;count=horizontal" width="130" height="21"></iframe>

<fb:like profile_id="" href="<?php the_permalink(); ?>" width="300" height="31"  layout="button_count" show_faces="false"></fb:like>
 <span class='st_sharethis' st_title='<?php the_title(); ?>' st_url='<?php the_permalink(); ?>' displayText='Share'></span>
<script charset="utf-8" type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script><script type="text/javascript">stLight.options({publisher:'wp.640ecdaa-678d-4a4f-bafc-bea7c872c750'});var st_type='wordpress3.0.1';</script>
<a href='<?php the_permalink(); ?>?print=1' rel='nofollow' ><img src='<?php echo get_settings('home') ?>/wp-content/plugins/wp-print/images/print.gif' /> Print</a>
</div>
			<div class="entry-utility">
				<?php if ( count( get_the_category() ) ) : ?>
					<span class="cat-links">
						<?php printf( __( '<span class="%1$s">Posted in</span> %2$s', 'twentyten' ), 'entry-utility-prep entry-utility-prep-cat-links', get_the_category_list( ', ' ) ); ?>
					</span>
					<span class="meta-sep">|</span>
				<?php endif; ?>
				<?php
					$tags_list = get_the_tag_list( '', ', ' );
					if ( $tags_list ):
				?>
					<span class="tag-links">
						<?php printf( __( '<span class="%1$s">Tagged</span> %2$s', 'twentyten' ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list ); ?>
					</span>
					<span class="meta-sep">|</span>
				<?php endif; ?>
				<span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'twentyten' ), __( '1 Comment', 'twentyten' ), __( '% Comments', 'twentyten' ) ); ?></span><span class="meta-sep">|</span><?php if(function_exists('the_views')) { the_views(); } ?>
				<?php edit_post_link( __( 'Edit', 'twentyten' ), '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
			
			</div><!-- .entry-utility -->
			</div><!--post-->
 
			 <?php $i++; endforeach; ?>
 

			<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php if (function_exists('seopagebar')) { seopagebar(); } else { ?>

<div id="nav-below" class="navigation">
					<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'twentyten' ) ); ?></div>
					<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?></div>
				</div><!-- #nav-below -->

<?php } ?>

			<?php else : ?>
    <h2 class="center">Not Found</h2>
    <p class="center">Sorry, but you are looking for something that isn't here.</p>
    <?php include (TEMPLATEPATH . "/searchform.php"); ?>
 <?php endif; ?>
			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
