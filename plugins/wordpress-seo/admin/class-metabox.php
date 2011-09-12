<?php

class WPSEO_Metabox {
	
	var $wpseo_meta_length = 155;
	var $wpseo_meta_length_reason = '';
	
	function WPSEO_Metabox() {
		$options = get_wpseo_options();
		
		add_action('admin_print_scripts', array(&$this,'scripts'));
		add_action('admin_print_styles', array(&$this,'styles'));	
		
		if ( isset($options['enablexmlsitemap']) && $options['enablexmlsitemap'] ) {
			// WPSC integration
			add_action('wpsc_edit_product', array(&$this,'rebuild_sitemap'));
			add_action('wpsc_rate_product', array(&$this,'rebuild_sitemap'));

			// When permalink structure is changed, sitemap should be regenerated
			add_action('permalink_structure_changed', array(&$this,'rebuild_sitemap') );
			add_action('publish_post', array(&$this,'rebuild_sitemap') );
		}

		add_action('admin_menu', array(&$this,'create_meta_box') );
		add_action('save_post', array(&$this,'save_postdata') );
		
		add_filter('manage_page_posts_columns',array(&$this,'page_title_column_heading'),10,1);
		add_filter('manage_post_posts_columns',array(&$this,'page_title_column_heading'),10,1);
		add_action('manage_pages_custom_column',array(&$this,'page_title_column_content'), 10, 2);
		add_action('manage_posts_custom_column',array(&$this,'page_title_column_content'), 10, 2);

		add_action('get_inline_data',array(&$this,'yoast_wpseo_inline_edit'));
	}
	
	function scripts() {
		global $pagenow;
		
		if (in_array($pagenow, array('post.php', 'page.php', 'post-new.php'))) {
			wp_enqueue_script('jquery-bgiframe', WPSEO_URL.'js/jquery.bgiframe.min.js', array('jquery'));
			wp_enqueue_script('jquery-autocomplete', WPSEO_URL.'js/jquery.autocomplete.min.js', array('jquery'));
			wp_enqueue_script('wp-seo-metabox', WPSEO_URL.'js/wp-seo-metabox.js', array('jquery','jquery-bgiframe','jquery-autocomplete'));
		} elseif ($pagenow == 'edit.php') {
			wp_enqueue_script('jquery-bgiframe', WPSEO_URL.'js/inline-edit.js',array('jquery'));
		}
	}
	
	function styles() {
		global $pagenow;
		
		if (in_array($pagenow, array('post.php', 'page.php', 'post-new.php'))) {
			wp_enqueue_style('wp-seo-metabox', WPSEO_URL.'css/wp-seo-metabox.css');
		}
	}
	
