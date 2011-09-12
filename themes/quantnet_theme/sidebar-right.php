<div id="primary" class="widget-area" role="complementary">
	<!--showing Big Banner Ads-->
  <ul id="banner-add">
      <?php
      if ( ! dynamic_sidebar( 'secondary-widget-area' ) ) : ?>
      <?php endif; // end secondary widget area ?>
  </ul>
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
</div>