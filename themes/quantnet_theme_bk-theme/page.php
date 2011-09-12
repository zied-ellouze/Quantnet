<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
get_header(); ?>
<?php if(is_page('submit-a-review')) { ?>
<div id="one-column">
    <h2 class="entry-title">Submit A Review for Your Program</h2>
    <div class="review-post">
        <div class="review-thumb">
            <img src="<?php bloginfo('template_url'); ?>/images/dummy-img.jpg">
        </div>
        <div class="review-info">
            <h2 class="review-title">Review of Rutgers Master of Quantitative Finance Program</h2>
            <span class="reviewed">#4</span> of 35 programs reviewed<br/>
            <span class="ranked">Ranked #13</span> in Quantnet MFE Ranking
            <div class="avg-rate">Average rating: 8.0/10 <a href="#" class="666666">(59 reviews)</a></div>
        </div>
    </div>
    <div class="req-feilds">* Required</div>
<?php } else { ?>
<div id="container">
<?php } ?>
    <div id="content" role="main">
        <?php
        if(!is_page('Submit A Review for Your Program'))//for checking Submit page
        {
        /* Run the loop to output the page.
         * If you want to overload this in a child theme then include a file
         * called loop-page.php and that will be used instead.
         */
        get_template_part( 'loop', 'page' );
        ?>

    </div><!-- #content -->
</div><!-- #container and #one-column -->
<?php get_sidebar();
    }
    else {
        //if(is_user_logged_in())
        //{
        get_template_part( 'loop', 'page' );
        //}
        //else { echo "You must be login to Submit a Review"; }
     }
 ?>
<?php get_footer(); ?>