	function get_meta_boxes( $post_type = 'post' ) {
		$options = get_wpseo_options();
		$mbs = array();
		$mbs['title'] = array(
			"name" => "title",
			"std" => "",
			"type" => "text",
			"title" => __("SEO Title"),
			"description" => __('<div class="alignright" style="padding:5px;"><a class="button" href="#snippetpreview" id="wpseo_regen_title">'.__('Generate SEO title').'</a></div><p>'."Title display in search engines is limited to 70 chars, <span id='yoast_wpseo_title-length'></span> chars left.<br/>If the SEO Title is empty, the preview shows what the plugin generates based on your <a target='_blank' href='".admin_url('admin.php?page=wpseo_titles#'.$post_type)."'>title template</a>.".'</p>'));
		$mbs['metadesc'] = array(
			"name" => "metadesc",
			"std" => "",
			"class" => "metadesc",
			"type" => "textarea",
			"title" => __("Meta Description"),
			"rows" => 2,
			"richedit" => false,
			"description" => "The <code>meta</code> description will be limited to ".$this->wpseo_meta_length." chars".$this->wpseo_meta_length_reason.", <span id='yoast_wpseo_metadesc-length'></span> chars left. <div id='yoast_wpseo_metadesc_notice'></div>"."<p>If the meta description is empty, the preview shows what the plugin generates based on your <a target='_blank' href='".admin_url('admin.php?page=wpseo_titles#'.$post_type)."'>meta description template</a>.</p>"
		);
		if ( isset($options['usemetakeywords']) && $options['usemetakeywords'] ) {
			$mbs['metakeywords'] = array(
				"name" => "metakeywords",
				"std" => "",
				"class" => "metakeywords",
				"type" => "text",
				"title" => __("Meta Keywords"),
				"description" => "If you type something above it will override your <a target='_blank' href='".admin_url('admin.php?page=wpseo_titles#'.$post_type)."'>meta keywords template</a>."
			);
		}
		$mbs['focuskw'] = array(
			"name" => "focuskw",
			"std" => "",
			"type" => "text",
			"title" => __("Focus Keyword"),
			"description" => "<div class='alignright' style='width: 300px;'>"
			."<a class='preview button' id='wpseo_relatedkeywords' href='#wpseo_tag_suggestions'>".__('Find related keywords')."</a> "
			."<p id='related_keywords_heading'>".__('Related keywords:')."</p><div id='wpseo_tag_suggestions'></div></div><div id='focuskwresults'><p>".__("What is the main keyword or key phrase this page should be found for?")."</p></div>",
		);
		
		// Apply filters before entering the advanced section
		$mbs = apply_filters('wpseo_metabox_entries', $mbs);

		if ( ! isset($options['disableadvanced_meta']) || !$options['disableadvanced_meta'] ) {
		
			$mbs['advancedopen'] = array(
				"type" => "div",
				"id" => "advancedseo",
			);
			$mbs['meta-robots-noindex'] = array(
				"name" => "meta-robots-noindex",
				"std" => "index",
				"title" => __("Meta Robots Index"),
				"type" => "radio",
				"options" => array(
					"0" => __("Index"),
					"1" => __("Noindex"),
				),
			);
			$mbs['meta-robots-nofollow'] = array(
				"name" => "meta-robots-nofollow",
				"std" => "follow",
				"title" => __("Meta Robots Follow"),
				"type" => "radio",
				"options" => array(
					"0" => __("Follow"),
					"1" => __("Nofollow"),
				),
			);
			$mbs['meta-robots-adv'] = array(
				"name" => "meta-robots-adv",
				"std" => "none",
				"type" => "multiselect",
				"title" => __("Meta Robots Advanced"),
				"description" => __("Advanced <code>meta</code> robots settings for this page."),
				"options" => array(
					"noodp" => "NO ODP",
					"noydir" => "NO YDIR",
					"noarchive" => __("No Archive"),
					"nosnippet" => __("No Snippet"),
				),
			);
			if (isset($options['breadcrumbs-enable']) && $options['breadcrumbs-enable']) {
				$mbs['bctitle'] = array(
					"name" => "bctitle",
					"std" => "",
					"type" => "text",
					"title" => __("Breadcrumbs title"),
					"description" => __("Title to use for this page in breadcrumb paths"),
				);
			}
			if (isset($options['enablexmlsitemap']) && $options['enablexmlsitemap']) {		
				$mbs['sitemap-include'] = array(
					"name" => "sitemap-include",
					"std" => "-",
					"type" => "select",
					"title" => __("Include in Sitemap"),
					"description" => __("Should this page be in the XML Sitemap at all times, regardless of Robots Meta settings?"),
					"options" => array(
						"-" => __("Auto detect"),
						"always" => __("Always include"),
						"never" => __("Never include"),
					),
				);
				$mbs['sitemap-prio'] = array(
					"name" => "sitemap-prio",
					"std" => "-",
					"type" => "select",
					"title" => __("Sitemap Priority"),
					"description" => __("The priority given to this page in the XML sitemap."),
					"options" => array(
						"-" => __("Automatic prioritization"),
						"1" => __("1 - Highest priority"),
						"0.9" => "0.9",
						"0.8" => "0.8 - ".__("Default for first tier pages"),
						"0.7" => "0.7",
						"0.6" => "0.6 - ".__("Default for second tier pages and posts"),
						"0.5" => "0.5 - ".__("Medium priority"),
						"0.4" => "0.4",
						"0.3" => "0.3",
						"0.2" => "0.2",
						"0.1" => "0.1 - ".__("Lowest priority"),
					),
				);
			}
			$mbs['canonical'] = array(
				"name" => "canonical",
				"std" => "",
				"type" => "text",
				"title" => "Canonical URL",
				"description" => "The canonical URL that this page should point to, leave empty to default to permalink. <a target='_blank' href='http://googlewebmastercentral.blogspot.com/2009/12/handling-legitimate-cross-domain.html'>Cross domain canonical</a> supported too."
			);
			$mbs['redirect'] = array(
				"name" => "redirect",
				"std" => "",
				"type" => "text",
				"title" => "301 Redirect",
				"description" => "The URL that this page should redirect to."
			);
		
			// Apply filters for in advanced section
			$mbs = apply_filters('wpseo_metabox_entries_advanced', $mbs);
		
			$mbs['advancedclose'] = array(
				"type" => "divclose",
				"id" => "advancedseo",
				"label" => "Advanced",
			);
		}
		return $mbs;
	}

