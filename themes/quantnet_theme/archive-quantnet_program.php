<?php get_header(); ?>

<div id="container">
	<div id="content" role="main" class="review">
		<?php
		//quantnet_set_reviews_ranking();
		//exit();
		//Get all of the programs to be displayed
		if($_GET['sort'] == "name"):
			$wpsc_query = new WP_Query( 
				array(
					'post_type'=>'quantnet_program',
					'post_status'=>'publish',
					'orderby'=>'page_title',
					'order'=>'ASC'
				)
			);
		elseif($_GET['sort'] == "reviews"):
			$wpsc_query = new WP_Query( 
				array(
					'post_type'=>'quantnet_program',
					'post_status'=>'publish',
					'meta_key'=>'number_of_reviews',
					'orderby'=>'meta_value_num',
					'order'=>'ASC'
				)
			);
		else:
			$wpsc_query = new WP_Query( 
				array(
					'post_type'=>'quantnet_program',
					'post_status'=>'publish',
					'meta_key'=>'ranking',
					'orderby'=>'meta_value_num',
					'order'=>'ASC'
				)
			);
		endif;
		?>
		
		<?php if($wpsc_query->have_posts()) : ?>
			<div class="ratesorting">
            	<span class="sortby"></span>
				<a href="<?php echo $_SERVER['REQUEST_URI'];?>" class="desc">Rating</a><a href="<?php echo $_SERVER['REQUEST_URI'];?>?sort=name" class="brder">Program Name</a><a href="<?php echo $_SERVER['PHP_SELF'];?>?reviews">Number of Reviews</a>
			</div>
			<?php foreach($wpsc_query->get_posts() as $post) : setup_postdata($post); $review_details = quantnet_review_details(get_the_id()); ?>
				<?php //start loop ?>
				<div id="post" <?php post_class(); ?>>
					<div class="entry-summary">
						<div class="main-image">
							<?php
							$images = quant_get_all_program_images(get_the_id());
							if(count($images) > 0):
								?>
								<img src="<?php echo $images[0];?>" width="206" />
								<?php
							else:
								?>
								<img src="DEFAULT IMAGE" width="206" />
								<?php
							endif;
							?>
						</div>
						<div class="main-entry">
							<h2><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyten' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
							<p><span class="ranking">#<?php echo $review_details['ranking']; ?></span> of <?php echo count($wpsc_query->get_posts());?> programs reviewed</p>
							<p><span class="mfe_ranking">Ranked #<?php echo $review_details['mfe_ranking']; ?></span> in Quantnet MFE Ranking</p>
							<p class="average_rating">Average rating: <span class="rating"><?php echo $review_details['average_rating']; ?></span>/10 (<span class="reviews"><?php echo $review_details['total_reviews'];?> reviews</span>)</p>
                            <?php the_excerpt(); ?>
							<p >Wiki Link: <a href="<?php echo $review_details['wiki_link'];?>" target="_blank" title="<?php the_title();?> - Wikipedia"><?php echo $review_details['wiki_link'];?></a><br />
							<a href="">Write A Review</a></p>
						</div>
                        <div class="clear"></div>
					</div><!-- .entry-summary -->
				</div><!--post-->
				<?php //end loop ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div><!-- #content -->
</div><!-- #container -->

<?php get_sidebar('right'); ?>
<?php get_footer(); ?>
