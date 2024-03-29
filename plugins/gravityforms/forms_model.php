<?php

require_once(ABSPATH . "/wp-includes/post.php");

define("GFORMS_MAX_FIELD_LENGTH", 200);

class RGFormsModel{

    public static $uploaded_files = array();

    public static function get_form_table_name(){
        global $wpdb;
        return $wpdb->prefix . "rg_form";

    }

    public static function get_meta_table_name(){
        global $wpdb;
        return $wpdb->prefix . "rg_form_meta";
    }

    public static function get_form_view_table_name(){
        global $wpdb;
        return $wpdb->prefix . "rg_form_view";
    }

    public static function get_lead_table_name(){
        global $wpdb;
        return $wpdb->prefix . "rg_lead";
    }

    public static function get_lead_notes_table_name(){
        global $wpdb;
        return $wpdb->prefix . "rg_lead_notes";
    }

    public static function get_lead_details_table_name(){
        global $wpdb;
        return $wpdb->prefix . "rg_lead_detail";
    }

    public static function get_lead_details_long_table_name(){
        global $wpdb;
        return $wpdb->prefix . "rg_lead_detail_long";
    }

     public static function get_lead_view_name(){
        global $wpdb;
        return $wpdb->prefix . "rg_lead_view";
    }

    public static function get_forms($is_active = null, $sort="title ASC"){
        global $wpdb;
        $form_table_name =  self::get_form_table_name();
        $lead_table_name = self::get_lead_table_name();
        $view_table_name = self::get_form_view_table_name();

        $active_clause = $is_active !== null ? $wpdb->prepare("WHERE is_active=%d", $is_active) : "";
        $order_by = !empty($sort) ? "ORDER BY $sort" : "";

        $sql = "SELECT f.id, f.title, f.date_created, f.is_active, 0 as lead_count, 0 view_count
                FROM $form_table_name f
                $active_clause
                $order_by";

        //Getting all forms
        $forms = $wpdb->get_results($sql);

        //Getting entry count per form
        $sql = "SELECT form_id, count(id) as lead_count FROM $lead_table_name l GROUP BY form_id";
        $entry_count = $wpdb->get_results($sql);

        //Getting view count per form
        $sql = "SELECT form_id, sum(count) as view_count FROM $view_table_name GROUP BY form_id";
        $view_count = $wpdb->get_results($sql);

        //Adding entry counts and to form array
        foreach($forms as &$form){
            foreach($view_count as $count){
                if($count->form_id == $form->id){
                    $form->view_count = $count->view_count;
                    break;
                }
            }

            foreach($entry_count as $count){
                if($count->form_id == $form->id){
                    $form->lead_count = $count->lead_count;
                    break;
                }
            }
        }

        return $forms;
    }

