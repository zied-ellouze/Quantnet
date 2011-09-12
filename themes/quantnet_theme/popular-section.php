<?php 
/*
Template name: Popular 
*/
?> 
<link rel="stylesheet" type="text/css" href="<?php bloginfo('template_url'); ?>/css/tabcontent.css" />
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/tabcontent.js"></script>
<!--Tab Menu Nav-->
<div id="pettabs" class="indentmenu">
    <ul>
    <li><a href="#" rel="today_tab" class="selected">Viewed</a></li>
    <li><a href="#" rel="alltime_tab">Commented</a></li>
    </ul>
</div>
<!--Tab Menu Nav-->
<!--Tab Main Area-->
<div class="tabarea">
    <!--Tab 1-->
    <div id="today_tab" class="tabcontent">
        <ul>
            <?php if(function_exists('get_most_viewed')) { get_most_viewed('post',10); } ?>
        </ul>
    </div>
    <!--Tab 1-->
    <!--Tab 2-->
    <div id="alltime_tab" class="tabcontent">
          <ul>
            <?php query_posts('orderby=comment_count&posts_per_page=>10');
            if ( have_posts() ) : while ( have_posts() ) : the_post();
                global $wpdb;
                $postID = get_the_ID();
                $title = get_the_title();
                $comments = $wpdb->get_row("SELECT comment_count as count FROM wp_posts WHERE ID = '$postID'");
                $commentcount = $comments->count;
                if($commentcount == 1): $commenttext = ''; endif;
                if($commentcount > 1 || $commentcount == 0): $commenttext = ''; endif;
                $fulltitle = $title.' ('.$commentcount.' '.$commenttext.')';
                 ?>

                <li><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php echo $title; ?> </a><span class="comment-bubble">(<?=$commentcount?>)</span> </a></li>
                <?php endwhile; ?>
                <?php else : ?>
                <li>Sorry, no posts were found.</li>
            <?php endif;
            wp_reset_query();?>
         </ul>
    </div>
    <!--Tab 2-->
</div>
<!--Tab Main Area-->
<script type="text/javascript">
var mypets=new ddtabcontent("pettabs")
mypets.setpersist(true)
mypets.setselectedClassTarget("link")
mypets.init()

</script>