	function meta_boxes() {
		global $post;

		$options = get_wpseo_options();
		
		$wpseo_meta_length = apply_filters('wpseo_metadesc_length', 155);
		
		$date = '';
		if ( $post->post_type == 'post' && ( !isset($options['disabledatesnippet']) || !$options['disabledatesnippet'] ) ) {
			if ( isset($post->post_date) )
				$date = date('M j, Y', strtotime($post->post_date));
			else 
				$date = date('M j, Y');

			$this->wpseo_meta_length = $this->wpseo_meta_length - (strlen($date)+5);
			$this->wpseo_meta_length_reason = ' (because of date display)';
		}
		
		echo '<script type="text/javascript">
			var wpseo_lang = "'.substr(get_locale(),0,2).'";
			var wpseo_meta_desc_length = '.$this->wpseo_meta_length.';
		</script>
		<div class="hidden" id="wpseo_hidden_metadesc"></div>';
		
		echo '<table class="yoasttable">';
		
		$title = wpseo_get_value('title');
		$desc = wpseo_get_value('metadesc');
			
		$slug = $post->post_name;
		if (empty($slug))
			$slug = sanitize_title($title);
		
?>
	<tr id="snippetpreview">
		<th><label>Snippet Preview:</label></th>
		<td>
<?php 
		$video = wpseo_get_value('video_meta',$post->ID);
		if ( $video && $video != 'none' ) {
			// TODO: improve snippet display of video duration to include seconds for shorter video's
			// echo '<pre>'.print_r(wpseo_get_value('video_meta'),1).'</pre>';
?>
			<div id="snippet" class="video">
				<h4 style="margin:0;font-weight:normal;"><a class="title" href="#"><?php echo $title; ?></a></h4>
				<div style="margin:5px 10px 10px 0;width:82px;height:62px;float:left;">
					<img style="border: 1px solid blue;padding: 1px;width:80px;height:60px;" src="<?php echo $video['thumbnail_loc']; ?>"/>
					<div style="margin-top:-23px;margin-right:4px;text-align:right"><img src="http://www.google.com/images/icons/sectionized_ui/play_c.gif" alt="" border="0" height="20" style="-moz-opacity:.88;filter:alpha(opacity=88);opacity:.88" width="20"></div>
				</div>
				<div style="float:left;width:440px;">
					<p style="color:#767676;font-size:13px;line-height:15px;"><?php echo number_format($video['duration']/60); ?> mins - <?php echo $date; ?></p>
					<p style="color:#000;font-size:13px;line-height:15px;" class="desc"><span><?php echo $desc; ?></span></p>
					<a href="#" class="url"><?php echo str_replace('http://','',get_bloginfo('url')).'/'.$slug.'/'; ?></a> - <a href="#" class="util">More videos &raquo;</a>
				</div>
			</div>
			
<?php
		} else {
			if (!empty($date))
				$date .= ' ... ';
?>
			<div id="snippet">
				<a class="title" href="#"><?php echo $title; ?></a>
				<p class="desc" style="font-size: 13px; color: #000; line-height: 15px;"><?php echo $date; ?><span><?php echo $desc ?></span></p>
				<a href="#" style="font-size: 13px; color: #282; line-height: 15px;" class="url"><?php echo str_replace('http://','',get_bloginfo('url')).'/'.$slug.'/'; ?></a> - <a href="#" class="util">Cached</a> - <a href="#" class="util">Similar</a>
			</div>
<?php } ?>
		</td>
	</tr>
<?php
	
		foreach($this->get_meta_boxes($post->post_type) as $meta_box) {
			$this->do_meta_box( $meta_box );
		}  
		echo '</table>';
	}

	function create_meta_box() {
		$options = get_wpseo_options();
		if ( function_exists('add_meta_box') ) {  
			foreach ( get_post_types() as $posttype ) {
				if ( in_array( $posttype, array('revision','nav_menu_item','post_format','attachment') ) )
					continue;
				if ( isset($options['hideeditbox-'.$posttype]) && $options['hideeditbox-'.$posttype] )
					continue;
				add_meta_box( 'yoast-wpseo-meta-box', 'WordPress SEO', array(&$this, 'meta_boxes'), $posttype, 'normal', 'high' );  
			}
		}  
	}

