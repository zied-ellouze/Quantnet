<?php if ( have_posts() ) while ( have_posts() ) : the_post(); $review_details = quantnet_review_details(get_the_id()); $program_id = get_the_id(); ?>
	<?php //start loop ?>
	<div id="post" <?php post_class(); ?>>
		<div class="entry-summary">
			<h1><?php the_title(); ?></h1>
			<div class="main-image">
				<?php
				$images = quant_get_all_program_images(get_the_id(), 4);
				if(count($images) > 0): $i=0;
					foreach($images as $image):
						if($i==0):
							?>
							<div class="main-image">
								<a href="/gallery/?id=<?php the_id();?>&picture_id=<?php echo md5($image); ?>"><img src="<?php echo $image; ?>" width="500" border="0">
							</div>
							<?php
						else:
							?>
							<div class="small-image" style="float: left; width: 100px;">
								<a href="/gallery/?id=<?php the_id();?>&picture_id=<?php echo md5($image); ?>"><img src="<?php echo $image; ?>" width="100" border="0"></a>
							</div>
							<?php
						endif;
						$i++;
					endforeach;
				endif;
				?>
			</div>
			<div style="clear: both;"></div>
			<div class="individual-entry">
				<p><span class="ranking">#<?php echo $review_details['ranking']; ?></span> of <?php echo $wpdb->query("SELECT id FROM wp_posts WHERE post_type = 'quantnet_program' AND post_status = 'publish'"); ?> programs reviewed</p>
				<p><span class="mfe_ranking">Ranked #<?php echo $review_details['mfe_ranking']; ?></span> in Quantnet MFE Ranking</p>
				<p class="average_rating">Average rating: <span class="rating"><?php echo $review_details['average_rating']; ?></span>/10 (<?php echo $review_details['total_reviews'];?> reviews)</p>
			</div>
			<div class="main-entry-desc">
				<p class="wiki-link">Wiki Link: <a href="<?php echo $review_details['wiki_link'];?>" target="_blank" title="<?php the_title();?> - Wikipedia"><?php echo $review_details['wiki_link'];?></a></p>
				<?php the_content(); ?>
			</div>
		</div><!-- .entry-summary -->
	</div><!--post-->
	<p>Posted in: <?php the_category(', '); ?></p>
	<p><?php the_tags(); ?></p>
	<?php //end loop ?>
	<div class="reviews">
		<?php
		//quantnet_set_reviews_ranking();
		//exit();
		//Get all of the programs to be displayed
		if($_GET['sort'] == "date"):
			$wpsc_query = new WP_Query( 
				array(
					'post_type'=>'quantnet_review',
					'post_status'=>'publish',
					'post_parent'=>get_the_id(),
					'orderby'=>'post_date',
					'order'=>'DESC'
				)
			);
		else:
			$wpsc_query = new WP_Query( 
				array(
					'post_type'=>'quantnet_review',
					'post_status'=>'publish',
					'post_parent'=>get_the_id(),
					'meta_key'=>'rating',
					'orderby'=>'meta_value_num',
					'order'=>'DESC'
				)
			);
		endif;
		?>
		<?php if($wpsc_query->have_posts()) : ?>
			<div class="navigation">
				<a href="<?php echo parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);?>">Rating</a> - <a href="<?php echo parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);?>?sort=date">Date</a>
			</div>
			<?php foreach($wpsc_query->get_posts() as $post) : setup_postdata($post); $r_images = quant_get_all_review_images(get_the_id(), 4); ?>
				<?php //start loop ?>
				<div id="entry">
					<h2><?php the_title(); ?></h2>
					<p>Rating: <?php echo get_post_meta(get_the_id(), 'rating', true); ?>/10 on <?php echo date("F j, Y", strtotime($post->post_date)); ?></p>
					<p><?php the_content(); ?></p>
					<?php if(count($r_images)> 0): foreach($r_images as $image): ?>
						<?php if($key): ?>
							<a href="/gallery/?id=<?php echo $program_id;?>&picture_id=<?php echo md5($image); ?>"><img src="<?php echo $image;?>" width="100" border="0"></a>
						<?php else: ?>
							<img src="<?php echo $image;?>" width="100">
						<?php endif; ?>
					<?php endforeach; endif; ?>
				</div>
				<?php //end loop ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
	<?php
	$categories = wp_get_post_terms( $program_id, "program_type", $args );
	if(count($categories) > 0):
		echo "<p>Posted in: ";
		$i=0;
		foreach($categories as $category):
			if($i!=0)
				echo ", ";
			echo "<a href='/program_type/".$category->slug."'>".$category->name."</a>";
			$i++;
		endforeach;
		echo "</p>";
	endif;
	$tags = wp_get_post_terms( $program_id, "program_tag", $args );
	if(count($tags) > 0):
		echo "<p>Tags: ";
		$i=0;
		foreach($tags as $tag):
			if($i!=0)
				echo ", ";
			echo "<a href='/program_tag/".$tag->slug."'>".$tag->name."</a>";
			$i++;
		endforeach;
		echo "</p>";
	endif;
	?>
<?php endwhile; ?>