<?php 
if (!class_exists('ARM_user_private_content_feature')) {

    class ARM_user_private_content_feature {
    	var $private_content_settings;
        var $isPrivateContentFeature;

        function __construct() {
        	global $wpdb, $ARMember, $arm_slugs;
        	$is_private_content_feature = get_option('arm_is_user_private_content_feature', 0);
        	$this->isPrivateContentFeature = ($is_private_content_feature == '1') ? true : false;
        	
            if($this->isPrivateContentFeature==true)
            {
	            add_action('arm_save_private_content', array($this, 'arm_save_private_content_func'));
	            add_action('arm_save_default_private_content', array($this, 'arm_save_default_private_content_func'));

	            add_action('wp_ajax_arm_delete_private_content', array($this, 'arm_delete_private_content'), 10);
	            add_action('wp_ajax_arm_changes_status_private_content', array($this, 'arm_changes_status_private_content'), 10);

				add_action('wp_ajax_get_member_list', array($this, 'get_member_list_func'), 10);
	            
	            
	            add_shortcode('arm_user_private_content', array($this, 'arm_private_content_shortcode_func'));

                add_action( 'add_others_section_option_tinymce',array($this,'arm_private_content_shortcode_option'),10,2);

                add_action('arm_shortcode_add_other_tab_buttons',array($this,'arm_private_content_shortcode_add_tab_buttons'));

                add_action( 'wp_ajax_get_private_content_data', array($this, 'arm_retrieve_private_content_data'));
	        }
        }


        function arm_private_content_shortcode_add_tab_buttons($tab_buttons =array()){
            $tab_buttons =' <div class="arm_group_footer arm_shortcode_other_opts arm_shortcode_other_opts_arm_private_content arm_hidden">
                                    <div class="popup_content_btn_wrapper">
                                            <button type="button" class="arm_shortcode_insert_btn arm_insrt_btn" id="arm_shortcode_other_opts_arm_private_content" data-code="arm_user_private_content">'.esc_html__('Add Shortcode', 'ARMember').'</button>
                                            <a class="arm_cancel_btn popup_close_btn" href="javascript:void(0)">'.esc_html__('Cancel', 'ARMember').'</a>
                                    </div>
                            </div>';
            echo $tab_buttons; //phpcs:ignore
        }

        function arm_private_content_shortcode_option($arm_data =array()){
            if($this->isPrivateContentFeature==true) {
                $arm_data = '<li data-label="'.esc_html__('User Private Content', 'ARMember').'" data-value="arm_private_content">
                    '.esc_html__('User Private Content',  'ARMember').'
                 </li>';    
            }
            
            echo $arm_data; //phpcs:ignore
        }

        function get_member_list_func(){
            global $ARMember, $arm_capabilities_global;
        	if(isset($_REQUEST['action']) && $_REQUEST['action']=='get_member_list') {
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_private_content'],0,1); //phpcs:ignore --Reason:Verifying nonce
        		$text = isset($_REQUEST['txt']) ? sanitize_text_field($_REQUEST['txt']) : '';
                $text = !empty($text) ? '%'.$text.'%' : '';
                global $wpdb;

                $user_list = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->users." WHERE ID NOT IN ((SELECT DISTINCT user_id FROM ".$wpdb->usermeta." WHERE meta_key LIKE %s AND meta_value LIKE %s OR meta_key = %s and meta_value != %s GROUP BY user_id )) AND (user_login LIKE %s OR user_nicename LIKE %s OR user_email LIKE %s) LIMIT 10",'%capabilities%','%administrator%','arm_member_private_content','', $text, $text, $text));//phpcs:ignore --Reason $wpdb->users is a table name


        		$user_list_html = "";
        		$drData = array();
        		if(!empty($user_list)) {
        			foreach ( $user_list as $user ) {
				        $author_info = get_userdata( $user->ID );
				        $user_list_html .= '<li data-id="'.esc_attr($author_info->ID).'">' . $author_info->user_login . '</li>';
				        $drData[] = array(
                                    'id' => $user->ID,
                                    'value' => $author_info->user_login." (".$author_info->user_email.")",
                                    'label' => $author_info->user_login." (".$author_info->user_email.")",
                                );
				    }
        		}
        		
        		$response = array('status' => 'success', 'data' => $drData);
        		echo json_encode($response);
        		die;
        	}
        }

        function arm_private_content_shortcode_func($atts, $content, $tag) {
        	
        	$user_private_content = "";
        	if($this->isPrivateContentFeature==true && !current_user_can("administrator")) {
        		if(is_user_logged_in()) {
	        		$user = wp_get_current_user();
		        	$user_id = $user->ID;
		        	$private_content = get_user_meta($user_id, 'arm_member_private_content', true);
		        	
		        	if($private_content == "") {
		        		$user_private_content = stripslashes_deep(get_option("arm_member_default_private_content"));
		        	} else {
		        		$private_content = json_decode($private_content);
		        		if($private_content->enable_private_content) {
		        			$user_private_content = $private_content->private_content;

                            $user_private_content = stripslashes_deep(stripslashes_deep($private_content->private_content));
		        		}
		        	}	
	        	}

                return do_shortcode($user_private_content);    	        	
        	}
        	
        }

        function arm_save_default_private_content_func($posted_data = array()) {
        	global $wp, $wpdb, $arm_slugs, $ARMember, $arm_global_settings,$ARMemberAllowedHTMLTagsArray;
        	$redirect_to = admin_url('admin.php?page=' . $arm_slugs->private_content);
        	if (isset($posted_data) && !empty($posted_data) && $posted_data['page'] == 'arm_manage_private_content') { 
        		$private_content = isset($posted_data['arm_default_private_content']) ?  wp_kses($posted_data['arm_default_private_content'],$ARMemberAllowedHTMLTagsArray) : '';
        		update_option('arm_member_default_private_content', $private_content);
        		$ARMember->arm_set_message('success', esc_html__('Default private content has been added successfully.', 'ARMember'));
        		wp_redirect($redirect_to);
        		exit;
        	}
        }

        function arm_save_private_content_func($posted_data = array()) {
        	global $wp, $wpdb, $arm_slugs, $ARMember, $arm_global_settings,$ARMemberAllowedHTMLTagsArray;
        	$redirect_to = admin_url('admin.php?page=' . $arm_slugs->private_content);

        	if (isset($posted_data) && !empty($posted_data) && in_array($posted_data['action'], array('add_private_content', 'edit_private_content'))) { 
        		$action = !empty($posted_data['action']) ? $posted_data['action'] : '';

        		$user_ids = !empty($posted_data['arm_member_input_hidden']) ? $posted_data['arm_member_input_hidden'] : 0;
        		$private_content = isset($posted_data['arm_private_content']) ? wp_kses($posted_data['arm_private_content'],$ARMemberAllowedHTMLTagsArray) : '';
        		$enable_private_content = !empty($posted_data['enable_private_content']) ? addslashes($posted_data['enable_private_content']) : 0;
        		$arm_data = array();
        		if($action!='' && $action=='add_private_content') {
        			if($user_ids != 0) {
        				
        				foreach ($user_ids as $key => $user_id) {
        					$arm_data['private_content'] = $private_content;
		        			$arm_data['enable_private_content'] = $enable_private_content;

                            $arm_user_data_content = addslashes(json_encode($arm_data));
		        			update_user_meta($user_id, 'arm_member_private_content', $arm_user_data_content);
        				}	
                        $ARMember->arm_set_message('success', esc_html__('Private Content has been added successfully.', 'ARMember'));
                        
                        wp_redirect($redirect_to);
	        		}
	        		
	        		exit;	
        		}
        		else if($action!='' && $action=='edit_private_content') {
        			if($user_ids != 0) {
        				
        				foreach ($user_ids as $key => $user_id) {
        					$arm_data['private_content'] = $private_content;
		        			$arm_data['enable_private_content'] = $enable_private_content;

                            $arm_user_data_content = addslashes(json_encode($arm_data));
                            update_user_meta($user_id, 'arm_member_private_content', $arm_user_data_content);
        				}
        				
        			}
        			$ARMember->arm_set_message('success', esc_html__('Private Content has been updated successfully.', 'ARMember'));
        			$redirect_to = $arm_global_settings->add_query_arg("action", "edit_private_content", $redirect_to);
                    $redirect_to = $arm_global_settings->add_query_arg("member_id", $user_id, $redirect_to);
                    wp_redirect($redirect_to);
                    exit;
        		}
        	}
        	return;
        }

        function arm_changes_status_private_content() {
        	global $arm_global_settings, $wpdb, $ARMember, $arm_capabilities_global;
        	$id = intval($_POST['member_id']);//phpcs:ignore
        	if($id != '' || $id!=0) {
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_private_content'],0,1);//phpcs:ignore --Reason:Verifying nonce
        		$private_content = get_user_meta($id, 'arm_member_private_content', true);
        		$private_content = stripslashes_deep(json_decode($private_content));
        		if($private_content->enable_private_content==0) {
        			$private_content->enable_private_content = 1;
        		} else {
        			$private_content->enable_private_content = 0;
        		}

				$message = "";
        		if($private_content->enable_private_content==1) {
        			$message = esc_html__('Private Content has been activated successfully.', 'ARMember');	
        		} else {
        			$message = esc_html__('Private Content has been deactivated successfully.', 'ARMember');	
        		}
        		update_user_meta($id, 'arm_member_private_content', addslashes(json_encode($private_content)));
        		
        		$return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
        	} else {
        		$errors[] = esc_html__('Invalid action.', 'ARMember');
        	}
        	$return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
            echo json_encode($return_array);
            exit;
        }

        function arm_delete_private_content () {
        	global $arm_global_settings, $wpdb, $ARMember, $arm_capabilities_global;
        	$id = intval($_POST['member_id']);//phpcs:ignore

            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_private_content'],0,1);//phpcs:ignore --Reason:Verifying nonce
        	
        	if($id != '' || $id!=0) {
        		delete_user_meta($id, 'arm_member_private_content');
        		$message = esc_html__('Private Content has been deleted successfully.', 'ARMember');
        		$return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
        	} else {
        		$errors[] = esc_html__('Invalid action.', 'ARMember');
        	}

        	$return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
            echo json_encode($return_array);
            exit;
        }

        function arm_retrieve_private_content_data(){

            global $wpdb, $ARMember, $arm_global_settings, $arm_slugs;
            
            $user_table = $wpdb->users;
            $usermeta_table = $wpdb->usermeta;

            $offset = isset( $_POST['iDisplayStart'] ) ? intval($_POST['iDisplayStart']) : 0;//phpcs:ignore
            $limit = isset( $_POST['iDisplayLength'] ) ? intval($_POST['iDisplayLength']) : 10;//phpcs:ignore

            $search_term = ( isset( $_POST['sSearch'] ) && '' != $_POST['sSearch'] ) ? true : false;//phpcs:ignore

            $search_query = '';
            if( $search_term ){
                $search_query = $wpdb->prepare("AND (u.user_login LIKE %s )",'%'.sanitize_text_field($_POST['sSearch']).'%');//phpcs:ignore
            }

            $sortOrder = isset( $_POST['sSortDir_0'] ) ? sanitize_text_field($_POST['sSortDir_0']) : 'DESC';//phpcs:ignore


            $orderBy = 'ORDER BY u.user_login ' . $sortOrder;
            if( isset( $_POST['iSortCol_0'] ) && '' != $_POST['iSortCol_0'] ){//phpcs:ignore
                if( $_POST['iSortCol_0'] == 0 ){//phpcs:ignore
                    $orderBy = 'ORDER BY u.ID ' . $sortOrder;
                }
            }

            $user_query = $wpdb->prepare("SELECT u.* FROM {$user_table} u INNER JOIN {$usermeta_table} um ON ( u.ID = um.user_id ) WHERE 1=1 AND ( um.meta_key = %s) {$search_query} {$orderBy}  LIMIT {$offset}, {$limit}",'arm_member_private_content'); //phpcs:ignore --Reason user_table and usermeta_table is a tables name

            $get_all_armembers = $wpdb->get_results( $user_query ); //phpcs:ignore --Reason user_query is a query
            

            $totalUsers = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) as total FROM `{$user_table}` u LEFT JOIN `{$usermeta_table}` ut ON u.id = ut.user_id WHERE ut.meta_key = %s", 'arm_member_private_content' ) ); //phpcs:ignore --Reason user_table and $usermeta_table are tables name

            $grid_data = array();
            $ai = 0;
            if( !empty( $get_all_armembers )){
                foreach ($get_all_armembers as $key => $member) {
                    if( !isset($grid_data[$ai]) || !is_array( $grid_data[$ai] ) ){
                        $grid_data[$ai] = array();
                    }

                    $grid_data[$ai][] =  $member->ID;
                    $grid_data[$ai][] = "<a class='arm_openpreview_popup' href='javascript:void(0)' data-id='".$member->ID."'>".$member->user_login."</a>";

                    $private_content = get_user_meta($member->ID, 'arm_member_private_content', true);

                    $checked_content = "";
                    if($private_content!='') {
                        $private_content = stripslashes_deep(json_decode($private_content));
                        if(isset($private_content->enable_private_content) && $private_content->enable_private_content==1) {
                            $checked_content = "checked=\'checked\'"; 
                        } 
                    } else {
                        $checked_content = "checked=\'checked\'";
                    }

                    $switch_div = '<div class="armswitch">
                                        <input type="checkbox" class="armswitch_input arm_private_content_status_action arm_private_content_status_input" id="'."arm_private_content_status_input_".esc_attr($member->ID).'" value="1" data-item_id="'.esc_attr($member->ID).'" '.$checked_content.'>
                                        <label class="armswitch_label" for="'."arm_private_content_status_input_".esc_attr($member->ID).'"></label>
                                        <span class="arm_status_loader_img"></span>
                                    </div>';

                    $grid_data[$ai][] = $switch_div;

                    $edit_link = admin_url('admin.php?page='.$arm_slugs->private_content.'&action=edit_private_content&member_id='.$member->ID);
                    $gridAction = "<div class='arm_grid_action_btn_container'>";
                    if (current_user_can('arm_manage_private_content')) {
                        $gridAction .= "<a href='".esc_url($edit_link)."'><img src='".MEMBERSHIP_IMAGES_URL."/grid_edit.png' onmouseover=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_edit_hover.png';\" class='armhelptip' title='".esc_attr__('Edit Private Content','ARMember')."' onmouseout=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_edit.png';\" /></a>";
                        $gridAction .= "<a href='javascript:void(0)' onclick='showConfirmBoxCallback(".$member->ID.");'><img src='".MEMBERSHIP_IMAGES_URL."/grid_delete.png' class='armhelptip' title='".esc_attr__('Delete','ARMember')."' onmouseover=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_delete_hover.png';\" onmouseout=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_delete.png';\" /></a>";
                        $gridAction .= $arm_global_settings->arm_get_confirm_box($member->ID, esc_html__("Are you sure you want to delete the Private Content form this user?", 'ARMember'), 'arm_private_content_delete_btn');
                    }
                    $gridAction .= "</div>";

                    $grid_data[$ai][] = '<div class="arm_grid_action_wrapper">'.$gridAction.'</div>';

                    $ai++;
                }
            }

            $sEcho = isset($_REQUEST['sEcho']) ? intval($_REQUEST['sEcho']) : intval(10);
            $after_filter = $totalUsers;
            if( $search_term ){
                $after_filter = $ai;
            }
            $response = array(
                'sColumns' => implode(',',array('userID','Username','Active','')),
                'sEcho' => $sEcho,
                'iTotalRecords' => $totalUsers,
                'iTotalDisplayRecords' => $after_filter,
                'aaData' => $grid_data
            );

            echo json_encode( $response );
            die;

        }

    }

}

global $arm_private_content_feature;
$arm_private_content_feature = new ARM_user_private_content_feature();