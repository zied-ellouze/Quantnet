<link rel="stylesheet" type="text/css" href="<?php bloginfo('template_url'); ?>/css/tabcontent.css" />
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/tabcontent.js"></script>
<!-- Tabs div nav-->
<div id="latesttab" class="indentmenu">
    <ul>
    <li><a href="#" rel="todays_tab" class="selected">Today</a></li>
    <li><a href="#" rel="month_tab">This Month</a></li>
    <li><a href="#" rel="overtime_tab">Overall</a></li>
    </ul>
</div>
<!-- Tabs div nav-->
<!-- Tabs main area-->
<div class="tabarea">
    <!-- Tabs 1-->
    <div id="todays_tab" class="tabcontent">
        <ul>
            <?php $today = getdate(); //echo "<pre>";print_r($today); echo "</pre>";?>
            <?php query_posts('year='.$today["year"].'&monthnum='.$today["mon"].'&day='.$today["mday"].'&order=DESC&showposts=10');
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

                <li><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php echo $title; ?> </a><span class="comment-bubble">(<?=$commentcount?>)</span></li>
                <?php endwhile; ?>
                <?php else : ?>
                <li>Sorry, no posts were found.</li>
            <?php endif;
            wp_reset_query();?>
        </ul>
    </div>
    <!-- Tabs 1-->
    <!-- Tabs 2-->
    <div id="month_tab" class="tabcontent">
        <ul>
            <?php $current_month = date('m'); ?>
            <?php $current_year = date('Y'); ?>
            <?php query_posts('year='.$current_year.'&monthnum='.$current_month.'&order=DESC&showposts=10');
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

                <li><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php echo $title; ?> </a><span class="comment-bubble">(<?=$commentcount?>)</span></li>
                <?php endwhile; ?>
                <?php else : ?>
                <li>Sorry, no posts were found.</li>
            <?php endif;
            wp_reset_query();?>
        </ul>
    </div>
   <!-- Tabs 2-->
   <!-- Tabs 3-->
    <div id="overtime_tab" class="tabcontent">
        <ul>
             <?php $current_year = date('Y'); ?>
             <?php query_posts('order=DESC&showposts=10');
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

                <li><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php echo $title; ?> </a><span class="comment-bubble">(<?=$commentcount?>)</span></a></li>
                <?php endwhile; ?>
                <?php else : ?>
                <li>Sorry, no posts were found.</li>
             <?php endif;
             wp_reset_query();?>
        </ul>
    </div>
    <!-- Tabs 3-->
</div>
<!-- Tabs main area-->
<script type="text/javascript">

var mypets=new ddtabcontent("latesttab")
mypets.setpersist(true)
mypets.setselectedClassTarget("link")
mypets.init()

</script>