	function save_postdata( $post_id ) {  
		if ($post_id == null || empty($_POST))
			return;

		if ( wp_is_post_revision( $post_id ) )
			$post_id = wp_is_post_revision( $post_id );
			
		global $post;  
		if ( empty( $post ) )
			$post = get_post($post_id);

		foreach($this->get_meta_boxes($post->post_type) as $meta_box) {  
			if ( !isset($meta_box['name']) )
				continue;
			// // Verify  
			// if ( !wp_verify_nonce( $_POST['yoast_wpseo_nonce'], 'yoast-wpseo-form-submit' )) {  
			// 	return $post;
			// }  

			if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {  
				if ( !current_user_can( 'edit_page', $post_id ))  
					return $post_id;  
			} else {  
				if ( !current_user_can( 'edit_post', $post_id ))  
					return $post_id;  
			}  

			if ( isset($_POST['yoast_wpseo_'.$meta_box['name']]) )
				$data = $_POST['yoast_wpseo_'.$meta_box['name']];  
			else 
				continue;
			if ($meta_box['type'] == 'checkbox') {
				if (isset($_POST['yoast_wpseo_'.$meta_box['name']]))
					$data = true;
				else
					$data = false;
			} elseif ($meta_box['type'] == 'multiselect') {
				if (is_array($_POST['yoast_wpseo_'.$meta_box['name']]))
					$data = implode( ",", $_POST['yoast_wpseo_'.$meta_box['name']] );
				else
					$data = $_POST['yoast_wpseo_'.$meta_box['name']];
			}

			$option = '_yoast_wpseo_'.$meta_box['name'];
			$oldval = get_post_meta($post_id, $option);

			if($oldval == "")  
				add_post_meta($post_id, $option, $data, true);  
			elseif($data != $oldval)  
				update_post_meta($post_id, $option, $data);  
			elseif($data == "")  
				delete_post_meta($post_id, $option, $oldval);  
		}  
		do_action('wpseo_saved_postdata');
	}

	function rebuild_sitemap( $post ) {
		global $wpseo_generate, $wpseo_echo;
		$wpseo_generate = true;
		$wpseo_echo = false;
		require_once WPSEO_PATH.'/sitemaps/xml-sitemap-class.php';
	}

	function page_title_column_heading( $columns ) {
		return array_merge(array_slice($columns, 0, 6), array('page-meta-robots' => 'Robots Meta'), array_slice($columns, 6, count($columns)));
	}

	function page_title_column_content( $column_name, $id ) {
		// if ( $column_name == 'page-title' ) {
		// 	echo esc_html( $this->page_title($id) );
		// }
		if ( $column_name == 'page-meta-robots' ) {
			$robots 			= array();
			$robots['index'] 	= 'Index';
			$robots['follow'] 	= 'Follow';

			if ( wpseo_get_value('meta-robots-noindex') )
				$robots['index'] = 'Noindex';
			if ( wpseo_get_value('meta-robots-nofollow') )
				$robots['follow'] = 'Nofollow';
			
			echo $robots['index'].', '.$robots['follow'];
		}
	}

