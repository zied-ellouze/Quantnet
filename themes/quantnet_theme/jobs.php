<?php
/**
Template Name: Jobs
 * The template for displaying job posts.
 *
 * This is the template that displays feeds from different sites.
 
 */

get_header();

?>

<div id="container">
  <div id="content" role="main">
    <?php if(function_exists('fetch_feed')) {

	$feed = fetch_feed('http://www.quantfinancejobs.com/rss/latestjobs.asp'); // specify the source feed

	$limit = $feed->get_item_quantity(10); // specify number of items
	$items = $feed->get_items(0, $limit); // create an array of items

}
if ($limit == 0) echo '<div>The feed is either empty or unavailable.</div>';
else foreach ($items as $item) : ?>
    <div id="post" <?php post_class(); ?>>
    
      <h2 class="entry-title"><a href="<?php echo $item->get_permalink(); ?>"  target="_blank" title="<?php echo $item->get_date('j F Y @ g:i a'); ?>" rel="bookmark"><?php echo $item->get_title(); ?></a></h2>
      <div class="entry-summary"> <?php echo substr($item->get_description(), 0, 200); ?> <span>[...]</span></div>
      <!-- .entry-summary -->
      <div class="post-meta">
        <div class="posted-on"><em>Posted On <?php echo $item->get_date('M jS, Y'); ?></em></div>
      </div>
      <!--post-meta-->
    </div>
    <!--postdiv-->
    <?php endforeach; ?>
  </div>
  <!-- #content -->
</div>
<!-- #container -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>
