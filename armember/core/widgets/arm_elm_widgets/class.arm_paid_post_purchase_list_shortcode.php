<?php
namespace ElementorARMELEMENT\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Controls_Stack;

if(! defined('ABSPATH')) exit;

class arm_paid_post_list_shortcode extends Widget_Base
{
	public function get_categories() {
		return array( 'armember' );
	}

    public function get_name()
    {
        return 'arm-paid-post-membership-button-shortcode';
    }

    public function get_title()
    {
        return esc_html('ARMember Purchased Paid Post List','ARMemeber').'<style>
        .arm_element_icon{
			display: inline-block;
		    width: 28px;
		    height: 28px;
		    background-image: url('.MEMBERSHIP_IMAGES_URL.'/armember_icon.png);
		    background-repeat: no-repeat;
		    background-position: bottom;
			border-radius: 5px;
		}
        .arm_show_title .elementor-choices-label .elementor-screen-only{
			position: relative;
			top: 0;
		}
        </style>';
    }
    public function get_icon() {
		return 'arm_element_icon';
	}

    public function get_script_depends() {
		return [ 'elementor-arm-element' ];
	}
    protected function register_controls()
    {
        global $ARMember,$wp,$wpdb,$armainhelper,$arm_member_forms,$arm_members_directory,$arm_social_feature;
		$arm_form =array();
		
        /**START Fetch all shortcode controls from DB */
        /*END*/
        $this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'ARMember Membership Setup', 'ARMember' ),
			]
		);
        $this->add_control(
			'title',
			[
				'label' => esc_html__( 'Title', 'ARMember' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
                'default'=>'Paid Post Purchase List'
			]
		);
        $setups = $wpdb->get_results("SELECT `arm_setup_id`, `arm_setup_name` FROM `".$ARMember->tbl_arm_membership_setup."` WHERE arm_setup_type=1"); //phpcs:ignore --Reason $ARMember->tbl_arm_membership_setup is a table name
		$arm_setups =array();
        $arm_setups['Select Setup']='Select Setup';
		$default = $cnt = 0;
		if(!empty($setups)){
			foreach ($setups as $ms) {
				$setup_id = $ms->arm_setup_id;
				if($cnt == 0)
				{
					$default = $setup_id;
				}
				$cnt++;
				$setup_name = $ms->arm_setup_name." (" . esc_html__("ID:","ARMember")." ".$setup_id.")";
				$arm_setups[$setup_id]=$setup_name;
			} 
		}
		$this->add_control(
			'arm_shortcode_select',
			[
				'label' => esc_html__( 'Select Setup', 'ARMember' ),
				'type' => Controls_Manager::SELECT,
				'options' => $arm_setups,
                'default'=>$default,
				'label_block' => true,
			]
		);
        $this->add_control(
			'arm_no_title',
			[
				'label' => esc_html__('Hide Setup Title','ARMember'),
				'type' => Controls_Manager::CHOOSE,
				'default' =>'false',
				'options' => [
					'true' => [
						'title' => esc_html__( 'Yes', 'ARMember' ),
					],
					'false' => [
						'title' => esc_html__( 'No', 'ARMember' ),
					],
				],
				'classes'=>'arm_show_title',
				
			]
		);
				
        $this->end_controls_section();
        $this->start_controls_section(
			'section_content_cm',
			[
				'label' => esc_html__( 'Purchased Paid Post List', 'ARMember' ),
			]
		);
        $this->add_control(
            'arm_show_current_membership_fields',
            [
                'label' => esc_html__( 'Purchased Paid Post List', 'ARMember' ),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => [
                    'current_membership_no'=> esc_html__('No.','ARMember'),
                    'current_membership_is'=> esc_html__('Post Name','ARMember'),
                    'current_membership_recurring_profile'=>esc_html__('Post Type','ARMember'),
                    'current_membership_started_on' => esc_html__('Start On','ARMember'),
                    'current_membership_expired_on'=> esc_html__('Expires On','ARMember'),
                    'current_membership_next_billing_date' => esc_html__('Cycle Date','ARMember'),
                    'action_button' => esc_html__('Action','ARMember')
                ],
                'default'=>['current_membership_no','current_membership_is','current_membership_recurring_profile','current_membership_started_on' ,'current_membership_expired_on','current_membership_next_billing_date','action_button'],
                'classes'=>'arm_show_profiles',	
                'label_block' => true,
            ]
        );
        $this->add_control(
			'arm_current_membership_no',
			[
				'label' => esc_html__( 'No', 'ARMember' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
                'default'=>'No',
                'condition' => ['arm_show_current_membership_fields'=> 'current_membership_no']

			]
		);

        $this->add_control(
			'arm_current_membership_is',
			[
				'label' => esc_html__('Paid Post', 'ARMember' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
                'default'=>esc_html__('Paid Post', 'ARMember' ),
                'condition' => ['arm_show_current_membership_fields'=> 'current_membership_is']
			]
		);

        $this->add_control(
			'arm_current_membership_recurring_profile',
			[
				'label' => esc_html__( 'Post Type', 'ARMember' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
                'default'=>esc_html__( 'Post Type', 'ARMember' ),
                'condition' => ['arm_show_current_membership_fields'=> 'current_membership_recurring_profile']
			]
		);

        $this->add_control(
			'arm_current_membership_started_on',
			[
				'label' => esc_html__('Start On', 'ARMember' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
                'default'=>esc_html__('Start On', 'ARMember' ),
                'condition' => ['arm_show_current_membership_fields'=> 'current_membership_started_on']
			]
		);

        $this->add_control(
			'arm_current_membership_expired_on',
			[
				'label' => esc_html__( 'Expires On', 'ARMember' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
                'default'=>esc_html__('Expires On', 'ARMember' ),
                'condition' => ['arm_show_current_membership_fields'=> 'current_membership_expired_on']
			]
		);

        $this->add_control(
			'arm_current_membership_next_billing_date',
			[
				'label' => esc_html__( 'Cycle Date', 'ARMember' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
                'default'=>esc_html__( 'Cycle Date', 'ARMember' ),
                'condition' => ['arm_show_current_membership_fields'=> 'current_membership_next_billing_date']
			]
		);

        $this->add_control(
			'arm_action_button',
			[
				'label' => esc_html__( 'Action', 'ARMember' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
                'default'=>esc_html__( 'Action', 'ARMember' ),
                'condition' => ['arm_show_current_membership_fields'=> 'action_button']
			]
		);
        

        $this->end_controls_section();
        $this->start_controls_section(
			'section_content_cm_buttons',
			[
				'label' => esc_html__( 'Paid Post Membership Buttons options', 'ARMember' ),
			]
		);

        $this->add_control(
			'arm_renew_btn',
			[
				'label' => esc_html__('Display Renew Subscription Button','ARMember'),
				'type' => Controls_Manager::CHOOSE,
				'default' =>'false',
				'options' => [
					'true' => [
						'title' => esc_html__( 'Yes', 'ARMember' ),
					],
					'false' => [
						'title' => esc_html__( 'No', 'ARMember' ),
					],
				],
				'classes'=>'arm_show_title',
			]
		);

        $this->add_control(
			'arm_renew_txt',
			[
				'label' => esc_html__( 'Renew Text', 'ARMember' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
                'default'=>'Renew',
                'condition'=>['arm_renew_btn'=>'true'],
			]
		);
        $this->add_control(
			'arm_make_payement_txt',
			[
				'label' => esc_html__( 'Make Payment Text', 'ARMember' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
                'default'=>'Make Payment',
                 'condition'=>['arm_renew_btn'=>'true'],
			]
		);

        $this->add_control(
			'arm_button_css',
			[
				'label' => esc_html__( 'Button CSS', 'ARMember' ),
				'type' => Controls_Manager::TEXTAREA,
				'label_block' => true,
                'default'=>'',
                 'condition'=>['arm_renew_btn'=>'true'],
			]
		);
        $this->add_control(
			'arm_button_hover_css',
			[
				'label' => esc_html__( 'Button Hover CSS', 'ARMember' ),
				'type' => Controls_Manager::TEXTAREA,
				'label_block' => true,
                'default'=>'',
                 'condition'=>['arm_renew_btn'=>'true'],
			]
		);



        $this->add_control(
			'arm_cancel_btn',
			[
				'label' => esc_html__('Display Cancel Subscription Button','ARMember'),
				'type' => Controls_Manager::CHOOSE,
				'default' =>'false',
				'options' => [
					'true' => [
						'title' => esc_html__( 'Yes', 'ARMember' ),
					],
					'false' => [
						'title' => esc_html__( 'No', 'ARMember' ),
					],
				],
				'classes'=>'arm_show_title',
				
			]
		);

        $this->add_control(
			'arm_cancel_txt',
			[
				'label' => esc_html__( 'Button Text', 'ARMember' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
                'default'=>'Cancel',
                 'condition'=>['arm_cancel_btn'=>'true'],
			]
		);
        

        $this->add_control(
			'arm_cancel_button_css',
			[
				'label' => esc_html__( 'Button CSS', 'ARMember' ),
				'type' => Controls_Manager::TEXTAREA,
				'label_block' => true,
                'default'=>'',
                 'condition'=>['arm_cancel_btn'=>'true'],
			]
		);
        $this->add_control(
			'arm_cancel_button_hover_css',
			[
				'label' => esc_html__( 'Button Hover CSS', 'ARMember' ),
				'type' => Controls_Manager::TEXTAREA,
				'label_block' => true,
                'default'=>'',
                 'condition'=>['arm_cancel_btn'=>'true'],
			]
		);
        $this->add_control(
			'arm_cancelled_message_txt',
			[
				'label' => esc_html__( 'Subscription Cancelled Message', 'ARMember' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
                'default'=>'Your subscription has been cancelled.',
                 'condition'=>['arm_cancel_btn'=>'true'],
			]
		);

        $this->add_control(
			'arm_update_card_btn',
			[
				'label' => esc_html__('Display Update Card Subscription Button?','ARMember'),
				'type' => Controls_Manager::CHOOSE,
				'default' =>'false',
				'options' => [
					'true' => [
						'title' => esc_html__( 'Yes', 'ARMember' ),
					],
					'false' => [
						'title' => esc_html__( 'No', 'ARMember' ),
					],
				],
				'classes'=>'arm_show_title',
				
			]
		);

        $this->add_control(
			'arm_update_txt',
			[
				'label' => esc_html__( 'Update Card Text', 'ARMember' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
                'default'=>'Cancel',
                 'condition'=>['arm_update_card_btn'=>'true'],
			]
		);
        

        $this->add_control(
			'arm_update_button_css',
			[
				'label' => esc_html__( 'Button CSS', 'ARMember' ),
				'type' => Controls_Manager::TEXTAREA,
				'label_block' => true,
                'default'=>'',
                 'condition'=>['arm_update_card_btn'=>'true'],
			]
		);
        $this->add_control(
			'arm_update_button_hover_css',
			[
				'label' => esc_html__( 'Button Hover CSS', 'ARMember' ),
				'type' => Controls_Manager::TEXTAREA,
				'label_block' => true,
                'default'=>'',
                 'condition'=>['arm_update_card_btn'=>'true'],
			]
		);
        $this->end_controls_section();
        $this->start_controls_section(
			'section_content_cm_others',
			[
				'label' => esc_html__( 'Paid Post Membership Other options', 'ARMember' ),
			]
		);

        $this->add_control(
			'arm_trial_active',
			[
				'label' => esc_html__( 'Trial Active Label', 'ARMember' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
                'default'=>'trial active',
			]
		);

        $this->add_control(
			'arm_records_per_page',
			[
				'label' => esc_html__( 'Records Per Page', 'ARMember' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
                'default'=>'5',
			]
		);
        $this->add_control(
			'arm_records_message',
			[
				'label' => esc_html__( 'No Records Message', 'ARMember' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
                'default'=>'There is no membership found.',
			]
		);

        $this->end_controls_section();

	}
				
	protected function render()
	{
		global $arm_social_feature;
		$settings = $this->get_settings_for_display();
        $str='';
		$str_label = '';
        foreach($settings['arm_show_current_membership_fields'] as $sk)
        {
            $str .= $settings['arm_'.$sk].',';
			$str_label .= $sk.',';
        }
		echo '<div class="arm_select">';
			$arm_shortcode='';
            echo do_shortcode('[arm_purchased_paid_post_list title="'.$settings['title'].'" setup_id="'.$settings['arm_shortcode_select'].'" display_renew_button="'.$settings['arm_renew_btn'].'" renew_text="'.$settings['arm_renew_txt'].'" make_payment_text="'.$settings['arm_make_payement_txt'].'" renew_css="'.$settings['arm_button_css'].'" renew_hover_css="'.$settings['arm_button_hover_css'].'" display_cancel_button="'.$settings['arm_cancel_btn'].'" cancel_text="'.$settings['arm_cancel_txt'].'" cancel_css="'.$settings['arm_cancel_button_css'].'" cancel_hover_css="'.$settings['arm_cancel_button_hover_css'].'" cancel_message="'.$settings['arm_cancelled_message_txt'].'" display_update_card_button="'.$settings['arm_update_card_btn'].'" update_card_text="'.$settings['arm_update_txt'].'" update_card_css="'.$settings['arm_update_button_css'].'" update_card_hover_css="'.$settings['arm_update_button_hover_css'].'"  per_page="'.$settings['arm_records_per_page'].'" message_no_record="'.$settings['arm_records_message'].'"  membership_label="'.$str_label.'" membership_value="'.$str.'"]');
		echo '</div>';
	}
}
