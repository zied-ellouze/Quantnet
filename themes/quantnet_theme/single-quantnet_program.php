<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); ?>
<div id="container">
  <div id="content" role="main" class="singlepage">
  	<?php get_template_part( 'loop', 'program' ); ?>
	</div><!-- #content -->
</div><!-- #container -->
<?php get_sidebar('right'); ?>
<?php get_footer(); ?>