<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress 2.7 Plugin: WP-Print 2.50										|
|	Copyright (c) 2008 Lester "GaMerZ" Chan									|
|																							|
|	File Written By:																	|
|	- Lester "GaMerZ" Chan															|
|	- http://lesterchan.net															|
|																							|
|	File Information:																	|
|	- Printer Friendly Post/Page Template										|
|	- wp-content/plugins/wp-print/print-posts.php							|
|																							|
+----------------------------------------------------------------+
*/
?>

<?php global $text_direction; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
	<title><?php bloginfo('name'); ?> <?php wp_title(); ?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta name="Robots" content="noindex, nofollow" />
	<?php if(@file_exists(TEMPLATEPATH.'/print-css.css')): ?>
		<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/print-css.css" type="text/css" media="screen, print" />
	<?php else: ?>
		<link rel="stylesheet" href="<?php echo plugins_url('wp-print/print-css.css'); ?>" type="text/css" media="screen, print" />
	<?php endif; ?>
	<?php if('rtl' == $text_direction): ?>
		<?php if(@file_exists(TEMPLATEPATH.'/print-css-rtl.css')): ?>
			<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/print-css-rtl.css" type="text/css" media="screen, print" />
		<?php else: ?>
			<link rel="stylesheet" href="<?php echo plugins_url('wp-print/print-css-rtl.css'); ?>" type="text/css" media="screen, print" />
		<?php endif; ?>
	<?php endif; ?>
</head>
<body>
<p style="text-align: center;"><strong>- <?php bloginfo('name'); ?> - <span dir="ltr"><?php bloginfo('url')?></span> -</strong></p>
<div class="Center">
	<div id="Outline">
		<?php if (have_posts()): ?>
			<?php while (have_posts()): the_post(); ?>
			<?php $id = get_the_id();?>
					<p id="BlogTitle"><?php the_title(); ?></p>
					<p id="BlogDate"><?php _e('Posted By', 'wp-print'); ?> <u><?php the_author(); ?></u> <?php _e('On', 'wp-print'); ?> <?php the_time(sprintf(__('%s @ %s', 'wp-print'), get_option('date_format'), get_option('time_format'))); ?> <?php _e('In', 'wp-print'); ?> <?php print_categories('<u>', '</u>'); ?> | <u><a href='#comments_controls'><?php print_comments_number(); ?></a></u></p>
					<div id="BlogContent"><?php print_content(); ?></div>
				<?php $varsss = explode('/', $_SERVER['REQUEST_URI']);
				//print_r($varsss);
				if($varsss[1] == 'job_post')
				{	
				$employer = get_post_meta($id, 'employer' , true);
			$job_title = get_post_meta($id, 'job_title' , true);
			$job_loc = get_post_meta($id, 'job_location' , true);
			$job_type = get_post_meta($id, 'job_type' , true);
			$job_desc = get_post_meta($id, 'job_description' , true);
			$job_exp = get_post_meta($id, 'job_posting_expiration' , true);
			$compensation = get_post_meta($id, 'compensation' , true);
			$cont_info = get_post_meta($id, 'contact_info' , true);
			$apply_link = get_post_meta($id, 'apply_link' , true);
			$qualification = get_post_meta($id, 'qualification' , true);
			$about_emp = get_post_meta($id, 'about_the_employer' , true);
			if(!empty($employer)) { echo "<div><b>Employer</b> - ".$employer."</div>"; } //for getting employer values
			if(!empty($job_title)) { echo "<div><b>Job Title</b> - ".$job_title."</div>"; } //for getting job title values
			if(!empty($job_loc)) { echo "<div><b>Job Location</b> - ".$job_loc."</div>"; } //for getting job location values
			if(!empty($job_type)) { echo "<div><b>Job Type</b> - ".$job_type."</div>"; } //for getting job type values
			if(!empty($job_desc)) { echo "<div><b>Job Description</b> - ".$job_desc."</div>"; } //for getting job description values
			if(!empty($job_exp)) { echo "<div><b>Job Posting Expiration</b> - ".$job_exp."</div>"; } //for getting job experience values
			if(!empty($compensation)) { echo "<div><b>Compensation</b> - ".$compensation."</div>"; } //for getting compensation values
			if(!empty($cont_info)) { echo "<div><b>Contact Info</b> - <a href='mailto:someone@example.com?Subject=Mail%20from%20Quantnet'>".$cont_info."</div>"; } //for getting contact info values
			if(!empty($apply_link)) { echo "<div><b><a href='".$apply_link."'>Click Here to Apply</a></b></div>"; } //for getting apply link values
			if(!empty($qualification)) { echo "<div><b>Qualification</b> - ".$qualification."</div>"; } //for getting qualification values
			if(!empty($about_emp)) { echo "<div><b>About the Employer</b> - ".$about_emp."</div>"; } //for getting about employer values

				
				}
				?>
			<?php endwhile; ?>
			<hr class="Divider" style="text-align: center;" />
			<?php if(print_can('comments')): ?>
				<?php comments_template(); ?>
			<?php endif; ?>
			<p><?php _e('Article printed from', 'wp-print'); ?> <?php bloginfo('name'); ?>: <strong dir="ltr"><?php bloginfo('url'); ?></strong></p>
			<p><?php _e('URL to article', 'wp-print'); ?>: <strong dir="ltr"><?php the_permalink(); ?></strong></p>
			<?php if(print_can('links')): ?>
				<p><?php print_links(); ?></p>
			<?php endif; ?>
			<p style="text-align: <?php echo ('rtl' == $text_direction) ? 'left' : 'right'; ?>;" id="print-link"><?php _e('Click', 'wp-print'); ?> <a href="#Print" onclick="window.print(); return false;" title="<?php _e('Click here to print.', 'wp-print'); ?>"><?php _e('here', 'wp-print'); ?></a> <?php _e('to print.', 'wp-print'); ?></p>
		<?php else: ?>
				<p><?php _e('No posts matched your criteria.', 'wp-print'); ?></p>
		<?php endif; ?>
	</div>
</div>
<p style="text-align: center;"><?php echo stripslashes($print_options['disclaimer']); ?></p>
</body>
</html>
