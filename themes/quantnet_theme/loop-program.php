<?php if ( have_posts() ) while ( have_posts() ) : the_post(); $review_details = quantnet_review_details(get_the_id()); ?>
	<?php //start loop ?>
	<div id="post" <?php post_class(); ?>>
		<div class="gallerysummary">
        <div class="post-meta" id="post-meta-single">
        <div class="printlink"><?php if(function_exists('wp_print')) { print_link(); } //print link?>  </div>
        <!--Share this code-->
        <div class='st_sharethis' st_title='<?php echo  get_the_title($postID); ?>' st_url='<?php echo get_option('home')."/".$_SERVER[REQUEST_URI]; ?>' displayText='Share'>
                                <script charset="utf-8" type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script><script type="text/javascript">stLight.options({publisher:'wp.640ecdaa-678d-4a4f-bafc-bea7c872c750'});var st_type='wordpress3.0.1';</script>
                            </div>
           </div>
        	<div class="gallerycontent">
            <h2><?php the_title(); ?></h2>
			<div class="galleryimage">
            
           
				<?php
				$images = quant_get_all_program_images(get_the_id(), 4);
				if(count($images) > 0): $i=0; ?>
                <div class="bigimage">
				<a href="/gallery/?id=<?php the_id();?>&picture_id=<?php echo md5($images[0]); ?>"><img src="<?php echo $images[0]; ?>" width="570" border="0">
			</div>
                 <div class="small-image">
                <?php
					foreach($images as $image):
						if($i==0):
				
						else:
							?>
							<div>
								<a href="/gallery/?id=<?php the_id();?>&picture_id=<?php echo md5($image); ?>"><img src="<?php echo $image; ?>"  border="0"></a>
							</div>
							<?php
						endif;
						$i++;
					endforeach;?>
                    </div>
                    <?php
				endif;
				?>
			</div>
			<div class="individual-entry">
				<div class="rank1"><span>#<?php echo $review_details['ranking']; ?></span> of <?php echo quantnet_total_reviews(); ?><br /> programs reviewed</div>
				<div class="rank2"><span>Ranked #<?php echo $review_details['mfe_ranking']; ?></span> in<br /> Quantnet MFE Ranking</div>
				<div class="rank3">Average rating:<br /> <span class="rating"><?php echo $review_details['average_rating']; ?></span>/10 (<?php echo $review_details['total_reviews'];?> reviews)</div>
			</div>
			<div class="main-entry-desc">
				<?php the_content(); ?>
                <p class="wiki-link">Wiki Link: <a href="<?php echo $review_details['wiki_link'];?>" target="_blank" title="<?php the_title();?> - Wikipedia"><?php echo $review_details['wiki_link'];?></a></p>
               	<a name="review"></a>
                <?php
               echo $addreview =  "<p class='write-a-review'><a href='' onClick='jQuery(\"#add_program_review\").toggle(\"slow\"); return false;'>Add A Review</a></p><div id='add_program_review' style='display: none;'>".do_shortcode("[gravityform id=1 name=AddReview]")."</div>";
			   // echo $add_r = check_for_gravity_form( $addreview);
			   echo  '<script type="text/javascript">var count_files=0; var total_files = jQuery("input:file").length; jQuery("input:file").each(function(){ var id = this.id; var field_id = id.replace("input_","field_"); if(count_files != 0){ jQuery("#"+field_id).attr("style","display: none;"); } if(count_files == (total_files-1)){ jQuery("#"+field_id).after("<li><p><a href=\'\' onClick=\'showNext(); return false;\' class=\'addimage\'>Add Another Image</a></p></li>"); } count_files++; }); function showNext(){ jQuery("input:file").each(function(){ var id = this.id; var field_id = id.replace("input_","field_"); if(!jQuery("#"+field_id).is(":visible")){ jQuery("#"+field_id).show("slow"); return false; } }); }</script>';
			   
			   if((isset($_GET['r']) && $_GET['r'] == "t") || $_POST):
			   ?>
			   <script type="text/javascript">
			   jQuery("#add_program_review").toggle("slow");
			   </script>
			   <?php
			   endif;
				?>
			</div>
            </div>
		</div><!-- .entry-summary -->
	</div><!--post-->
	<?php //end loop ?>
	<div class="reviews">
		<?php
		$direction = "ASC";
		if(isset($_GET['dir'])):
			if($_GET['dir']=="desc"):
				$direction = "DESC";
			endif;
		endif;
		//Get all of the programs to be displayed
		if($_GET['sort'] == "date"):
			$wpsc_query = new WP_Query( 
				array(
					'post_type'=>'quantnet_review',
					'post_status'=>'publish',
					'post_parent'=>get_the_id(),
					'orderby'=>'date',
					'order'=>$direction
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
					'order'=>$direction
				)
			);
		endif;
		?>
		<?php if($wpsc_query->have_posts()) : ?>
			<div class="ratesorting">
            	<span class="sortby"></span>
				<a href="?sort=rating&dir=<?php if($_GET['dir']=="desc"&&$_GET["sort"]=="rating") echo "asc"; else echo "desc"; ?>" class="<?php if($_GET['sort']=="rating"&&isset($_GET['dir'])) echo $_GET['dir']." bld"; else if($_GET['sort']=="rating"||!isset($_GET['sort'])) echo "asc bld"; ?>">Rating</a><a href="?sort=date&dir=<?php if($_GET['dir']=="asc"&&$_GET["sort"]=="date") echo "desc"; else echo "asc"; ?>" id="brder" class="<?php if($_GET['sort']=="date"&&isset($_GET['dir'])) echo $_GET['dir']." bld"; else if($_GET['sort']=="date") echo "asc bld"; ?>">Date</a>
			</div>
			<?php foreach($wpsc_query->get_posts() as $post) : setup_postdata($post); $images = quant_get_all_review_images(get_the_id(), 4); ?>
				<?php //start loop ?>
				<div id="userreview">
					<h2>"<?php the_title(); ?>"</h2>
					<div class="ratingarea">Rating: <strong><?php echo get_post_meta(get_the_id(), 'rating', true); ?></strong>/10 <span>on <?php echo date("F j, Y", strtotime($post->post_date)); ?></span></div>
					<?php the_content(); ?>
					<?php if(count($images)> 0): foreach($images as $image): ?>
						<img src="<?php echo $image;?>" width="100">
					<?php endforeach; endif; ?>
				</div>
				<?php //end loop ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
<?php endwhile; ?>