	function do_meta_box( $meta_box ) {
		global $post;
		if (!isset($meta_box['name'])) {
			$meta_box['name'] = '';
		} else {
			$meta_box_value = wpseo_get_value($meta_box['name']);
		}
	
		$class = '';
		if (!empty($meta_box['class']))
			$class = ' '.$meta_box['class'];

		if( ( !isset($meta_box_value) || empty($meta_box_value) ) && isset($meta_box['std']) )  
			$meta_box_value = $meta_box['std'];  

		if ($meta_box['type'] != 'div' && $meta_box['type'] != 'divclose') {
			echo '<tr>';
			echo '<th><label for="yoast_wpseo_'.$meta_box['name'].'">'.$meta_box['title'].':</label></th>';  
			echo '<td>';		
		}
		switch($meta_box['type']) { 
			case "text":
				echo '<input type="text" id="yoast_wpseo_'.$meta_box['name'].'" name="yoast_wpseo_'.$meta_box['name'].'" value="'.$meta_box_value.'" class="yoast'.$class.'"/><br />';  
				break;
			case "textarea":
				$rows = 5;
				if (isset($meta_box['rows']))
					$rows = $meta_box['rows'];
				if (!isset($meta_box['richedit']) || $meta_box['richedit'] == true) {
					echo '<div class="editor_container">';
					wp_tiny_mce( true, array( "editor_selector" => $meta_box['name'].'_class' ) );
					echo '<textarea class="yoast'.$class.' '.$meta_box['name'].'_class" rows="'.$rows.'" id="yoast_wpseo_'.$meta_box['name'].'" name="yoast_wpseo_'.$meta_box['name'].'">'.$meta_box_value.'</textarea>';
					echo '</div>';
				} else {
					echo '<textarea class="yoast'.$class.'" rows="5" id="yoast_wpseo_'.$meta_box['name'].'" name="yoast_wpseo_'.$meta_box['name'].'">'.$meta_box_value.'</textarea>';
				}
				break;
			case "select":
				echo '<select name="yoast_wpseo_'.$meta_box['name'].'" id="yoast_wpseo_'.$meta_box['name'].'" class="yoast'.$class.'">';
				foreach ($meta_box['options'] as $val => $option) {
					$selected = '';
					if ($meta_box_value == $val)
						$selected = 'selected="selected"';
					echo '<option '.$selected.' value="'.$val.'">'.$option.'</option>';
				}
				echo '</select>';
				break;
			case "multiselect":
				$selectedarr = explode(',',$meta_box_value);
				$meta_box['options'] = array('none' => 'None') + $meta_box['options'];
				echo '<select multiple="multiple" size="'.count($meta_box['options']).'" style="height: '.(count($meta_box['options'])*16).'px;" name="yoast_wpseo_'.$meta_box['name'].'[]" id="yoast_wpseo_'.$meta_box['name'].'" class="yoast'.$class.'">';
				foreach ($meta_box['options'] as $val => $option) {
					$selected = '';
					if (in_array($val, $selectedarr))
						$selected = 'selected="selected"';
					echo '<option '.$selected.' value="'.$val.'">'.$option.'</option>';
				}
				echo '</select>';
				break;
			case "checkbox":
				$checked = '';
				if ($meta_box_value != false)
					$checked = 'checked="checked"';
				echo '<input type="checkbox" id="yoast_wpseo_'.$meta_box['name'].'" name="yoast_wpseo_'.$meta_box['name'].'" '.$checked.' class="yoast'.$class.'"/><br />';
				break;
			case "radio":
				if ($meta_box_value == '')
					$meta_box_value = $meta_box['std'];
				foreach ($meta_box['options'] as $val => $option) {
					$selected = '';
					if ($meta_box_value == $val)
						$selected = 'checked="checked"';
					echo '<input type="radio" '.$selected.' id="yoast_wpseo_'.$meta_box['name'].'_'.$val.'" name="yoast_wpseo_'.$meta_box['name'].'" value="'.$val.'"/> <label for="yoast_wpseo_'.$meta_box['name'].'_'.$val.'">'.$option.'</label> ';
				}
				break;
			case "div":
				echo '</table>';
				echo '<br class="clear"/>';
				echo '<div id="'.$meta_box['id'].'">';
				echo '<table class="yoasttable">';
				break;
			case "divclose":
				$tableopen = false;
				echo '</table>';
				echo '</div>';
				echo '<div class="divtoggle"><small><a class="button" href="" id="'.$meta_box['id'].'_open">'.$meta_box['label'].' &darr;</a></small></div>';
				break;
			case "divtext":
				echo '<p>' . $meta_box['description'] . '</p>';
		}
		
		if ($meta_box['type'] != 'div' && $meta_box['type'] != 'divclose' && $meta_box['type'] != 'divtext') {
			if ( isset($meta_box['description']) )
				echo '<p>'.$meta_box['description'].'</p>';
			echo '</td>';  
			echo '</tr>';	
		}
	}
	
	function page_title( $postid ) {
		$fixed_title = wpseo_get_value('title', $postid );
		if ($fixed_title) {
			return $fixed_title;
		} else {
			$post = get_post( $postid );
			$options = get_wpseo_options();
			if ( isset($options['title-'.$post->post_type]) && !empty($options['title-'.$post->post_type]) )
				return wpseo_replace_vars($options['title-'.$post->post_type], (array) $post );				
			else
				return wpseo_replace_vars('%%title%%', (array) $post );				
		}
	}
}
$wpseo_metabox = new WPSEO_Metabox();
