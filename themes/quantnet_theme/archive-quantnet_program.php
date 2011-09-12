<?php get_header(); ?>

<div id="container">
	<div id="content" role="main" class="review">
		<?php
		//quantnet_set_reviews_ranking();
		//exit();
		//Get all of the programs to be displayed
		$direction = "ASC";
		if(isset($_GET['dir'])):
			if($_GET['dir']=="desc"):
				$direction = "DESC";
			endif;
		endif;
		if($_GET['sort'] == "name"):
			$wpsc_query = new WP_Query( 
				array(
					'post_type'=>'quantnet_program',
					'post_status'=>'publish',
					'orderby'=>'title',
					'order'=>$direction
				)
			);
		elseif($_GET['sort'] == "reviews"):
			$wpsc_query = new WP_Query( 
				array(
					'post_type'=>'quantnet_program',
					'post_status'=>'publish',
					'meta_key'=>'number_of_reviews',
					'orderby'=>'meta_value_num',
					'order'=>$direction
				)
			);
		else:
			$wpsc_query = new WP_Query( 
				array(
					'post_type'=>'quantnet_program',
					'post_status'=>'publish',
					'meta_key'=>'ranking',
					'orderby'=>'meta_value_num',
					'order'=>$direction
				)
			);
		endif;
		?>
		
		<?php if($wpsc_query->have_posts()) : ?>
			<div class="ratesorting">
        <span class="sortby"></span>
				<a href="?sort=rating&dir=<?php if($_GET['dir']=="desc"&&$_GET["sort"]=="rating") echo "asc"; else echo "desc"; ?>" class="<?php if($_GET['sort']=="rating"&&isset($_GET['dir'])) echo $_GET['dir']." bld"; else if($_GET['sort']=="rating"||!isset($_GET['sort'])) echo "asc bld"; ?>">Rating</a><a href="?sort=name&dir=<?php if($_GET['dir']=="asc"&&$_GET["sort"]=="name") echo "desc"; else echo "asc"; ?>" id="brder" class="<?php if($_GET['sort']=="name"&&isset($_GET['dir'])) echo $_GET['dir']." bld"; else if($_GET['sort']=="name") echo "asc bld"; ?>">Program Name</a><a href="?sort=reviews&dir=<?php if($_GET['dir']=="desc"&&$_GET["sort"]=="reviews") echo "asc"; else echo "desc"; ?>" class="<?php if($_GET['sort']=="reviews"&&isset($_GET['dir'])) echo $_GET['dir']." bld"; else if($_GET['sort']=="reviews") echo "asc bld"; ?>">Number of Reviews</a>
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