    public static function get_forms_by_id($ids){
        global $wpdb;
        $form_table_name =  self::get_form_table_name();
        $meta_table_name =  self::get_meta_table_name();

        if(is_array($ids))
            $ids = implode(",", $ids);

        $results = $wpdb->get_results(" SELECT display_meta FROM {$form_table_name} f
                                        INNER JOIN {$meta_table_name} m ON f.id = m.form_id
                                        WHERE id in({$ids})", ARRAY_A);

        foreach ($results as &$result)
            $result = maybe_unserialize($result["display_meta"]);

        return $results;

    }

    public static function get_form_payment_totals($form_id){
        global $wpdb;
        $lead_table_name = self::get_lead_table_name();

        $sql = $wpdb->prepare(" SELECT sum(payment_amount) revenue, count(l.id) orders
                                 FROM $lead_table_name l
                                 WHERE form_id=%d AND payment_amount IS NOT null", $form_id);

        $totals = $wpdb->get_row($sql, ARRAY_A);

        $active = $wpdb->get_var($wpdb->prepare(" SELECT count(id) as active
                                                 FROM $lead_table_name
                                                 WHERE form_id=%d AND payment_status='Active'", $form_id));

        if(empty($active))
            $active = 0;

        $totals["active"] = $active;

        return $totals;
    }

    public static function get_form_counts($form_id){
        global $wpdb;
        $lead_table_name = self::get_lead_table_name();

         $sql = $wpdb->prepare(" SELECT count(l.id)
                                 FROM $lead_table_name l
                                 WHERE is_read=0
                                 AND form_id=%d", $form_id);

         $unread_count = $wpdb->get_var($sql);

         $sql = $wpdb->prepare(" SELECT count(l.id)
                                 FROM $lead_table_name l
                                 WHERE is_starred=1
                                 AND form_id=%d", $form_id);

         $starred_count = $wpdb->get_var($sql);

         $sql = $wpdb->prepare(" SELECT count(l.id)
                                 FROM $lead_table_name l
                                 WHERE form_id=%d", $form_id);

         $total_count = $wpdb->get_var($sql);

         return array("total" => $total_count, "unread" => $unread_count, "starred" => $starred_count);

    }

    public static function get_form_summary(){
        global $wpdb;
        $form_table_name =  self::get_form_table_name();
        $lead_table_name = self::get_lead_table_name();

        $sql = "SELECT l.form_id, count(l.id) as unread_count
                FROM $lead_table_name l
                WHERE is_read=0
                GROUP BY form_id";

        //getting number of unread leads for all forms
        $unread_results = $wpdb->get_results($sql, ARRAY_A);

        $sql = "SELECT l.form_id, max(l.date_created) as last_lead_date
                FROM $lead_table_name l
                GROUP BY form_id";

        $lead_date_results = $wpdb->get_results($sql, ARRAY_A);

        $sql = "SELECT id, title, '' as last_lead_date, 0 as unread_count
                FROM $form_table_name
                WHERE is_active=1
                ORDER BY title";

        $forms = $wpdb->get_results($sql, ARRAY_A);


        for($i=0; $count = sizeof($forms), $i<$count; $i++){
            if(is_array($unread_results)){
                foreach($unread_results as $unread_result){
                    if($unread_result["form_id"] == $forms[$i]["id"]){
                        $forms[$i]["unread_count"] = $unread_result["unread_count"];
                        break;
                    }
                }
            }

            if(is_array($lead_date_results)){
                foreach($lead_date_results as $lead_date_result){
                    if($lead_date_result["form_id"] == $forms[$i]["id"]){
                        $forms[$i]["last_lead_date"] = $lead_date_result["last_lead_date"];
                        break;
                    }
                }
            }

        }

        return $forms;
    }

    public static function get_form_count(){
        global $wpdb;
        $form_table_name =  self::get_form_table_name();
        $results = $wpdb->get_results("SELECT count(0) as count FROM $form_table_name UNION ALL SELECT count(0) as count FROM $form_table_name WHERE is_active=1 ");
        return array(   "total" => intval($results[0]->count),
                        "active" => intval($results[1]->count),
                        "inactive" => intval($results[0]->count) - intval($results[1]->count)
                        );
    }

    public static function get_form($form_id){
        global $wpdb;
        $table_name =  self::get_form_table_name();
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE id=%d", $form_id));
        return $results[0];
    }

    public static function get_form_meta($form_id){
        global $wpdb;

        $table_name =  self::get_meta_table_name();
        $form = maybe_unserialize($wpdb->get_var($wpdb->prepare("SELECT display_meta FROM $table_name WHERE form_id=%d", $form_id)));

        $page_number = 1;
        if(is_array($form["fields"])){
            foreach($form["fields"] as &$field){
                $field["formId"] = $form["id"];
                $field["pageNumber"] = $page_number;
                if($field["type"] == "page"){
                    $page_number++;
                    $field["pageNumber"] = $page_number;
                }
            }
        }
        return $form;
    }

    public static function add_default_properties($form){
        if(is_array($form["fields"])){
            $all_fields = array("adminLabel"=>"","adminOnly"=>"","allowsPrepopulate"=>"","defaultValue"=>"","description"=>"","content"=>"","cssClass"=>"",
                                "errorMessage"=>"","id"=>"","inputName"=>"","isRequired"=>"","label"=>"","noDuplicates"=>"",
                                "size"=>"","type"=>"","postCustomFieldName"=>"","displayAllCategories"=>"","displayCaption"=>"","displayDescription"=>"",
                                "displayTitle"=>"","inputType"=>"","rangeMin"=>"","rangeMax"=>"","calendarIconType"=>"",
                                "calendarIconUrl"=>"", "dateType"=>"","dateFormat"=>"","phoneFormat"=>"","addressType"=>"","defaultCountry"=>"","defaultProvince"=>"",
                                "defaultState"=>"","hideAddress2"=>"","hideCountry"=>"","hideState"=>"","inputs"=>"","nameFormat"=>"","allowedExtensions"=>"",
                                "captchaType"=>"","page_number"=>"","captchaTheme"=>"","simpleCaptchaSize"=>"","simpleCaptchaFontColor"=>"","simpleCaptchaBackgroundColor"=>"",
                                "failed_validation"=>"", "productField" => "", "enablePasswordInput" => "", "maxLength" => "", "enablePrice" => "", "basePrice" => "");

            foreach($form["fields"] as &$field)
                $field = wp_parse_args($field, $all_fields);
        }
        return $form;
    }

    public static function get_grid_column_meta($form_id){
        global $wpdb;

        $table_name =  self::get_meta_table_name();
        return maybe_unserialize($wpdb->get_var($wpdb->prepare("SELECT entries_grid_meta FROM $table_name WHERE form_id=%d", $form_id)));
    }

    public static function update_grid_column_meta($form_id, $columns){
        global $wpdb;

        $table_name = self::get_meta_table_name();
        $meta = maybe_serialize(stripslashes_deep($columns) );
        $wpdb->query( $wpdb->prepare("UPDATE $table_name SET entries_grid_meta=%s WHERE form_id=%d", $meta, $form_id) );
    }

    public static function get_lead_detail_id($current_fields, $field_number){
        foreach($current_fields as $field)
            if($field->field_number == $field_number)
                return $field->id;

        return 0;
    }

    public static function update_form_active($form_id, $is_active){
        global $wpdb;
        $form_table = self::get_form_table_name();
        $sql = $wpdb->prepare("UPDATE $form_table SET is_active=%d WHERE id=%d", $is_active, $form_id);
        $wpdb->query($sql);
    }

    public static function update_forms_active($forms, $is_active){
        foreach($forms as $form_id)
            self::update_form_active($form_id, $is_active);
    }

    public static function update_leads_property($leads, $property_name, $property_value){
        foreach($leads as $lead)
            self::update_lead_property($lead, $property_name, $property_value);
    }

    public static function update_lead_property($lead_id, $property_name, $property_value){
        global $wpdb;
        $lead_table = self::get_lead_table_name();
        $wpdb->update($lead_table, array($property_name => $property_value ), array("id" => $lead_id));
    }

    public static function update_lead($lead){
        global $wpdb;
        $lead_table = self::get_lead_table_name();

        $payment_date = strtotime($lead["payment_date"]) ? "'{$lead["payment_date"]}'" : "NULL";
        $payment_amount = !empty($lead["payment_amount"]) ? $lead["payment_amount"] : "NULL";
        $transaction_type = !empty($lead["transaction_type"]) ? $lead["transaction_type"] : "NULL";

        $sql = $wpdb->prepare("UPDATE $lead_table SET
                                    form_id=%d,
                                    post_id=%d,
                                    is_starred=%d,
                                    is_read=%d,
                                    ip=%s,
                                    source_url=%s,
                                    user_agent=%s,
                                    currency=%s,
                                    payment_status=%s,
                                    payment_date={$payment_date},
                                    payment_amount={$payment_amount},
                                    transaction_id=%s,
                                    is_fulfilled=%d,
                                    transaction_type={$transaction_type}
                                WHERE id=%d",   $lead["form_id"], $lead["post_id"], $lead["is_starred"], $lead["is_read"], $lead["ip"], $lead["source_url"], $lead["user_agent"],
                                                $lead["currency"], $lead["payment_status"], $lead["transaction_id"], $lead["is_fulfilled"], $lead["id"]);
        $wpdb->query($sql);
    }

    public static function delete_leads($leads){
        foreach($leads as $lead_id)
            self::delete_lead($lead_id);
    }

    public static function delete_forms($forms){
        foreach($forms as $form_id)
            self::delete_form($form_id);
    }

    public static function delete_leads_by_form($form_id){
        global $wpdb;

        if(!GFCommon::current_user_can_any("gravityforms_delete_entries"))
            die(__("You don't have adequate permission to delete entries.", "gravityforms"));

        $lead_table = self::get_lead_table_name();
        $lead_notes_table = self::get_lead_notes_table_name();
        $lead_detail_table = self::get_lead_details_table_name();
        $lead_detail_long_table = self::get_lead_details_long_table_name();

        //Delete from detail long
        $sql = $wpdb->prepare(" DELETE FROM $lead_detail_long_table
                                WHERE lead_detail_id IN(
                                    SELECT ld.id FROM $lead_detail_table ld
                                    INNER JOIN $lead_table l ON l.id = ld.lead_id
                                    WHERE l.form_id=%d AND ld.form_id=%d
                                )", $form_id, $form_id);
        $wpdb->query($sql);

        //Delete from lead details
        $sql = $wpdb->prepare(" DELETE FROM $lead_detail_table
                                WHERE lead_id IN (
                                    SELECT id FROM $lead_table WHERE form_id=%d
                                )", $form_id);
        $wpdb->query($sql);

        //Delete from lead notes
        $sql = $wpdb->prepare(" DELETE FROM $lead_notes_table
                                WHERE lead_id IN (
                                    SELECT id FROM $lead_table WHERE form_id=%d
                                )", $form_id);
        $wpdb->query($sql);

        //Delete from lead
        $sql = $wpdb->prepare("DELETE FROM $lead_table WHERE form_id=%d", $form_id);
        $wpdb->query($sql);
    }

    public static function delete_views($form_id){
        global $wpdb;

        $form_view_table = self::get_form_view_table_name();

        //Delete form view
        $sql = $wpdb->prepare("DELETE FROM $form_view_table WHERE form_id=%d", $form_id);
        $wpdb->query($sql);
    }

    public static function delete_form($form_id){
        global $wpdb;

        if(!GFCommon::current_user_can_any("gravityforms_delete_forms"))
            die(__("You don't have adequate permission to delete forms.", "gravityforms"));

        do_action("gform_before_delete_form", $form_id);

        $form_meta_table = self::get_meta_table_name();
        $form_table = self::get_form_table_name();

        //Deleting form Entries
        self::delete_leads_by_form($form_id);

        //Delete form meta
        $sql = $wpdb->prepare("DELETE FROM $form_meta_table WHERE form_id=%d", $form_id);
        $wpdb->query($sql);

        //Deleting form Views
        self::delete_views($form_id);

        //Delete form
        $sql = $wpdb->prepare("DELETE FROM $form_table WHERE id=%d", $form_id);
        $wpdb->query($sql);

        do_action("gform_after_delete_form", $form_id);
    }

    public static function duplicate_form($form_id){
        global $wpdb;

        if(!GFCommon::current_user_can_any("gravityforms_create_form"))
            die(__("You don't have adequate permission to create forms.", "gravityforms"));

        //finding unique title
        $form = self::get_form($form_id);
        $count = 2;
        $title = $form->title . " - Copy 1";
        while(!self::is_unique_title($title)){
            $title = $form->title . " - Copy $count";
            $count++;
        }

        //creating new form
        $new_id = self::insert_form($title);

        //copying form meta
        $meta = self::get_form_meta($form_id);
        $meta["title"] = $title;
        $meta["id"] = $new_id;
        self::update_form_meta($new_id, $meta);

        return $new_id;
    }

    public static function is_unique_title($title){
        $forms = self::get_forms();
        foreach($forms as $form){
            if(strtolower($form->title) == strtolower($title))
                return false;
        }

        return true;
    }

    public static function insert_form($form_title){
        global $wpdb;
        $form_table_name =  $wpdb->prefix . "rg_form";

        //creating new form
        $wpdb->query($wpdb->prepare("INSERT INTO $form_table_name(title, date_created) VALUES(%s, utc_timestamp())", $form_title));

        //returning newly created form id
        return $wpdb->insert_id;

    }

    public static function update_form_meta($form_id, $form_meta){
        global $wpdb;
        $meta_table_name = self::get_meta_table_name();
        $form_meta = maybe_serialize($form_meta);

        if(intval($wpdb->get_var($wpdb->prepare("SELECT count(0) FROM $meta_table_name WHERE form_id=%d", $form_id))) > 0)
            $wpdb->query( $wpdb->prepare("UPDATE $meta_table_name SET display_meta=%s WHERE form_id=%d", $form_meta, $form_id) );
        else
            $wpdb->query( $wpdb->prepare("INSERT INTO $meta_table_name(form_id, display_meta) VALUES(%d, %s)", $form_id, $form_meta ) );
        }

    public static function delete_file($lead_id, $field_id){
        global $wpdb;

        if($lead_id == 0 || $field_id == 0)
            return;

        $lead_detail_table = self::get_lead_details_table_name();

        //Deleting file
        $sql = $wpdb->prepare("SELECT value FROM $lead_detail_table WHERE lead_id=%d AND field_number BETWEEN %f AND %f", $lead_id, $field_id - 0.001, $field_id + 0.001);
        $file_path = $wpdb->get_var($sql);

        //Convert from url to physical path
        $file_path = str_replace(WP_CONTENT_URL, WP_CONTENT_DIR, $file_path);
        unlink($file_path);

        //Delete from lead details
        $sql = $wpdb->prepare("DELETE FROM $lead_detail_table WHERE lead_id=%d AND field_number BETWEEN %f AND %f", $lead_id, $field_id - 0.001, $field_id + 0.001);
        $wpdb->query($sql);
    }

    public static function delete_field($form_id, $field_id){
        global $wpdb;

        if($form_id == 0)
            return;

        do_action("gform_before_delete_field", $form_id, $field_id);

        $lead_table = self::get_lead_table_name();
        $lead_detail_table = self::get_lead_details_table_name();
        $lead_detail_long_table = self::get_lead_details_long_table_name();


        $form = self::get_form_meta($form_id);

        //Deleting field from form meta
        $count = sizeof($form["fields"]);
        for($i = $count-1; $i >= 0; $i--){
            $field = $form["fields"][$i];

            //Deleting associated conditional logic rules
            if(!empty($field["conditionalLogic"])){
                $rule_count = sizeof($field["conditionalLogic"]["rules"]);
                for($j = $rule_count-1; $j >= 0; $j--){
                    if($field["conditionalLogic"]["rules"][$j]["fieldId"] == $field_id){
                        unset($form["fields"][$i]["conditionalLogic"]["rules"][$j]);
                    }
                }
                $form["fields"][$i]["conditionalLogic"]["rules"] = array_values($form["fields"][$i]["conditionalLogic"]["rules"]);

                //If there aren't any rules, remove the conditional logic
                if(sizeof($form["fields"][$i]["conditionalLogic"]["rules"]) == 0){
                    $form["fields"][$i]["conditionalLogic"] = false;
                }
            }

            //Deleting field from form meta
            if($field["id"] == $field_id){
                $field_type = $field["type"];
                unset($form["fields"][$i]);
            }

        }

        //removing post content and title template if the field being deleted is a post content field or post title field
        if($field_type == "post_content"){
            $form["postContentTemplateEnabled"] = false;
            $form["postContentTemplate"] = "";
        }
        else if($field_type == "post_title"){
            $form["postTitleTemplateEnabled"] = false;
            $form["postTitleTemplate"] = "";
        }

        //Deleting associated routing rules
        if(!empty($form["notification"]["routing"])){
            $routing_count = sizeof($form["notification"]["routing"]);
            for($j = $routing_count-1; $j >= 0; $j--){
                if(intval($form["notification"]["routing"][$j]["fieldId"]) == $field_id){
                    unset($form["notification"]["routing"][$j]);
                }
            }
            $form["notification"]["routing"] = array_values($form["notification"]["routing"]);

            //If there aren't any routing, remove it
            if(sizeof($form["notification"]["routing"]) == 0){
                $form["notification"]["routing"] = null;
            }
        }

        $form["fields"] = array_values($form["fields"]);
        self::update_form_meta($form_id, $form);

        //Delete from grid column meta
        $columns = self::get_grid_column_meta($form_id);
        $count = sizeof($columns);
        for($i = $count -1; $i >=0; $i--)
        {
            if(intval($columns[$i]) == intval($field_id)){
                unset($columns[$i]);
            }
        }
        self::update_grid_column_meta($form_id, $columns);

        //Delete from detail long
        $sql = $wpdb->prepare(" DELETE FROM $lead_detail_long_table
                                WHERE lead_detail_id IN(
                                    SELECT id FROM $lead_detail_table WHERE form_id=%d AND field_number >= %d AND field_number < %d
                                )", $form_id, $field_id, $field_id + 1);
        $wpdb->query($sql);

        //Delete from lead details
        $sql = $wpdb->prepare("DELETE FROM $lead_detail_table WHERE form_id=%d AND field_number >= %d AND field_number < %d", $form_id, $field_id, $field_id + 1);
        $wpdb->query($sql);

        //Delete leads with no details
        $sql = $wpdb->prepare(" DELETE FROM $lead_table
                                WHERE form_id=%d
                                AND id NOT IN(
                                    SELECT DISTINCT(lead_id) FROM $lead_detail_table WHERE form_id=%d
                                )", $form_id, $form_id);
        $wpdb->query($sql);

        do_action("gform_after_delete_field", $form_id, $field_id);
    }

    public static function delete_lead($lead_id){
        global $wpdb;

        if(!GFCommon::current_user_can_any("gravityforms_delete_entries"))
            die(__("You don't have adequate permission to delete entries.", "gravityforms"));

        $lead_table = self::get_lead_table_name();
        $lead_notes_table = self::get_lead_notes_table_name();
        $lead_detail_table = self::get_lead_details_table_name();
        $lead_detail_long_table = self::get_lead_details_long_table_name();

        //Delete from detail long
        $sql = $wpdb->prepare(" DELETE FROM $lead_detail_long_table
                                WHERE lead_detail_id IN(
                                    SELECT id FROM $lead_detail_table WHERE lead_id=%d
                                )", $lead_id);
        $wpdb->query($sql);

        //Delete from lead details
        $sql = $wpdb->prepare("DELETE FROM $lead_detail_table WHERE lead_id=%d", $lead_id);
        $wpdb->query($sql);

        //Delete from lead notes
        $sql = $wpdb->prepare("DELETE FROM $lead_notes_table WHERE lead_id=%d", $lead_id);
        $wpdb->query($sql);

        //Delete from lead
        $sql = $wpdb->prepare("DELETE FROM $lead_table WHERE id=%d", $lead_id);
        $wpdb->query($sql);
    }

    public static function add_note($lead_id, $user_id, $user_name, $note){
        global $wpdb;

        $table_name = self::get_lead_notes_table_name();
        $sql = $wpdb->prepare("INSERT INTO $table_name(lead_id, user_id, user_name, value, date_created) values(%d, %d, %s, %s, utc_timestamp())", $lead_id, $user_id, $user_name, $note);

        $wpdb->query($sql);
    }

    public static function delete_note($note_id){
        global $wpdb;

        if(!GFCommon::current_user_can_any("gravityforms_edit_entry_notes"))
            die(__("You don't have adequate permission to delete notes.", "gravityforms"));

        $table_name = self::get_lead_notes_table_name();
        $sql = $wpdb->prepare("DELETE FROM $table_name WHERE id=%d", $note_id);
        $wpdb->query($sql);
    }

    public static function delete_notes($notes){
        if(!is_array($notes))
            return;

        foreach($notes as $note_id){
            self::delete_note($note_id);
        }
    }

    public static function get_ip(){
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        if (!$ip)
            $ip = $_SERVER["REMOTE_ADDR"];

        $ip_array = explode(",", $ip); //HTTP_X_FORWARDED_FOR can return a comma separated list of IPs. Using the first one.
        return $ip_array[0];
    }

    public static function save_lead($form, &$lead){
        global $wpdb;

        if(IS_ADMIN && !GFCommon::current_user_can_any("gravityforms_edit_entries"))
            die(__("You don't have adequate permission to edit entries.", "gravityforms"));

        $lead_detail_table = self::get_lead_details_table_name();

        //Inserting lead if null
        if($lead == null){
            global $current_user;
            $user_id = $current_user && $current_user->ID ? $current_user->ID : 'NULL';

            $lead_table = RGFormsModel::get_lead_table_name();
            $user_agent = strlen($_SERVER["HTTP_USER_AGENT"]) > 250 ? substr($_SERVER["HTTP_USER_AGENT"], 0, 250) : $_SERVER["HTTP_USER_AGENT"];
            $currency = GFCommon::get_currency();
            $wpdb->query($wpdb->prepare("INSERT INTO $lead_table(form_id, ip, source_url, date_created, user_agent, currency, created_by) VALUES(%d, %s, %s, utc_timestamp(), %s, %s, {$user_id})", $form["id"], self::get_ip(), self::get_current_page_url(), $user_agent, $currency));

            //reading newly created lead id
            $lead_id = $wpdb->insert_id;
            $lead = array("id" => $lead_id);
        }

        $current_fields = $wpdb->get_results($wpdb->prepare("SELECT id, field_number FROM $lead_detail_table WHERE lead_id=%d", $lead["id"]));
        $original_post_id = $lead["post_id"];

        foreach($form["fields"] as $field){

            //Ignore fields that are marked as display only
            if($field["displayOnly"] && $field["type"] != "password"){
                continue;
            }

            //ignore pricing fields in the entry detail
            if(RG_CURRENT_VIEW == "entry" && GFCommon::is_pricing_field($field["type"])){
                continue;
            }

            //only save fields that are not hidden (except on entry screen)
            if(RG_CURRENT_VIEW == "entry" || !RGFormsModel::is_field_hidden($form, $field, array()) ){

                if(is_array($field["inputs"])){
                    foreach($field["inputs"] as $input)
                        self::save_input($form, $field, $lead, $current_fields, $input["id"]);
                }
                else{
                    self::save_input($form, $field, $lead, $current_fields, $field["id"]);
                }
            }
        }

    }

    public static function is_field_hidden($form, $field, $field_values){

        $section = self::get_section($form, $field["id"]);
        $section_display = self::get_field_display($form, $section, $field_values);

        //if section is hidden, hide field no matter what. if section is visible, see if field is supposed to be visible
        if($section_display == "hide")
            return true;
        else if(self::is_page_hidden($form, $field["page_number"], $field_values)){
            return true;
        }
        else{
            $display = self::get_field_display($form, $field, $field_values);
            return $display == "hide";
        }
    }

    public static function is_page_hidden($form, $page_number, $field_values){
        $page = self::get_page_by_number($form, $page_number);

        if(!$page)
            return false;

        $display = self::get_field_display($form, $page, $field_values);
        return $display == "hide";
    }

    public static function get_page_by_number($form, $page_number){
        foreach($form["fields"] as $field){
            if($field["type"] == "page" && $field["pageNumber"] == $page_number)
                return $field;
        }
        return null;
    }

    public static function get_page_by_field($form, $field){
        return get_page_by_number($field["page_number"]);
    }

    //gets the section that the specified field belongs to, or null if none
    public static function get_section($form, $field_id){
        $current_section = null;
        foreach($form["fields"] as $field){
            if($field["type"] == "section")
                $current_section = $field;

            //stop section at a page break (sections don't go cross page)
            if($field["type"] == "page")
                $current_section = null;

            if($field["id"] == $field_id)
                return $current_section;
        }

        return null;
    }

    public static function is_value_match($field_value, $target_value){
        if(is_array($field_value)){
            foreach($field_value as $val){
                if(GFCommon::get_selection_value($val) == $target_value)
                    return true;
            }
        }
        else if(GFCommon::get_selection_value($field_value) == $target_value){
            return true;
        }

        return false;
    }

    private static function get_field_display($form, $field, $field_values){

        $logic = RGForms::get("conditionalLogic", $field);

        //if this field does not have any conditional logic associated with it, it won't be hidden
        if(empty($logic))
            return "show";

        $match_count = 0;
        foreach($logic["rules"] as $rule){
            $source_field = RGFormsModel::get_field($form, $rule["fieldId"]);
            $field_value = self::get_field_value($source_field, $field_values);
            $is_value_match = self::is_value_match($field_value, $rule["value"]);

            if( ($rule["operator"] == "is" && $is_value_match ) || ($rule["operator"] == "isnot" && !$is_value_match) )
                $match_count++;
        }

        $do_action = ($logic["logicType"] == "all" && $match_count == sizeof($logic["rules"]) ) || ($logic["logicType"] == "any"  && $match_count > 0);
        $is_hidden = ($do_action && $logic["actionType"] == "hide") || (!$do_action && $logic["actionType"] == "show");

        return $is_hidden ? "hide" : "show";
    }

    public static function get_field_value($field, $field_values = array()){

        switch(RGFormsModel::get_input_type($field)){
            case "post_image" :
                $value[$field["id"] . ".1"] = self::get_input_value($field, "input_" . $field["id"] . "_1");
                $value[$field["id"] . ".4"] = self::get_input_value($field, "input_" . $field["id"] . "_4");
                $value[$field["id"] . ".7"] = self::get_input_value($field, "input_" . $field["id"] . "_7");
            break;
            case "checkbox" :
                $parameter_values = explode(",", self::get_parameter_value($field["inputName"], $field_values));

                if(!is_array($field["inputs"]))
                    return "";

                $choice_index = 0;
                foreach($field["inputs"] as $input){
                    if(!empty($_POST["is_submit_" . $field["formId"]])){
                        $value[strval($input["id"])] = stripslashes($_POST["input_" . str_replace('.', '_', strval($input["id"]))]);
                    }
                    else{
                        foreach($parameter_values as $item){
                            $item = trim($item);
                            if(self::choice_value_match($field, $field["choices"][$choice_index], $item))
                            {
                                $value[$input["id"]] = $item;
                                break;
                            }
                        }
                    }
                    $choice_index++;
                }

            break;

            default:

                if(is_array($field["inputs"])){
                    foreach($field["inputs"] as $input){
                        $value[strval($input["id"])] = self::get_input_value($field, "input_" . str_replace('.', '_', strval($input["id"])), RGForms::get("name", $input), $field_values);
                    }
                }
                else{
                    $value = self::get_input_value($field, "input_" . $field["id"], $field["inputName"], $field_values);
                }
            break;
        }

        return $value;
    }

    private static function get_input_value($field, $standard_name, $custom_name = "", $field_values=array()){
        if(!empty($_POST["is_submit_" . $field["formId"]])){
            $value = RGForms::post($standard_name);
            if(!is_array($value))
                $value = stripslashes($value);

            return $value;
        }
        else if($field["allowsPrepopulate"]){
            return self::get_parameter_value($custom_name, $field_values);
        }
    }

    private static function get_parameter_value($name, $field_values){
        $value = stripslashes($_GET[$name]);
        if(empty($value))
            $value = $field_values[$name];

        return apply_filters("gform_field_value_$name", $value);
    }

    private static function get_default_value($field, $input_id){
        if(!is_array($field["choices"])){
            return IS_ADMIN ? $field["defaultValue"] : GFCommon::replace_variables_prepopulate($field["defaultValue"]);
        }
        else if($field["type"] == "checkbox"){
            for($i=0, $count=sizeof($field["inputs"]); $i<$count; $i++){
                $input = $field["inputs"][$i];
                $choice = $field["choices"][$i];
                if($input["id"] == $input_id && $choice["isSelected"]){
                    return $choice["value"];
                }
            }
            return "";
        }
        else{
            foreach($field["choices"] as $choice){
                if($choice["isSelected"] || $field["type"] == "post_category")
                    return $choice["value"];
            }
            return "";
        }

    }

    public static function get_input_type($field){
        return empty($field["inputType"]) ? $field["type"] : $field["inputType"];
    }

    private static function get_post_field_value($field, $lead){

        if(is_array($field["inputs"])){
            $value = array();
            foreach($field["inputs"] as $input){
                $val = isset($lead[$input["id"]]) ? $lead[$input["id"]] : "";
                if(!empty($val))
                    $value[] = $val;
            }
            $value = implode(",", $value);
        }
        else{
            $value = isset($lead[$field["id"]]) ? $lead[$field["id"]] : "";
        }
        return $value;
    }

    private static function get_post_fields($form, $lead){

        $post_data = array();
        $post_data["post_custom_fields"] = array();
        $post_data["tags_input"] = array();
        $categories = array();
        $images = array();

        foreach($form["fields"] as $field){

            $value = self::get_post_field_value($field, $lead);

            switch($field["type"]){
                case "post_title" :
                case "post_excerpt" :
                case "post_content" :
                    $post_data[$field["type"]] = $value;
                break;

                case "post_tags" :
                    $tags = explode(",", $value);
                    if(is_array($tags) && sizeof($tags) > 0)
                        $post_data["tags_input"] = array_merge($post_data["tags_input"], $tags) ;
                break;

                case "post_custom_field" :
                    $meta_name = $field["postCustomFieldName"];
                    if(!isset($post_data["post_custom_fields"][$meta_name])){
                        $post_data["post_custom_fields"][$meta_name] = $value;
                    }
                    else if(!is_array($post_data["post_custom_fields"][$meta_name])){
                        $post_data["post_custom_fields"][$meta_name] = array($post_data["post_custom_fields"][$meta_name], $value);
                    }
                    else{
                        $post_data["post_custom_fields"][$meta_name][] = $value;
                    }

                break;

                case "post_category" :
                    $category = get_term_by( 'name', $value, 'category' );
                    array_push($categories, $category->term_id);
                break;

                case "post_image" :
                    list($url, $title, $caption, $description) = !empty($value) ? explode("|:|", $value) : array();
                    array_push($images, array("field_id" => $field["id"], "url" => $url, "title" => $title, "description" => $description, "caption" => $caption));
                break;
            }
        }

        $post_data["post_status"] = $form["postStatus"];
        $post_data["post_category"] = !empty($categories) ? $categories : array($form["postCategory"]);
        $post_data["images"] = $images;

        //setting current user as author depending on settings
        global $current_user;
        $post_data["post_author"] = $form["useCurrentUserAsAuthor"] && !empty($current_user->ID) ? $current_user->ID : $form["postAuthor"];

        return $post_data;
    }

    public static function get_custom_field_names(){
        global $wpdb;
        $keys = $wpdb->get_col( "
        SELECT meta_key
        FROM $wpdb->postmeta
        WHERE meta_key NOT LIKE '\_%'
        GROUP BY meta_key
        ORDER BY meta_id DESC");

        if ( $keys )
            natcasesort($keys);

        return $keys;
    }

    private static function get_default_post_title(){
        global $wpdb;
        $title = "Untitled";
        $count = 1;

        while($wpdb->get_var($wpdb->prepare("SELECT count(0) FROM $wpdb->posts WHERE post_title=%s", $title)) > 0){
            $title = "Untitled_$count";
            $count++;
        }
        return $title;
    }

    private static function prepare_value($form_id, $field, $value, $input_name){
        $input_type = self::get_input_type($field);
        switch($input_type)
        {
            case "post_category" :
                $cat = get_category($value);
                $value = $cat->name;
            break;

            case "phone" :
                if($field["phoneFormat"] == "standard" && preg_match('/^\D?(\d{3})\D?\D?(\d{3})\D?(\d{4})$/', $value, $matches))
                    $value = sprintf("(%s)%s-%s", $matches[1], $matches[2], $matches[3]);
            break;

            case "time":

                if(!is_array($value) && !empty($value)){
                    preg_match('/^(\d*):(\d*) ?(.*)$/', $value, $matches);
                    $value = array();
                    $value[0] = $matches[1];
                    $value[1] = $matches[2];
                    $value[2] = $matches[3];
                }

                $hour = empty($value[0]) ? "0" : strip_tags($value[0]);
                $minute = empty($value[1]) ? "0" : strip_tags($value[1]);
                $ampm = strip_tags($value[2]);

                if(!(empty($hour) && empty($minute)))
                    $value = sprintf("%02d:%02d %s", $hour, $minute, $ampm);
                else
                    $value = "";

            break;

            case "date" :
                $format = empty($field["dateFormat"]) ? "mdy" : $field["dateFormat"];
                $date_info = GFCommon::parse_date($value, $format);
                if(!empty($date_info))
                    $value = sprintf("%d-%02d-%02d", $date_info["year"], $date_info["month"], $date_info["day"]);
                else
                    $value = "";

            break;

            case "post_image":
                $url = self::get_fileupload_value($form_id, $input_name);
                $image_title = isset($_POST["{$input_name}_1"]) ? strip_tags($_POST["{$input_name}_1"]) : "";
                $image_caption = isset($_POST["{$input_name}_4"]) ? strip_tags($_POST["{$input_name}_4"]) : "";
                $image_description = isset($_POST["{$input_name}_7"]) ? strip_tags($_POST["{$input_name}_7"]) : "";

                $value = !empty($url) ? $url . "|:|" . $image_title . "|:|" . $image_caption . "|:|" . $image_description : "";
            break;

            case "fileupload" :
                $value = self::get_fileupload_value($form_id, $input_name);
            break;

            case "number" :
                $value = GFCommon::clean_number($value);
            break;

            default:
                $value = stripslashes($value);

                //allow HTML for certain field types
                if(!in_array($field["type"], array("post_custom_field", "post_title", "post_content", "post_excerpt", "post_tags")) && !in_array($input_type, array("checkbox", "radio"))){
                    $value = strip_tags($value);
                }

                //do not save price fields with blank price
                if($field["enablePrice"]){
                    list($label, $price) = explode("|", $value);
                    $is_empty = (strlen(trim($price)) <= 0);
                    if($is_empty)
                        $value = "";
                }
            break;
        }
        return $value;
    }

    private static function get_fileupload_value($form_id, $input_name){
        global $_gf_uploaded_files;
        if(empty($_gf_uploaded_files))
            $_gf_uploaded_files = array();

        if(!isset($_gf_uploaded_files[$input_name])){

            //check if file has already been uploaded by previous step
            $file_info = self::get_temp_filename($form_id, $input_name);
            $temp_filepath = self::get_upload_path($form_id) . "/tmp/" . $file_info["temp_filename"];
            if($file_info && file_exists($temp_filepath)){
                $_gf_uploaded_files[$input_name] = self::move_temp_file($form_id, $file_info);
            }
            else if (!empty($_FILES[$input_name]["name"])){
                $_gf_uploaded_files[$input_name] = self::upload_file($form_id, $_FILES[$input_name]);
            }
        }

        return $_gf_uploaded_files[$input_name];
    }

    public static function get_form_unique_id($form_id){
        if(RGForms::post("gform_submit") == $form_id)
            return RGForms::post("gform_unique_id");
        else
            return uniqid();
    }

    public static function get_temp_filename($form_id, $input_name){

        $uploaded_filename = !empty($_FILES[$input_name]["name"]) ? $_FILES[$input_name]["name"] : "";

        if(empty($uploaded_filename) && isset(self::$uploaded_files[$form_id]))
            $uploaded_filename = self::$uploaded_files[$form_id][$input_name];

        if(empty($uploaded_filename))
            return false;

        $form_unique_id = self::get_form_unique_id($form_id);
        $pathinfo = pathinfo($uploaded_filename);
        return array("uploaded_filename" => $uploaded_filename, "temp_filename" => "{$form_unique_id}_{$input_name}.{$pathinfo["extension"]}");

    }

    public static function get_choice_text($field, $value, $input_id=0){
        if(!is_array($field["choices"]))
            return $value;

        foreach($field["choices"] as $choice){
            if(is_array($value) && self::choice_value_match($field, $choice, $value[$input_id])){
                return $choice["text"];
            }
            else if(self::choice_value_match($field, $choice, $value)){
                return $choice["text"];
            }
        }
        return is_array($value) ? "" : $value;
    }


    public static function choice_value_match($field, $choice, $value){

        if($choice["value"] == $value){
           return true;
        }
        else if($field["enablePrice"]){
            list($val, $price) = explode("|", $value);
            if($val == $choice["value"])
                return true;
        }
        return false;
    }

    public static function create_post($form, &$lead){

        $has_post_field = false;
        foreach($form["fields"] as $field){
            $is_hidden = self::is_field_hidden($form, $field, array());
            if(!$is_hidden && in_array($field["type"], array("post_category","post_title","post_content","post_excerpt","post_tags","post_custom_fields","post_image"))){
                $has_post_field = true;
                break;
            }
        }

        //if this form does not have any post fields, don't create a post
        if(!$has_post_field)
            return $lead;

        //processing post fields
        $post_data = self::get_post_fields($form, $lead);

        //allowing users to change post fields before post gets created
        $post_data = apply_filters("gform_post_data_{$form["id"]}", apply_filters("gform_post_data", $post_data , $form, $lead), $form, $lead);

        //adding default title if none of the required post fields are in the form (will make sure wp_insert_post() inserts the post)
        if(empty($post_data["post_title"]) && empty($post_data["post_content"]) && empty($post_data["post_excerpt"])){
            $post_data["post_title"] = self::get_default_post_title();
        }

        //inserting post
        $post_id = wp_insert_post($post_data);

        //adding form id and entry id hidden custom fields
        add_post_meta($post_id, "_gform-form-id", $form["id"]);
        add_post_meta($post_id, "_gform-entry-id", $lead["id"]);

        //creating post images
        $post_images = array();
        foreach($post_data["images"] as $image){
            $image_meta= array( "post_excerpt" => $image["caption"],
                                "post_content" => $image["description"]);

            //adding title only if it is not empty. It will default to the file name if it is not in the array
            if(!empty($image["title"]))
                $image_meta["post_title"] = $image["title"];

            if(!empty($image["url"])){
                $media_id = self::media_handle_upload($image["url"], $post_id, $image_meta);

                if($media_id){
                    //save media id for post body/title template variable replacement (below)
                    $post_images[$image["field_id"]] = $media_id;
                }
            }
        }

        //adding custom fields
        foreach($post_data["post_custom_fields"] as $meta_name => $meta_value){
            if(!is_array($meta_value))
                $meta_value = array($meta_value);

            $meta_index = 0;
            foreach($meta_value as $value){
                $custom_field = self::get_custom_field($form, $meta_name, $meta_index);

                //replacing template variables if template is enabled
                if($custom_field && $custom_field["customFieldTemplateEnabled"]){
                    //replacing post image variables
                    $value = GFCommon::replace_variables_post_image($custom_field["customFieldTemplate"], $post_images, $lead);

                    //replacing all other variables
                    $value = GFCommon::replace_variables($value, $form, $lead, false, false, false);
                }

                add_post_meta($post_id, $meta_name, $value);
                $meta_index++;
            }
        }

        $has_content_field = sizeof(GFCommon::get_fields_by_type($form, array("post_content"))) > 0;
        $has_title_field = sizeof(GFCommon::get_fields_by_type($form, array("post_title"))) > 0;

        //if a post field was configured with a content or title template, process template
        if( ($form["postContentTemplateEnabled"] && $has_content_field) || ($form["postTitleTemplateEnabled"] && $has_title_field) ){

            $post = get_post($post_id);

            if($form["postContentTemplateEnabled"] && $has_content_field){

                //replacing post image variables
                $post_content = GFCommon::replace_variables_post_image($form["postContentTemplate"], $post_images, $lead);

                //replacing all other variables
                $post_content = GFCommon::replace_variables($post_content, $form, $lead, false, false, false);

                //updating post content
                $post->post_content = $post_content;
            }

            if($form["postTitleTemplateEnabled"] && $has_title_field){

                //replacing post image variables
                $post_title = GFCommon::replace_variables_post_image($form["postTitleTemplate"], $post_images, $lead);

                //replacing all other variables
                $post_title = GFCommon::replace_variables($post_title, $form, $lead, false, false, false);

                //updating post
                $post->post_title = $post_title;

                $post->post_name = $post_title;
            }

            wp_update_post($post);
        }

        //update post_id field if a post was created
        $lead["post_id"] = $post_id;
        self::update_lead($lead);

        return $post_id;
    }

    private static function get_custom_field($form, $meta_name, $meta_index){
        $custom_fields = GFCommon::get_fields_by_type($form, array("post_custom_field"));

        $index = 0;
        foreach($custom_fields as $field){
            if($field["postCustomFieldName"] == $meta_name){
                if($meta_index == $index){
                    return $field;
                }
                $index++;
            }
        }
        return false;
    }

    private static function copy_post_image($url){
        $time = current_time('mysql');
        if ( $post = get_post($post_id) ) {
            if ( substr( $post->post_date, 0, 4 ) > 0 )
                $time = $post->post_date;
        }

        //making sure there is a valid upload folder
        if ( ! ( ( $uploads = wp_upload_dir($time) ) && false === $uploads['error'] ) )
            return false;

        $name = basename($url);

        $filename = wp_unique_filename($uploads['path'], $name);

        // Move the file to the uploads dir
        $new_file = $uploads['path'] . "/$filename";

        $uploaddir = wp_upload_dir();
        $path = str_replace($uploaddir["baseurl"], $uploaddir["basedir"], $url);

        if(!copy($path, $new_file))
            return false;

        // Set correct file permissions
        $stat = stat( dirname( $new_file ));
        $perms = $stat['mode'] & 0000666;
        @ chmod( $new_file, $perms );

        // Compute the URL
        $url = $uploads['url'] . "/$filename";

        if ( is_multisite() )
            delete_transient( 'dirsize_cache' );

        $type = wp_check_filetype($new_file);
        return array("file" => $new_file, "url" => $url, "type" => $type["type"]);

    }

    private static function media_handle_upload($url, $post_id, $post_data = array()) {

        //WordPress Administration API required for the media_handle_upload() function
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $name = basename($url);

        $file = self::copy_post_image($url);

        if(!$file)
            return false;

        $name_parts = pathinfo($name);
        $name = trim( substr( $name, 0, -(1 + strlen($name_parts['extension'])) ) );

        $url = $file['url'];
        $type = $file['type'];
        $file = $file['file'];
        $title = $name;
        $content = '';

        // use image exif/iptc data for title and caption defaults if possible
        if ( $image_meta = @wp_read_image_metadata($file) ) {
            if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) )
                $title = $image_meta['title'];
            if ( trim( $image_meta['caption'] ) )
                $content = $image_meta['caption'];
        }

        // Construct the attachment array
        $attachment = array_merge( array(
            'post_mime_type' => $type,
            'guid' => $url,
            'post_parent' => $post_id,
            'post_title' => $title,
            'post_content' => $content,
        ), $post_data );

        // Save the data
        $id = wp_insert_attachment($attachment, $file, $post_id);
        if ( !is_wp_error($id) ) {
            wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
        }

        return $id;
    }

    private static function save_input($form, $field, &$lead, $current_fields, $input_id){
        global $wpdb;

        $lead_detail_table = self::get_lead_details_table_name();
        $lead_detail_long_table = self::get_lead_details_long_table_name();

        $input_name = "input_" . str_replace('.', '_', $input_id);
        $value = $_POST[$input_name];

        //ignore file upload when nothing was sent in the admin
        //ignore post fields in the admin
        if(RG_CURRENT_VIEW == "entry" && self::get_input_type($field) == "fileupload" && empty($_FILES[$input_name]["name"]))
            return;
        else if(RG_CURRENT_VIEW == "entry" && in_array($field["type"], array("post_category","post_title","post_content","post_excerpt","post_tags","post_custom_fields","post_image")))
            return;

        if(empty($value) && $field["adminOnly"] && !IS_ADMIN){
            $value = self::get_default_value($field, $input_id);
        }

        //processing values so that they are in the correct format for each input type
        $value = self::prepare_value($form["id"], $field, $value, $input_name);

        //ignore fields that have not changed
        if($lead != null && $value == $lead[$input_id])
            return;

        if(!empty($value) || $value === "0"){
            $truncated_value = substr($value, 0, GFORMS_MAX_FIELD_LENGTH);

            $lead_detail_id = self::get_lead_detail_id($current_fields, $input_id);
            if($lead_detail_id > 0){

                //filtering truncated value
                $truncated_value = apply_filters("gform_save_field_value", $truncated_value, $lead, $field, $form);
                $wpdb->update($lead_detail_table, array("value" => $truncated_value), array("id" => $lead_detail_id), array("%s"), array("%d"));

                //insert, update or delete long value
                $sql = $wpdb->prepare("SELECT count(0) FROM $lead_detail_long_table WHERE lead_detail_id=%d", $lead_detail_id);
                $has_long_field = intval($wpdb->get_var($sql)) > 0;

                //delete long field if value has been shortened
                if($has_long_field && strlen($value) <= GFORMS_MAX_FIELD_LENGTH){
                    $sql = $wpdb->prepare("DELETE FROM $lead_detail_long_table WHERE lead_detail_id=%d", $lead_detail_id);
                    $wpdb->query($sql);
                }
                //update long field
                else if($has_long_field){
                    //filtering long value
                    $value = apply_filters("gform_save_field_value", $value, $lead, $field, $form);
                    $wpdb->update($lead_detail_long_table, array("value" => $value), array("lead_detail_id" => $lead_detail_id), array("%s"), array("%d"));
                }
                //insert long field (value has been increased)
                else if(strlen($value) > GFORMS_MAX_FIELD_LENGTH){
                    //filtering long value
                    $value = apply_filters("gform_save_field_value", $value, $lead, $field, $form);
                    $wpdb->insert($lead_detail_long_table, array("lead_detail_id" => $lead_detail_id, "value" => $value), array("%d", "%s"));
                }

            }
            else{
                //filtering truncated value
                $truncated_value = apply_filters("gform_save_field_value", $truncated_value, $lead, $field, $form);
                $wpdb->insert($lead_detail_table, array("lead_id" => $lead["id"], "form_id" => $form["id"], "field_number" => $input_id, "value" => $truncated_value), array("%d", "%d", "%f", "%s"));

                if(strlen($value) > GFORMS_MAX_FIELD_LENGTH){

                    //read newly created lead detal id
                    $lead_detail_id = $wpdb->insert_id;

                    //filtering long value
                    $value = apply_filters("gform_save_field_value", $value, $lead, $field, $form);

                    //insert long value
                    $wpdb->insert($lead_detail_long_table, array("lead_detail_id" => $lead_detail_id, "value" => $value), array("%d", "%s"));
                }
            }
        }
        else{
            //Deleting details for this field
            $sql = $wpdb->prepare("DELETE FROM $lead_detail_table WHERE lead_id=%d AND field_number BETWEEN %f AND %f ", $lead["id"], $input_id - 0.001, $input_id + 0.001);
            $wpdb->query($sql);

            //Deleting long field if there is one
            $sql = $wpdb->prepare("DELETE FROM $lead_detail_long_table
                                    WHERE lead_detail_id IN(
                                        SELECT id FROM $lead_detail_table WHERE lead_id=%d AND field_number BETWEEN %f AND %f
                                    )",
                                    $lead["id"], $input_id - 0,001, $input_id + 0.001);
            $wpdb->query($sql);
        }
    }

    private static function move_temp_file($form_id, $tempfile_info){

        $target = self::get_file_upload_path($form_id, $tempfile_info["uploaded_filename"]);
        $source = self::get_upload_path($form_id) . "/tmp/" . $tempfile_info["temp_filename"];

        if(rename($source, $target["path"])){
            self::set_permissions($target["path"]);
            return $target["url"];
        }
        else{
            return "FAILED (Temporary file could not be moved.)";
        }
    }

    private static function set_permissions($path){
        $permission = apply_filters("gform_file_permission", false, $path);
        if($permission){
            $result = chmod($path, $permission);
        }
    }

    public static function upload_file($form_id, $file){

        $target = self::get_file_upload_path($form_id, $file["name"]);
        if(!$target)
            return "FAILED (Upload folder could not be created.)";

        if(move_uploaded_file($file['tmp_name'], $target["path"])){
            self::set_permissions($target["path"]);
            return $target["url"];
        }
        else{
            return "FAILED (Temporary file could not be copied.)";
        }
    }


    public static function get_upload_root(){
        $dir = wp_upload_dir();

        if($dir["error"])
            return null;

        return $dir["basedir"] . "/gravity_forms/";
    }

    public static function get_upload_path($form_id){
        return self::get_upload_root() . $form_id;
    }

    public static function get_upload_url($form_id){
        $dir = wp_upload_dir();
        return $dir["baseurl"] . "/gravity_forms/$form_id";
    }

    public static function get_file_upload_path($form_id, $file_name)
    {
        if (get_magic_quotes_gpc())
            $file_name = stripslashes($file_name);

        // Where the file is going to be placed
        // Generate the yearly and monthly dirs
        $time = current_time( 'mysql' );
        $y = substr( $time, 0, 4 );
        $m = substr( $time, 5, 2 );
        $target_root = self::get_upload_path($form_id) . "/$y/$m/";
        $target_root_url = self::get_upload_url($form_id) . "/$y/$m/";

        //adding filter to upload root path and url
        $upload_root_info = array("path" => $target_root, "url" => $target_root_url);
        $upload_root_info = apply_filters("gform_upload_path_{$form_id}", apply_filters("gform_upload_path", $upload_root_info, $form_id));

        $target_root = $upload_root_info["path"];
        $target_root_url = $upload_root_info["url"];

        if(!wp_mkdir_p($target_root))
            return false;

        //Add the original filename to our target path.
        //Result is "uploads/filename.extension"
        $file_info = pathinfo($file_name);
        $extension = $file_info["extension"];
        $file_name = basename($file_info["basename"], "." . $extension);

        $counter = 1;
        $target_path = $target_root . $file_name . "." . $extension;
        while(file_exists($target_path)){
            $target_path = $target_root . $file_name . "$counter" . "." . $extension;
            $counter++;
        }

        //creating url
        $target_url = str_replace($target_root, $target_root_url, $target_path);

        return array("path" => $target_path, "url" => $target_url);
    }

    public static function drop_tables(){
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS " . self::get_lead_details_long_table_name());
        $wpdb->query("DROP TABLE IF EXISTS " . self::get_lead_notes_table_name());
        $wpdb->query("DROP TABLE IF EXISTS " . self::get_lead_details_table_name());
        $wpdb->query("DROP TABLE IF EXISTS " . self::get_lead_table_name());
        $wpdb->query("DROP TABLE IF EXISTS " . self::get_form_view_table_name());
        $wpdb->query("DROP TABLE IF EXISTS " . self::get_meta_table_name());
        $wpdb->query("DROP TABLE IF EXISTS " . self::get_form_table_name());
    }

    public static function insert_form_view($form_id, $ip){
        global $wpdb;
        $table_name = self::get_form_view_table_name();

        $sql = $wpdb->prepare(" SELECT id FROM $table_name
                                WHERE form_id=%d
                                AND year(date_created) = year(utc_timestamp())
                                AND month(date_created) = month(utc_timestamp())
                                AND day(date_created) = day(utc_timestamp())
                                AND hour(date_created) = hour(utc_timestamp())", $form_id);

        $id = $wpdb->get_var($sql, 0, 0);

        if(empty($id))
            $wpdb->query($wpdb->prepare("INSERT INTO $table_name(form_id, date_created, ip) values(%d, utc_timestamp(), %s)", $form_id, $ip));
        else
            $wpdb->query($wpdb->prepare("UPDATE $table_name SET count = count+1 WHERE id=%d", $id));
    }

    public static function is_duplicate($form_id, $field, $value){
        global $wpdb;
        $lead_detail_table_name = self::get_lead_details_table_name();
        $lead_table_name = self::get_lead_table_name();

        switch(RGFormsModel::get_input_type($field)){
            case "time" :
                $value = sprintf("%d:%02d %s", $value[0], $value[1], $value[2]);
            break;
         }


        $inner_sql_template = " SELECT %s as input, ld.lead_id
                                FROM $lead_detail_table_name ld
                                INNER JOIN $lead_table_name l ON l.id = ld.lead_id
                                WHERE l.form_id=%d AND ld.form_id=%d
                                AND ld.field_number between %s AND %s
                                AND ld.value=%s";

        $sql = "SELECT count(distinct input) as match_count FROM ( ";

        $input_count = 1;
        if(is_array($field["inputs"])){
            $input_count = sizeof($field["inputs"]);
            foreach($field["inputs"] as $input){
                $union = empty($inner_sql) ? "" : " UNION ALL ";
                $inner_sql .= $union . $wpdb->prepare($inner_sql_template, $input["id"], $form_id, $form_id, $input["id"] - 0.001, $input["id"] + 0.001, $value[$input["id"]]);
            }
        }
        else{
            $inner_sql = $wpdb->prepare($inner_sql_template, $field["id"], $form_id, $form_id, $field["id"] - 0.001, $field["id"] + 0.001, $value);
        }

        $sql .= $inner_sql . "
                ) as count
                GROUP BY lead_id
                ORDER BY match_count DESC";

        $count = apply_filters("gform_is_duplicate_{$form_id}", apply_filters('gform_is_duplicate', $wpdb->get_var($sql), $form_id, $field, $value), $form_id, $field, $value);

        return $count != null && $count >= $input_count;
    }

    public static function get_lead($lead_id){
        global $wpdb;
        $lead_detail_table_name = self::get_lead_details_table_name();
        $lead_table_name = self::get_lead_table_name();

        $results = $wpdb->get_results($wpdb->prepare("  SELECT l.*, field_number, value
                                                        FROM $lead_table_name l
                                                        INNER JOIN $lead_detail_table_name ld ON l.id = ld.lead_id
                                                        WHERE l.id=%d", $lead_id));

        $leads = self::build_lead_array($results, true);
        return sizeof($leads) == 1 ? $leads[0] : false;
    }

    public static function get_lead_notes($lead_id){
        global $wpdb;
        $notes_table = self::get_lead_notes_table_name();

        return $wpdb->get_results($wpdb->prepare("  SELECT n.id, n.user_id, n.date_created, n.value, ifnull(u.display_name,n.user_name) as user_name, u.user_email
                                                    FROM $notes_table n
                                                    LEFT OUTER JOIN $wpdb->users u ON n.user_id = u.id
                                                    WHERE lead_id=%d ORDER BY id", $lead_id));
    }

    public static function get_lead_field_value($lead, $field){
        if(empty($lead))
            return;

        $max_length = GFORMS_MAX_FIELD_LENGTH;

        if(is_array($field["inputs"])){
            //making sure values submitted are sent in the value even if
            //there isn't an input associated with it
            $lead_field_keys = array_keys($lead);
            foreach($lead_field_keys as $input_id){
                if(is_numeric($input_id) && absint($input_id) == absint($field["id"])){
                    $val = $lead[$input_id];
                    $value[$input_id] = strlen($val) >= $max_length ? self::get_field_value_long($lead["id"], $input_id) : $val;
                }
            }
        }
        else{
            $val = $lead[$field["id"]];

            //To save a database call to get long text, only getting long text if regular field is "somewhat" large (i.e. max - 50)
            if(strlen($val) >= ($max_length - 50))
                $long_text = self::get_field_value_long($lead["id"], $field["id"]);

            $value = !empty($long_text) ? $long_text : $val;
        }

        //filtering lead value
        $value = apply_filters("gform_get_field_value", $value, $lead, $field);

        return $value;
    }

    public static function get_field_value_long($lead_id, $field_number){
        global $wpdb;
        $detail_table_name = self::get_lead_details_table_name();
        $long_table_name = self::get_lead_details_long_table_name();

        $sql = $wpdb->prepare(" SELECT l.value FROM $detail_table_name d
                                INNER JOIN $long_table_name l ON l.lead_detail_id = d.id
                                WHERE lead_id=%d AND field_number BETWEEN %f AND %f", $lead_id, $field_number - 0.001, $field_number + 0.001);
         return $wpdb->get_var($sql);
    }

    public static function get_leads($form_id, $sort_field_number=0, $sort_direction='DESC', $search='', $offset=0, $page_size=30, $star=null, $read=null, $is_numeric_sort = false, $start_date=null, $end_date=null){
        global $wpdb;

        if($sort_field_number == 0)
            $sort_field_number = "date_created";

        if(is_numeric($sort_field_number))
            $sql = self::sort_by_custom_field_query($form_id, $sort_field_number, $sort_direction, $search, $offset, $page_size, $star, $read, $is_numeric_sort);
        else
            $sql = self::sort_by_default_field_query($form_id, $sort_field_number, $sort_direction, $search, $offset, $page_size, $star, $read, $is_numeric_sort, $start_date, $end_date);

        //initializing rownum
        $wpdb->query("select @rownum:=0");

        //getting results
        $results = $wpdb->get_results($sql);

        return self::build_lead_array($results);
    }

    private static function sort_by_custom_field_query($form_id, $sort_field_number=0, $sort_direction='DESC', $search='', $offset=0, $page_size=30, $star=null, $read=null, $is_numeric_sort = false){
        global $wpdb;
        if(!is_numeric($form_id) || !is_numeric($sort_field_number)|| !is_numeric($offset)|| !is_numeric($page_size))
            return "";

        $lead_detail_table_name = self::get_lead_details_table_name();
        $lead_table_name = self::get_lead_table_name();

        $orderby = $is_numeric_sort ? "ORDER BY query, (value+0) $sort_direction" : "ORDER BY query, value $sort_direction";

        //$search = empty($search) ? "" : "WHERE d.value LIKE '%$search%' ";
        $search_term = "%$search%";
        $search_filter = empty($search) ? "" : $wpdb->prepare("WHERE d.value LIKE %s", $search_term);

        $where = empty($search) ? "WHERE" : "AND";
        $search_filter .= $star !== null ? $wpdb->prepare("$where is_starred=%d ", $star) : "";

        $where = empty($search) ? "WHERE" : "AND";
        $search_filter .= $read !== null ? $wpdb->prepare("$where is_read=%d ", $read) : "";

        $field_number_min = $sort_field_number - 0.001;
        $field_number_max = $sort_field_number + 0.001;

        $sql = "
            SELECT filtered.sort, l.*, d.field_number, d.value
            FROM $lead_table_name l
            INNER JOIN $lead_detail_table_name d ON d.lead_id = l.id
            INNER JOIN (
                SELECT distinct sorted.sort, l.id
                FROM $lead_table_name l
                INNER JOIN $lead_detail_table_name d ON d.lead_id = l.id
                INNER JOIN (
                    SELECT @rownum:=@rownum+1 as sort, id FROM (
                        SELECT 0 as query, lead_id as id, value
                        FROM $lead_detail_table_name
                        WHERE form_id=$form_id
                        AND field_number between $field_number_min AND $field_number_max

                        UNION ALL

                        SELECT 1 as query, l.id, d.value
                        FROM $lead_table_name l
                        LEFT OUTER JOIN $lead_detail_table_name d ON d.lead_id = l.id AND field_number between $field_number_min AND $field_number_max
                        WHERE l.form_id=$form_id
                        AND d.lead_id IS NULL

                    ) sorted1
                   $orderby
                ) sorted ON d.lead_id = sorted.id
                $search_filter
                LIMIT $offset,$page_size
            ) filtered ON filtered.id = l.id
            ORDER BY filtered.sort";

        return $sql;
    }

    private static function sort_by_default_field_query($form_id, $sort_field, $sort_direction='DESC', $search='', $offset=0, $page_size=30, $star=null, $read=null, $is_numeric_sort = false, $start_date=null, $end_date=null){
        global $wpdb;

        if(!is_numeric($form_id) || !is_numeric($offset)|| !is_numeric($page_size)){
            return "";
        }

        $lead_detail_table_name = self::get_lead_details_table_name();
        $lead_table_name = self::get_lead_table_name();

        //$search = empty($search) ? "" : " AND value LIKE '%$search%'";
        $search_term = "%$search%";
        $search_filter = empty($search) ? "" : $wpdb->prepare(" AND value LIKE %s", $search_term);

        $star_filter = $star !== null ? $wpdb->prepare(" AND is_starred=%d ", $star) : "";
        $read_filter = $read !== null ? $wpdb->prepare(" AND is_read=%d ", $read) :  "";
        $start_date_filter = empty($start_date) ? "" : " AND datediff(date_created, '$start_date') >=0";
        $end_date_filter = empty($end_date) ? "" : " AND datediff(date_created, '$end_date') <=0";

        $sql = "
            SELECT filtered.sort, l.*, d.field_number, d.value
            FROM $lead_table_name l
            INNER JOIN $lead_detail_table_name d ON d.lead_id = l.id
            INNER JOIN
            (
                SELECT @rownum:=@rownum + 1 as sort, id
                FROM
                (
                    SELECT distinct l.id
                    FROM $lead_table_name l
                    INNER JOIN $lead_detail_table_name d ON d.lead_id = l.id
                    WHERE l.form_id=$form_id
                    $search_filter
                    $star_filter
                    $read_filter
                    $start_date_filter
                    $end_date_filter
                    ORDER BY $sort_field $sort_direction
                    LIMIT $offset,$page_size
                ) page
            ) filtered ON filtered.id = l.id
            ORDER BY filtered.sort";

        return $sql;
    }

    private static function build_lead_array($results, $use_long_values = false){

        $leads = array();
        $lead = array();
        if(is_array($results) && sizeof($results) > 0){
            $lead = array("id" => $results[0]->id, "form_id" => $results[0]->form_id, "date_created" => $results[0]->date_created, "is_starred" => intval($results[0]->is_starred), "is_read" => intval($results[0]->is_read), "ip" => $results[0]->ip, "source_url" => $results[0]->source_url, "post_id" => $results[0]->post_id, "currency" => $results[0]->currency, "payment_status" => $results[0]->payment_status, "payment_date" => $results[0]->payment_date, "transaction_id" => $results[0]->transaction_id, "payment_amount" => $results[0]->payment_amount, "is_fulfilled" => $results[0]->is_fulfilled, "created_by" => $results[0]->created_by, "transaction_type" => $results[0]->transaction_type);
        }

        $prev_lead_id=0;
        foreach($results as $result){
            if($prev_lead_id <> $result->id && $prev_lead_id > 0){
                array_push($leads, $lead);
                $lead = array("id" => $result->id, "form_id" => $result->form_id,     "date_created" => $result->date_created,     "is_starred" => intval($result->is_starred),     "is_read" => intval($result->is_read),     "ip" => $result->ip,     "source_url" => $result->source_url,     "post_id" => $result->post_id,     "currency" => $result->currency,     "payment_status" => $result->payment_status,     "payment_date" => $result->payment_date,     "transaction_id" => $result->transaction_id,     "payment_amount" => $result->payment_amount,     "is_fulfilled" => $result->is_fulfilled,     "created_by" => $result->created_by,     "transaction_type" => $result->transaction_type);
            }

            $field_value = $result->value;
            //using long values if specified
            if($use_long_values && strlen($field_value) >= GFORMS_MAX_FIELD_LENGTH){
                $long_text = RGFormsModel::get_field_value_long($lead["id"], $result->field_number);
                $field_value = !empty($long_text) ? $long_text : $field_value;
            }

            $lead[$result->field_number] = $field_value;
            $prev_lead_id = $result->id;
        }
        //adding last lead.
        if(sizeof($lead) > 0)
            array_push($leads, $lead);

        return $leads;

    }

    public static function save_key($key){
        $current_key = get_option("rg_gforms_key");
        if(empty($key)){
            delete_option("rg_gforms_key");
        }
        else if($current_key != $key){
            $key = trim($key);
            update_option("rg_gforms_key", md5($key));
        }
    }

    public static function get_lead_count($form_id, $search, $star=null, $read=null, $start_date=null, $end_date=null){
        global $wpdb;

        if(!is_numeric($form_id))
            return "";

        $detail_table_name = self::get_lead_details_table_name();
        $lead_table_name = self::get_lead_table_name();

        $star_filter = $star !== null ? $wpdb->prepare("AND is_starred=%d ", $star) : "";
        $read_filter = $read !== null ? $wpdb->prepare("AND is_read=%d ", $read) : "";
        $start_date_filter = empty($start_date) ? "" : " AND datediff(date_created, '$start_date') >=0";
        $end_date_filter = empty($end_date) ? "" : " AND datediff(date_created, '$end_date') <=0";

        $search_term = "%$search%";
        $search_filter = empty($search) ? "" : $wpdb->prepare("AND value LIKE %s", $search_term);

        $sql = "SELECT count(distinct l.id)
                FROM $lead_table_name l
                INNER JOIN $detail_table_name ld ON l.id = ld.lead_id
                WHERE l.form_id=$form_id
                AND ld.form_id=$form_id
                $star_filter
                $read_filter
                $start_date_filter
                $end_date_filter
                $search_filter";

        return $wpdb->get_var($sql);
    }

    public static function get_grid_columns($form_id, $input_label_only=false){
        $form = self::get_form_meta($form_id);
        $field_ids = self::get_grid_column_meta($form_id);

        if(!is_array($field_ids)){
            $field_ids = array();
            for($i=0, $count=sizeof($form["fields"]); $i<$count && $i<5; $i++){
                $field = $form["fields"][$i];

                if(RGForms::get("displayOnly",$field))
                    continue;

                if(is_array($field["inputs"])){
                    if($field["type"] == "name"){
                        $field_ids[] = $field["id"] . '.3'; //adding first name
                        $field_ids[] = $field["id"] . '.6'; //adding last name
                    }
                    else{
                        $field_ids[] = $field["inputs"][0]["id"]; //getting first input
                    }
                }
                else{
                    $field_ids[] = $field["id"];
                }
            }
        }

        $columns = array();
        foreach($field_ids as $field_id){
            $field = self::get_field($form, $field_id);
            switch($field_id){
                case "id" :
                    $columns[$field_id] = array("label" => "Entry Id", "type" => "id");
                break;
                case "ip" :
                    $columns[$field_id] = array("label" => "User IP", "type" => "ip");
                break;
                case "date_created" :
                    $columns[$field_id] = array("label" => "Entry Date", "type" => "date_created");
                break;
                case "source_url" :
                    $columns[$field_id] = array("label" => "Source Url", "type" => "source_url");
                break;
                case "payment_status" :
                    $columns[$field_id] = array("label" => "Payment Status", "type" => "payment_status");
                break;
                case "transaction_id" :
                    $columns[$field_id] = array("label" => "Transaction Id", "type" => "transaction_id");
                break;
                case "payment_date" :
                    $columns[$field_id] = array("label" => "Payment Date", "type" => "payment_date");
                break;
                case "payment_amount" :
                    $columns[$field_id] = array("label" => "Payment Amount", "type" => "payment_amount");
                break;
                case "created_by" :
                    $columns[$field_id] = array("label" => "User", "type" => "created_by");
                break;
                default :
                    if(!is_array(RGForms::get("inputs", $field)) || self::has_input($field, $field_id)){
                        $columns[strval($field_id)] = array("label" => self::get_label($field, $field_id, $input_label_only), "type" => RGForms::get("type", $field), "inputType" => RGForms::get("inputType", $field));
                    }
            }
        }
        return $columns;
    }

    public static function get_label($field, $input_id = 0, $input_only = false){
        $field_label = (IS_ADMIN || RG_CURRENT_PAGE == "select_columns.php") && !empty($field["adminLabel"]) ? $field["adminLabel"] : $field["label"];
        $input = self::get_input($field, $input_id);
        if(rgget("type", $field) == "checkbox" && $input != null)
            return $input["label"];
        else if($input != null)
            return $input_only ? $input["label"] : $field_label . ' (' . $input["label"] . ')';
        else
            return $field_label;
    }

    public static function get_input($field, $id){
        if(isset($field["inputs"])){
            foreach($field["inputs"] as $input)
            {
                if($input["id"] == $id)
                    return $input;
            }
        }
        return null;
    }

    function has_input($field, $input_id){
        if(!is_array($field["inputs"]))
            return false;
        else{
            foreach($field["inputs"] as $input)
            {
                if($input["id"] == $input_id)
                    return true;
            }
            return false;
        }
    }

    public function get_current_page_url() {
        $pageURL = 'http';
        if (RGForms::get("HTTPS",$_SERVER) == "on")
            $pageURL .= "s";
        $pageURL .= "://";

        //if (RGForms::get("SERVER_PORT",$_SERVER) != "80")
        //    $pageURL .= RGForms::get("HTTP_HOST", $_SERVER).":".RGForms::get("SERVER_PORT", $_SERVER). RGForms::get("REQUEST_URI", $_SERVER);
        //else

        $pageURL .= RGForms::get("HTTP_HOST", $_SERVER). RGForms::get("REQUEST_URI", $_SERVER);

        return $pageURL;
    }

    public static function get_submitted_fields($form_id){
        global $wpdb;
        $lead_detail_table_name = self::get_lead_details_table_name();
        $field_list = "";
        $fields = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT field_number FROM $lead_detail_table_name WHERE form_id=%d", $form_id));
        foreach($fields as $field)
            $field_list .= intval($field->field_number) . ',';

        if(!empty($field_list))
            $field_list = substr($field_list, 0, strlen($field_list) -1);

        return $field_list;
    }

    public static function get_field($form, $field_id){
        if(is_numeric($field_id))
            $field_id = intval($field_id); //removing floating part of field (i.e 1.3 -> 1) to return field by input id

        if(!is_array($form["fields"]))
            return null;

        foreach($form["fields"] as $field){
            if($field["id"] == $field_id)
                return $field;
        }
        return null;
    }

    public static function is_html5_enabled(){
        return get_option("rg_gforms_enable_html5");
    }
}
?>
