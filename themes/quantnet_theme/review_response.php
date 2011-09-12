<?php
define('WP_USE_THEMES', false);
require('../../../wp-load.php');

if($_POST['orderby']=="post_title"){

$myreviews=$wpdb->get_results("SELECT * FROM wp_posts INNER JOIN wp_postmeta AS reviewrate ON(wp_posts.ID = reviewrate.post_id AND reviewrate.meta_key = 'review_rate')
INNER JOIN wp_rg_lead ON(wp_posts.ID=wp_rg_lead.post_id) INNER JOIN wp_rg_lead_detail ON(wp_rg_lead.id=wp_rg_lead_detail.lead_id AND wp_rg_lead_detail.value='".$_POST['data']."') ORDER BY wp_posts.post_title ".$_POST['order']."");
}else if($_POST['orderby']=="review_rate"){

$myreviews=$wpdb->get_results("SELECT * FROM wp_posts INNER JOIN wp_postmeta AS reviewrate ON(wp_posts.ID = reviewrate.post_id AND reviewrate.meta_key = 'review_rate')
INNER JOIN wp_rg_lead ON(wp_posts.ID=wp_rg_lead.post_id) INNER JOIN wp_rg_lead_detail ON(wp_rg_lead.id=wp_rg_lead_detail.lead_id AND wp_rg_lead_detail.value='".$_POST['data']."') ORDER BY reviewrate.meta_value ".$_POST['order']."");
				
}else{

$myreviews=$wpdb->get_results("SELECT * FROM wp_posts INNER JOIN wp_postmeta AS reviewrate ON(wp_posts.ID = reviewrate.post_id AND reviewrate.meta_key = 'review_rate')INNER JOIN wp_rg_lead ON(wp_posts.ID=wp_rg_lead.post_id) INNER JOIN wp_rg_lead_detail ON(wp_rg_lead.id=wp_rg_lead_detail.lead_id AND wp_rg_lead_detail.value='".$_POST['data']."') ORDER BY wp_posts.post_title ASC");

}

foreach($myreviews as $myreviews_data){
$meta_values_email = get_post_meta($myreviews_data->ID, "review_email");
$review_final_array['items'][]=array("Title"=>$myreviews_data->post_title, "Email"=>$meta_values_email[0], "Rating"=>$myreviews_data->meta_value);

}
//$review_final_array[]=array("review_data"=>$review_final_array);
echo json_encode($review_final_array);
//echo "<pre>"; print_r($my_final_array);
//exit();
?>