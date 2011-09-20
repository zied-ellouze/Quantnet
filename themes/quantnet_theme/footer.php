<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content
 * after.  Calls sidebar-footer.php for bottom widgets.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
?>
    <div class="clear"></div>
    </div><!-- #main -->
    <div id="footer" role="contentinfo">
       <div id="colophon">
            <div id="site-info">
                <?php $args = array(
                    'depth'        => 1,
                    'exclude'      => '2',
                    'title_li'     => __('')
                ); ?>
                <div class="footer-links"><a href="<?php bloginfo('url'); ?>" class="first">Home</a>|<a href="<?php bloginfo('url'); ?>/forum">Forum</a>|<a href="<?php bloginfo('url'); ?>/education">Education</a>|<a href="<?php bloginfo('url'); ?>/career">Career</a>|<a href="<?php bloginfo('url'); ?>/blog">Blog</a>|<a href="<?php bloginfo('url'); ?>/about-us">About Us</a>|<a href="<?php bloginfo('url'); ?>/contact-us">Contact Us</a>|<a href="<?php bloginfo('url'); ?>/terms-of-services">Terms of  Services</a>|<a href="<?php bloginfo('url'); ?>/privacy-policy">Privacy Policy</a>|<a href="<?php bloginfo('url'); ?>/sitemap">Sitemap</a></div>
                <p>&copy; <?php echo date('Y'); ?> <a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?> LLC. </a> All Rights Reserved.</p>
                <?//php wp_list_pages( $args ); ?>
            </div><!-- #site-info -->
            <div class="connectus">
                <span>Connect With Us</span>
                <a href="http://twitter.com/quantnet" class="twitter" target="_blank"></a>
                <a href="http://facebook.com/quantnet" class="facebook" target="_blank"></a>
                <a href="http://www.linkedin.com/in/nguyenandy" class="linkedin" target="_blank"></a>
                <!--<a href="#" class="youtube"></a>-->
            </div>
            <div id="site-generator">
                    <?php do_action( 'twentyten_credits' ); ?>
            </div><!-- #site-generator -->
         </div><!-- #colophon -->
    </div><!-- #footer -->
</div><!-- #wrapper -->
<!--code for facebook button-->
<div id="fb-root"></div>
<script>
      window.fbAsyncInit = function() {
        FB.init({appId: '144901038863524', status: true, cookie: true, xfbml: true});
      };
      (function() {
        var e = document.createElement('script');
        e.type = 'text/javascript';
        e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
        e.async = true;
        document.getElementById('fb-root').appendChild(e);
      }());
</script>
<?php
    /* Always have wp_footer() just before the closing </body>
    * tag of your theme, or you will break many plugins, which
    * generally use this hook to reference JavaScript files.
    */
    wp_footer();
 ?>
</body>
</html>
