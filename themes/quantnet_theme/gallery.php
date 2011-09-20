<?php
/**
Template Name: Gallery

The template is for displaying the images for a gallery.
*/
get_header();
?>
	<script type="text/javascript">
	var gallery = {
		current: 0,
		images: new Array(),
		left: function(){
			if(this.current != 0){
				this.image(this.current-1);
			}else{
				this.image(this.images.length-1)
			}
		},
		right: function(){
			if(this.current == (this.images.length-1)){
				this.image(0);
			}else{
				this.image(this.current+1)
			}
		},
		image: function(image_id){
			jQuery(".main-image-actual").html("<img src='"+this.images[image_id]+"'>");
			this.current = image_id;
		}
	}
	</script>
	<div class="gallerypage">
		<?php if(isset($_GET['id'])): $images = quant_get_all_program_images($_GET['id']); $md5_images = array(); ?>
			<a href="<?php echo get_post_permalink($_GET['id']); ?>" class="backreview">Back to the review page</a>
			<?php if(count($images) > 0): $i=0; $j=0; ?>
				<div class="image-container">
					<?php foreach($images as $image): ?>
						<?php $md5_images[] = md5($image); ?>
						<script type="text/javascript">
						gallery.images.push('<?php echo $image; ?>');
						</script>
						<?php if($i==0): ?>
							<div class="main-image-container">
								<a class="left-arrow" href="" onClick="gallery.left(); return false;">Left</a>
								<div class="main-image-actual">
									<img src="<?php echo $image; ?>">
								</div>
								<a class="right-arrow" href="" onClick="gallery.right(); return false;">Right</a>
							</div>
						<?php endif; ?>						
					<?php $i++; endforeach; ?>
                    
                     <div class="small-image-actual">
                    <?php foreach($images as $imagethumb): ?>
						<script type="text/javascript">
						gallery.images.push('<?php echo $imagethumb; ?>');
						</script>                       
							<a href="" onClick="gallery.image(<?php echo $j;?>); return false;"><img src="<?php echo $imagethumb; ?>" width="115" border="0"></a>											
					<?php $j++; endforeach; ?>
                    </div>
				</div>
				<?php if(isset($_GET['picture_id'])): ?>
					<?php $key = array_search($_GET['picture_id'], $md5_images); ?>
					<?php if($key): ?>
						<script type="text/javascript">
						gallery.image('<?php echo $key; ?>');
						</script>
					<?php endif; ?>
				<?php endif; ?>
			<?php else: ?>
				<h2>No images were found for this review.</h2>
			<?php endif; ?>
		<?php else: //If query string param id is not present ?>
			<h2>We're sorry, we could not locate this gallery.</h2>
		<?php endif; //End query string id check ?>
	</div>
<?php
get_footer();
?>