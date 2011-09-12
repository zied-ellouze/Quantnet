<?php
/**
Template Name: Job postings
 * The template for displaying job postings posts.
 *
 * This is the template that displays all posts from job postings category.
 
 */

get_header(); ?>

		<div id="container">
			<div id="content" role="main">
			<?php
			$lctn = $_POST['location'];
			$Compensatin = $_POST['Compensation'];
			$tag_drop = $_POST['tag-dropdn'];
			?>
			<form action="" method="POST" name="form">
			Filter By : Location
			<select style="display:block;" name="location" id="filterby" onchange="document.forms['form'].submit()">
                            <option value="">--Location--</option>
                            <?php $loc = $wpdb->get_results(" SELECT meta_value FROM `wp_postmeta` WHERE meta_key = 'job_location' LIMIT 0 , 30 "); 
                            foreach($loc as $job_loc) { 
                            $location = $job_loc->meta_value;
			    ?>
                            <option value="<?=$location?>" name="<?=$location?>" id="<?=$location?>" <?php if ($lctn == '$location') { echo "selected='selected'";}?>><?=$location?></option>
                            <?php  } ?>
                        </select> 
			 Compensation
			<select style="display:block;" name="Compensation" id="filterby" onchange="document.forms['form'].submit()">
                            <option value="">--Compensation--</option>
                            <?php $compen = $wpdb->get_results("SELECT meta_value FROM `wp_postmeta` WHERE meta_key = 'compensation'"); 
                            foreach($compen as $job_com) { 
                            $compensation = $job_com->meta_value; 
				if(is_numeric($compensation)) 
				{
                            ?>
                            <option value="<?=$compensation?>" name="<?=$compensation?>" id="<?=$compensation?>" <?php if ($Compensatin == '$compensation') { echo "selected";}?>><?=$compensation?></option>
                            <?php } } ?>
                        </select> 
			Tags 
			<?php $args = array(
			'categories' => '68'
			);
			$tags = get_category_tags($args);
			?>
			<!--select name="tag-dropdown" onchange="document.location.href=this.options[this.selectedIndex].value;">
			<option value="#">Select</option>
			<?php dropdown_tag_cloud('number=0&order=asc'); ?>
			</select-->	
			</form>
			<?php
			//echo "<pre>"; print_r($_REQUEST); echo "</pre>";
			
			 $today= getdate();
			$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
			if(!empty($lctn))
			{
			query_posts( 'post_type=job_post&meta_key=job_location&meta_value='.$lctn.'&paged='.$paged ); //showing posts from Job Postings category
			}
			else if(!empty($Compensatin))
			{
			query_posts( 'post_type=job_post&meta_key=compensation&meta_value='.$Compensatin.'&paged='.$paged ); //showing posts from Job Postings category for compensation 
			}
			else if(!empty($tag_drop))
			{
			//echo 'post_type=job_post&taxonomy_name='.$_POST["tag-dropdown"].'&meta_key=job_posting_expiration&meta_compare=>=&meta_value='.$date["Y-m-d"].'&orderby=date&order=DESC&paged='.$paged;
			query_posts( 'post_type=job_post&tag="'.$tag_drop.'"&orderby=date&order=DESC&paged='.$paged ); //showing posts from Job Postings category
			}
			else
			{
			query_posts( 'post_type=job_post&meta_key=job_posting_expiration&meta_compare=>=&meta_value='.$date["Y-m-d"].'&orderby=date&order=DESC&paged='.$paged ); //showing posts from Job Postings category
			}
			if(have_posts()):
			while ( have_posts() ) : the_post(); ?>
 			 <div id="post" <?php post_class(); ?>>
			<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyten' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>

			<div class="entry-meta">
				<?php twentyten_posted_on(); ?>
			</div><!-- .entry-meta -->

	
			<div class="entry-summary">
			<div align="left"><?php the_post_thumbnail(''); //for showing thumbnail ?></div>
	
		<?php  $TEXT = get_post_meta($post->ID, 'job_description' , true);  $LIMIT='250'; ?>
                                        <?php echo preview_text($TEXT, $LIMIT, $TAGS = 0, '');  ?>
			</div><!-- .entry-summary -->

			<div><iframe class="twitter-share-button" allowtransparency="true" frameborder="0" scrolling="no"
src="http://platform.twitter.com/widgets/tweet_button.html?url=<?php the_permalink()?>&amp;via=quantnet&amp;text=<?php the_title();?>&amp;count=horizontal" width="130" height="21"></iframe>

<fb:like profile_id="" href="<?php the_permalink(); ?>" width="300" height="31"  layout="button_count" show_faces="false"></fb:like></div>
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
 
			<?php endwhile;?>
			<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php if (function_exists('seopagebar')) { seopagebar(); } else { ?>

<div id="nav-below" class="navigation">
					<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'twentyten' ) ); ?></div>
					<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?></div>
				</div><!-- #nav-below -->

<?php } ?>

			<?php else:
			echo "Sorry, no posts found";
			endif;			
			// Reset Query
			 wp_reset_query();

			?>
			

			
			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
