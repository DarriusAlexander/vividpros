<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Module Name: Contact
 */
class TB_Contact_Module extends Themify_Builder_Component_Module {
	public function __construct() {
		parent::__construct(array(
			'name' => __('Contact', 'builder-contact'),
			'slug' => 'contact',
			'category' => array('addon')
		));
	}

	public function get_assets() {
		$instance = Builder_Contact::get_instance();
		return array(
			'selector' => '.module-contact',
			'css' => themify_enque($instance->url . 'assets/style.css'),
			'js' => themify_enque($instance->url . 'assets/scripts.js'),
			'external' => Themify_Builder_Model::localize_js( 'BuilderContact', array(
				'admin_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'tb_contact' ),
			) ),
			'ver' => $instance->version
		);
	}

	public function get_options() {
        $url = Builder_Contact::get_instance()->url;
		return array(
			array(
			    'id' => 'mod_title_contact',
			    'type' => 'title'
			),
			array(
				'id' => 'layout_contact',
				'type' => 'layout',
				'label' => __('Layout', 'builder-contact'),
				'options' => array(
					array('img' => $url . 'assets/style1.svg', 'value' => 'style1', 'label' => __('Style 1', 'builder-contact')),
					array('img' => $url . 'assets/style2.svg', 'value' => 'style2', 'label' => __('Style 2', 'builder-contact')),
					array('img' => $url . 'assets/style3.svg', 'value' => 'style3', 'label' => __('Style 3', 'builder-contact')),
					array('img' => $url . 'assets/style4.svg', 'value' => 'animated-label', 'label' => __('Animated Label', 'builder-contact'))
				)
			),
			array(
				'id' => 'mail_contact',
				'type' => 'text',
				'label' => __('Send to', 'builder-contact'),
				'class' => 'large',
				'help' => __( 'To send to multiple recipients, comma-separate the mail addresses.', 'builder-contact' ),
				'control' => false
			),
			array(
				'type' => 'multi',
				'label' => '',
				'options' => array(
						array(
							'id' => 'send_to_admins',
							'type' => 'checkbox',
							'options' => array(
								array( 'name' => 'true', 'value' =>  __("Send to", 'builder-contact'))
							),
							'control' => false,
							'binding' => array(
								'checked' => array( 'hide' => array( 'mail_contact' ) ),
								'not_checked' => array( 'show' => array( 'mail_contact' ) ),
							)
						),
						array(
							'id' => 'user_role',
							'type' => 'select',
							'options' => array(
								'admin'=>__("Admin's email", 'themify'),
								'author'=>__("Author's email", 'themify')
							),
							'control' => false
						),
				)
            ),
			array(
				'id' => 'specify_from_address',
				'label' => __('Specify From Address', 'builder-contact'),
				'help' => __( 'Use a custom "from" address in the mail header instead of sender&#39;s address', 'builder-contact' ),
				'type' => 'toggle_switch',
				'options' => array(
				    'on' => array('name'=>'enable','value' =>'en'),
				    'off' => array('name'=>'', 'value' =>'dis')
				),
				'binding' => array(
					'checked' => array( 'show' => array( 'specify_email_address' ) ),
					'not_checked' => array( 'hide' => array( 'specify_email_address' ) ),
				),
				'control' => false
			),
			array(
				'id' => 'specify_email_address',
				'type' => 'text',
				'label' => __('From Address', 'builder-contact'),
				'class' => 'large',
				'render_callback' => array(
					'binding' => false
				)
			),
			array(
				'id' => 'bcc_mail',
				'label' => __('Send to BCC', 'builder-contact'),
				'help' => __( 'Send mail as BCC (blind carbon copy), recipients do not see each other email address.', 'builder-contact' ),
				'type' => 'toggle_switch',
				'options' => array(
				    'on' => array('name'=>'enable','value' =>'en'),
				    'off' => array('name'=>'', 'value' =>'dis')
				),
				'binding' => array(
					'checked' => array( 'show' => array( 'bcc_mail_contact' ) ),
					'not_checked' => array( 'hide' => array( 'bcc_mail_contact' ) ),
				),
				'control' => false
			),
			array(
				'id' => 'bcc_mail_contact',
				'type' => 'text',
				'label' => __('BCC Addresses', 'builder-contact'),
				'help' => __( 'To send to multiple recipients, comma-separate the mail addresses.', 'builder-contact' ),
				'class' => 'large',
				'render_callback' => array(
					'binding' => false
				)
			),
			array(
				'id' => 'post_type',
				'label' => __('Contact Posts', 'builder-contact'),
				'type' => 'toggle_switch',
				'options' => array(
				    'on' => array('name'=>'enable','value' =>'en'),
				    'off' => array('name'=>'', 'value' =>'dis')
				),
				'binding' => array(
					'checked' => array( 'show' => array( 'post_author' ) ),
					'not_checked' => array( 'hide' => array( 'post_author' ) ),
				),
				'help'=>__('Enable this will create a copy of message as contact post in admin area.','builder-contact'),
				'control' => false
			),
			array(
				'id' => 'post_author',
				'type' => 'checkbox',
				'label' => '',
				'wrap_class' => '_tf-hide',
				'options' => array(
					array( 'name' => 'add', 'value' => __("Assign sender's email address as post author", 'builder-contact') )
				),
				'control' => false
			),
			array(
				'id' => 'gdpr',
				'label' => __('GDPR Checkbox', 'builder-contact'),
				'type' => 'toggle_switch',
				'options' => array(
					'on' => array('name'=>'accept','value' =>'en'),
					'off' => array('name'=>'', 'value' =>'dis')
				),
				'binding' => array(
					'checked' => array( 'show' => array( 'gdpr_label' ) ),
					'not_checked' => array( 'hide' => array( 'gdpr_label' ) ),
				)
			),
			array(
				'id' => 'gdpr_label',
				'type' => 'textarea',
				'class' => 'fullwidth',
				'label' => __( 'GDPR Message', 'builder-contact' )
			),
			array(
				'id' => 'success_url',
				'type' => 'url',
				'label' => __( 'Success URL', 'builder-contact' ),
				'class' => 'fullwidth',
				'help' =>  __( 'Redirect to this URL when the form is successfully sent.', 'builder-contact' ),
				'control' => false
			),
			array(
				'id' => 'success_message_text',
				'type' => 'text',
				'label' => __( 'Success Message', 'builder-contact' ),
				'class' => 'fullwidth',
				'control' => false
			),
			array(
				'id' => 'auto_respond',
				'label' => __('Auto Responder', 'builder-contact' ),
				'type' => 'toggle_switch',
				'options' => array(
				    'on' => array('name'=>'enable','value' =>'en'),
				    'off' => array('name'=>'', 'value' =>'dis')
				),
				'help'=>__('Send an auto reply message when user submits the contact form.','builder-contact'),
				'binding' => array(
				    'checked' => array( 'show' => array( 'auto_respond_message', 'auto_respond_subject' ) ),
				    'not_checked' => array( 'hide' => array( 'auto_respond_message', 'auto_respond_subject' ) ),
				),
				'control' => false
			),
			array(
				'id' => 'auto_respond_subject',
				'type' => 'text',
				'label' => __( 'Auto Respond Subject', 'builder-contact' ),
				'class' => 'fullwidth',
				'control' => false
			),
			array(
				'id' => 'auto_respond_message',
				'type' => 'textarea',
				'label' => __( 'Auto Respond Message', 'builder-contact' ),
				'class' => 'fullwidth',
				'control' => false
			),
			array(
				'id' => 'default_subject',
				'type' => 'text',
				'label' => __( 'Default Subject', 'builder-contact' ),
				'class' => 'fullwidth',
				'help' =>  __( 'This will be used as the subject of the mail if the Subject field is not shown on the contact form.', 'builder-contact' ),
                                'control' => false
			),
			array(
				'id' => 'contact_sent_from',
				'type' => 'checkbox',
				'label' => __( 'Contact Sent From', 'builder-contact' ),
				'options' => array(
					array( 'name' => 'enable', 'value' => __( 'Include contact sent from URL in message', 'builder-contact' ) )
				),
				'default'=>'enable',
				'control' => false
			),
			array(
				'id' => 'include_name_mail',
				'type' => 'checkbox',
				'label' => __( 'Name & Email', 'builder-contact' ),
				'options' => array(
					array( 'name' => 'enable', 'value' => __( 'Include name and email address in message', 'builder-contact' ) )
				)
			),
			array(
				'id' => 'fields_contact',
				'type' => 'contact_fields',
				'options'=>array(
				    'head'=>array(
						'f'=>__( 'Field', 'builder-contact' ),
						'l'=>__( 'Label', 'builder-contact' ),
						'p'=>__( 'Placeholder', 'builder-contact' ),
						'sh'=>__( 'Show', 'builder-contact' )
				    ),
				    'body'=>array(
						'name'=>__( 'Name', 'builder-contact' ),
						'email'=>__( 'Email', 'builder-contact' ),
						'subject'=>__( 'Subject', 'builder-contact' ),
						'message'=>__( 'Message', 'builder-contact' )
				    ),
				    'foot'=>array(
						'captcha'=>__( 'Captcha', 'builder-contact' ),
						'sendcopy'=>__( 'Send Copy', 'builder-contact' ),
						'optin' => __( 'Newsletter Subscription', 'builder-contact' ),
						'send'=>__( 'Send Button', 'builder-contact' ),
						'align'=>array(
							'id'=>'field_send_align',
							'label'=>__( 'Button Alignment', 'builder-contact' ),
							'options'=>array(
							'left'=>__( 'Left', 'builder-contact' ),
							'right'=>__( 'Right', 'builder-contact' ),
							'center'=>__( 'Center', 'builder-contact' )
							)
						)
					
				    )
				),
				'new_row'=>__( 'Add Field', 'builder-contact' )
			),
			array(
			    'id'=>'field_extra',
			    'type'=>'hidden'
			),
			array(
			    'id'=>'field_order',
			    'type'=>'hidden'
			),
			array(
			    'id' => 'css_class_contact',
			    'type' => 'custom_css'
			),
			array('type' => 'custom_css_id')
		);
	}

	public function get_default_settings() {
		return array(
			'field_name_label' =>__( 'Name', 'builder-contact' ),
			'field_email_label' => __( 'Email', 'builder-contact' ),
			'field_subject_label' => __( 'Subject', 'builder-contact' ),
			'field_message_label' => __( 'Message', 'builder-contact' ),
			'field_sendcopy_label' => __( 'Send a copy to myself', 'builder-contact' ),
			'field_sendcopy_subject' => __( 'COPY:', 'builder-contact' ),
			'field_send_label' => __( 'Send', 'builder-contact' ),
			'gdpr_label' => __( 'I consent to my submitted data being collected and stored', 'builder-contact' ),
			'field_name_require' => 'yes',
            'field_email_require' => 'yes',
			'field_name_active' => 'yes',
			'field_email_active' => 'yes',
			'field_subject_active' => 'yes',
			'field_subject_require' => 'yes',
			'field_message_active' => 'yes',
			'field_send_align' => 'left',
			'field_extra' => '{ "fields": [] }',
			'field_order' => '{}',
			'field_optin_label' => __( 'Subscribe to my newsletter.', 'builder-contact' )
		);
	}

	public function get_styling() {
	    /*START temp solution when the addon is new,the FW is old 09.03.19*/
	    if(version_compare(THEMIFY_VERSION, '4.5', '<')){
		return array(); 
	    }
		$general = array(
		    //bacground
		    self::get_expand('bg', array(
		       self::get_tab(array(
			   'n' => array(
			       'options' => array(
				   self::get_color('', 'background_color', 'bg_c', 'background-color')
			       )
			   ),
			   'h' => array(
			       'options' => array(
				   self::get_color('', 'bg_c', 'bg_c', 'background-color', 'h')
			       )
			   )
		       ))
		   )),
		    self::get_expand('f', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_font_family(),
				    self::get_color_type(' label'),
				    self::get_font_size(),
				    self::get_line_height(),
				    self::get_text_align(),
					self::get_text_shadow(),
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_font_family('', 'f_f', 'h'),
				    self::get_color_type(' label','h'),
				    self::get_font_size('', 'f_s', '', 'h'),
				    self::get_line_height('','l_h','h'),
				    self::get_text_align('', 't_a', 'h'),
					self::get_text_shadow('','t_sh','h'),
				)
			    )
			))
		    )),
		    // Padding
		    self::get_expand('p', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_padding()
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_padding('', 'p', 'h')
				)
			    )
			))
		    )),
		    // Margin
		    self::get_expand('m', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_margin()
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_margin('', 'm', 'h')
				)
			    )
			))
		    )),
		    // Border
		    self::get_expand('b', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_border()
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_border('', 'b', 'h')
				)
			    )
			))
		    )),
				// Height & Min Height
				self::get_expand('ht', array(
						self::get_height(),
						self::get_min_height(),
						self::get_max_height()
					)
				),
			// Rounded Corners
			self::get_expand('r_c', array(
					self::get_tab(array(
						'n' => array(
							'options' => array(
								self::get_border_radius()
							)
						),
						'h' => array(
							'options' => array(
								self::get_border_radius('', 'r_c', 'h')
							)
						)
		    ))
				)
			),
			// Shadow
			self::get_expand('sh', array(
					self::get_tab(array(
						'n' => array(
							'options' => array(
								self::get_box_shadow()
							)
						),
						'h' => array(
							'options' => array(
								self::get_box_shadow('', 'sh', 'h')
							)
						)
					))
				)
			),
		);

		$labels = array(
			// Font
                        self::get_seperator('f'),
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_font_family(' .control-label','font_family_labels'),
				    self::get_color(' .control-label', 'font_color_labels'),
				    self::get_font_size(' .control-label','font_size_labels'),
					self::get_text_shadow(' .control-label','t_sh_l'),
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_font_family(' .control-label','f_f_l','h'),
				    self::get_color(' .control-label', 'f_c_l',null,null,'h'),
				    self::get_font_size(' .control-label','f_s_l','','h'),
					self::get_text_shadow(' .control-label','t_sh_l','h'),
				)
			    )
			))
		);

		$inputs = array(
		    self::get_expand('bg', array(
			   self::get_tab(array(
			       'n' => array(
				   'options' => array(
				       self::get_color(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ), 'background_color_inputs', 'bg_c', 'background-color'),
				   )
			       ),
			       'h' => array(
				   'options' => array(
				         self::get_color(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ), 'b_c_i', 'bg_c', 'background-color','h'),
				   )
			       )
			   ))
		    )),
		    self::get_expand('f', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_font_family(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ),'font_family_inputs'),
				    self::get_color(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ), 'font_color_inputs'),
				    self::get_font_size(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ),'font_size_inputs'),
					self::get_text_shadow(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ),'t_sh_i'),
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_font_family(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ),'f_f_i','h'),
				    self::get_color(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ), 'f_c_i',null,null,'h'),
				    self::get_font_size(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ),'f_s_i','','h'),
					self::get_text_shadow(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ),'t_sh_i','h'),
				)
			    )
			))
		    )),
		    self::get_expand('Placeholder', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_font_family(array(' input[type="text"]::placeholder', ' textarea::placeholder', ' input[type="tel"]::placeholder' ),'f_f_in_ph'),
				    self::get_color(array(' input[type="text"]::placeholder', ' textarea::placeholder', ' input[type="tel"]::placeholder' ), 'f_c_in_ph'),
				    self::get_font_size(array(' input[type="text"]::placeholder', ' textarea::placeholder', ' input[type="tel"]::placeholder' ),'f_s_in_ph'),
					self::get_text_shadow(array(' input[type="text"]::placeholder', ' textarea::placeholder', ' input[type="tel"]::placeholder' ),'t_sh_in_ph'),
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_font_family(array( ' input[type="text"]:hover::placeholder', ' textarea:hover::placeholder', ' input[type="tel"]:hover::placeholder' ),'f_f_in_ph_h',''),
				    self::get_color(array( ' input[type="text"]:hover::placeholder', ' textarea:hover::placeholder', ' input[type="tel"]:hover::placeholder' ), 'f_c_in_ph_h',null,null,''),
				    self::get_font_size(array( ' input[type="text"]:hover::placeholder', ' textarea:hover::placeholder', ' input[type="tel"]:hover::placeholder' ),'f_s_in_ph_h','',''),
					self::get_text_shadow(array( ' input[type="text"]:hover::placeholder', ' textarea:hover::placeholder', ' input[type="tel"]:hover::placeholder' ),'t_sh_in_ph_h',''),
				)
			    )
			))
		    )),
		    // Border
		    self::get_expand('b', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_border(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ),'border_inputs')
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_border(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ),'b_i','h')
				)
			    )
			))
		    )),
			// Padding
			self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ), 'in_p')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ), 'in_p', 'h')
				)
				)
			))
			)),
			// Margin
			self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ), 'in_m')
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ), 'in_m', 'h')
				)
				)
			))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ), 'in_r_c')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ), 'in_r_c', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ), 'in_b_sh')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(array( ' input[type="text"]', ' textarea', ' input[type="tel"]' ), 'in_b_sh', 'h')
						)
					)
				))
			))
		);

		$send_button = array(
		    
		    self::get_expand('bg', array(
			   self::get_tab(array(
			       'n' => array(
				   'options' => array(
				       self::get_color(' .builder-contact-field-send button', 'background_color_send', 'bg_c', 'background-color')
				   )
			       ),
			       'h' => array(
				   'options' => array(
				        self::get_color(' .builder-contact-field-send button', 'background_color_send', 'bg_c', 'background-color','h')
				   )
			       )
			   ))
		    )),
		    self::get_expand('f', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_font_family(' .builder-contact-field-send button' ,'font_family_send'),
				    self::get_color( ' .builder-contact-field-send button', 'font_color_send'),
				    self::get_font_size( ' .builder-contact-field-send button','font_size_send'),
					self::get_text_shadow(' .builder-contact-field-send button' ,'t_sh_b'),
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_font_family(' .builder-contact-field-send button' ,'f_f_s','h'),
				    self::get_color( ' .builder-contact-field-send button', 'f_c_s',null,null,'h'),
				    self::get_font_size( ' .builder-contact-field-send button','f_s_s','','h'),
					self::get_text_shadow(' .builder-contact-field-send button' ,'t_sh_b','h'),
				)
			    )
			))
		    )),
		    // Border
		    self::get_expand('b', array(
			self::get_tab(array(
			    'n' => array(
				'options' => array(
				    self::get_border(' .builder-contact-field-send button','border_send')
				)
			    ),
			    'h' => array(
				'options' => array(
				    self::get_border(' .builder-contact-field-send button','b_s','h')
				)
			    )
			))
		    )),
			// Padding
			self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(' .builder-contact-field-send button', 'p_sd')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(' .builder-contact-field-send button', 'p_sd', 'h')
				)
				)
			))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .builder-contact-field-send button', 'r_c_sd')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .builder-contact-field-send button', 'r_c_sd', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow(' .builder-contact-field-send button', 's_sd')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .builder-contact-field-send button', 's_sd', 'h')
						)
					)
				))
			))
		);

		$success_message = array(
			self::get_expand('bg', array(
			   self::get_tab(array(
			       'n' => array(
				   'options' => array(
					self::get_color(' .contact-success', 'background_color_success_message','bg_c', 'background-color')
				   )
			       ),
			       'h' => array(
				   'options' => array(
				        self::get_color(' .contact-success', 'b_c_s_m','bg_c', 'background-color','h')
				   )
			       )
			   ))
			)),
			self::get_expand('f', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_font_family(' .contact-success','font_family_success_message'),
					self::get_color(' .contact-success', 'font_color_success_message'),
					self::get_font_size(' .contact-success','font_size_success_message'),
					self::get_line_height(' .contact-success','line_height_success_message'),
					self::get_text_align(' .contact-success','text_align_success_message'),
                    self::get_text_shadow(' .contact-success','t_sh_m'),
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_font_family(' .contact-success','f_f_s_m','h'),
					self::get_color(' .contact-success', 'f_c_s_m',null,null,'h'),
					self::get_font_size(' .contact-success','f_s_s_m','','h'),
					self::get_line_height(' .contact-success','l_h_s_m','h'),
					self::get_text_align(' .contact-success','t_a_s_m','h'),
                    self::get_text_shadow(' .contact-success','t_sh_m','h'),
				    )
				)
			    ))
			)),
			// Padding
			self::get_expand('p', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_padding(' .contact-success','padding_success_message')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_padding(' .contact-success','p_s_m','h')
				    )
				)
			    ))
			)),
			// Margin
			self::get_expand('m', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_margin(' .contact-success','margin_success_message')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_margin(' .contact-success','m_s_m','h')
				    )
				)
			    ))
			)),
			// Border
			self::get_expand('b', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_border(' .contact-success','border_success_message')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_border(' .contact-success','b_s_m','h')
				    )
				)
			    ))
			))
                        
		);

		$error_message = array(
		    
			self::get_expand('bg', array(
			   self::get_tab(array(
			       'n' => array(
				   'options' => array(
					self::get_color(' .contact-error', 'background_color_error_message', 'bg_c', 'background-color'),
				   )
			       ),
			       'h' => array(
				   'options' => array(
				        self::get_color(' .contact-error', 'b_c_e_m', 'bg_c', 'background-color','h'),
				   )
			       )
			   ))
			)),
			self::get_expand('f', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_font_family(' .contact-error','font_family_error_message'),
					self::get_color(' .contact-error', 'font_color_error_message'),
					self::get_font_size(' .contact-error','font_size_error_message'),
					self::get_line_height(' .contact-error','line_height_error_message'),
					self::get_text_align(' .contact-error','text_align_error_message'),
                    self::get_text_shadow(' .contact-error','t_sh_e_m'),
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_font_family(' .contact-error','f_f_e_m'),
					self::get_color(' .contact-error', 'f_c_e_m',null,null,'h'),
					self::get_font_size(' .contact-error','f_s_e_m','','h'),
					self::get_line_height(' .contact-error','l_h_e_m','h'),
					self::get_text_align(' .contact-error','t_a_e_m','h'),
                    self::get_text_shadow(' .contact-error','t_sh_e_m','h'),
				    )
				)
			    ))
			)),
			// Padding
			self::get_expand('p', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_padding(' .contact-error','padding_error_message')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_padding(' .contact-error','p_e_m','h')
				    )
				)
			    ))
			)),
			// Margin
			self::get_expand('m', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_margin(' .contact-error','margin_error_message'),
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_margin(' .contact-error','m_e_m','h'),
				    )
				)
			    ))
			)),
			// Border
			self::get_expand('b', array(
			    self::get_tab(array(
				'n' => array(
				    'options' => array(
					self::get_border(' .contact-error','border_error_message')
				    )
				),
				'h' => array(
				    'options' => array(
					self::get_border(' .contact-error','b_e_m','h')
				    )
				)
			    ))
			))
		);

		return array(
			'type' => 'tabs',
			'options' => array(
				'g' => array(
					'options' => $general
				),
				'm_t' => array(
					'options' => $this->module_title_custom_style()
				),
				'l' => array(
					'label' => __('Field Labels', 'builder-contact'),
					'options' => $labels
				),
				'i' => array(
					'label' => __('Input Fields', 'builder-contact'),
					'options' => $inputs
				),
				's_b' => array(
					'label' => __('Send Button', 'builder-contact'),
					'options' => $send_button
				),
				's_m' => array(
					'label' => __('Success Message', 'builder-contact'),
					'options' => $success_message
				),
				'e_m' => array(
					'label' => __('Error Message', 'builder-contact'),
					'options' => $error_message
				)
			)
		);

	}

	protected function _visual_template() {
		$module_args = self::get_module_args();?>
		<#
		var def ={
		    'field_email_active':'yes',
		    'field_name_active':'yes',
		    'field_message_active':'yes'
		};
		_.defaults(data, def);
		try{
		    field_extra = JSON.parse(data.field_extra).fields;
		} catch( e ){
			field_extra = {};
		}
		try{
			field_order = JSON.parse(data.field_order);
		} catch( e ){
			field_order = {};
		}
		#>
		<div class="module module-<?php echo $this->slug; ?> {{ data.css_class_contact }} <# data.layout_contact ? print('contact-' + data.layout_contact) : ''; #>">
			<# if( data.mod_title_contact ) { #>
				<?php echo $module_args['before_title']; ?>
				{{{ data.mod_title_contact }}}
				<?php echo $module_args['after_title']; ?>
			<# } #>

			<form class="builder-contact" method="post">
				<div class="contact-message"></div>

				<div class="builder-contact-fields">
				    <# if( data.field_name_active === 'yes' ) { #>
					<div class="builder-contact-field builder-contact-field-name builder-contact-text-field" data-order="{{ field_order.field_name_label }}">
					    <label class="control-label"><span class="tb-label-span">{{data.field_name_label}} <# if( data.field_name_require ){ #></span><span class="required">*</span><# } #></label>
						<div class="control-input">
							<input type="text" name="contact-name" placeholder="{{ data.field_name_placeholder }}" class="form-control" required />
						</div>
					</div>
				    <# } #>

				    <# if( data.field_email_active === 'yes' ) { #>
					<div class="builder-contact-field builder-contact-field-email builder-contact-text-field" data-order="{{ field_order.field_email_label }}">
					    <label class="control-label"><span class="tb-label-span">{{data.field_email_label}}<# if( data.field_email_require ){ #></span><span class="required">*</span><# } #></label>
						<div class="control-input">
							<input type="text" name="contact-email" placeholder="{{ data.field_email_placeholder }}"  class="form-control" required />
						</div>
					</div>
				    <# } #>

					<# if( data.field_subject_active === 'yes' ) { #>
					<div class="builder-contact-field builder-contact-field-subject builder-contact-text-field" data-order="{{ field_order.field_subject_label }}">
						<label class="control-label"><span class="tb-label-span"><#  print(data.field_subject_label)#> <# if( data.field_subject_require ){ #></span><span class="required">*</span><# } #></label>
						<div class="control-input">
							<input type="text" name="contact-subject" placeholder="{{ data.field_subject_placeholder }}"  class="form-control" <# true === data.field_subject_require && print( 'required' ) #> />
						</div>
					</div>
					<# } #>

				<# if( data.field_message_active === 'yes' ) { #>
					<div class="builder-contact-field builder-contact-field-message builder-contact-textarea-field" data-order="{{ field_order.field_message_label }}">
						<label class="control-label"><span class="tb-label-span">{{data.field_message_label}} <# if( data.field_message_label != '' ) { #></span><span class="required">*</span><# } #></label>
						<div class="control-input">
							<textarea name="contact-message" placeholder="{{ data.field_message_placeholder }}" rows="8" cols="45" class="form-control" required></textarea>
						</div>
					</div>
				<# } #>

					<# _.each( field_extra, function( field, field_index ){ #>
					<div class="builder-contact-field builder-contact-field-extra builder-contact-{{ field.type }}-field" data-order="{{field_order[field.label]!==undefined?field_order[field.label]:(field.order!==undefined?field.order:'')}}">
							<label class="control-label">{{{ field.label }}} <# if( field.required ){ #><span class="required">*</span><# } #></label>
							<div class="control-input">
							<# if( 'textarea' == field.type ){ #>
								<textarea name="field_extra_{{ field_index }}" placeholder="{{ field.value }}" rows="8" cols="45" class="form-control" <# true === field.required && print( 'required' ) #>></textarea>
							<# } else if( 'text' == field.type ){ #>
								<input type="text" name="field_extra_{{ field_index }}" placeholder="{{ field.value }}" class="form-control" <# true === field.required && print( 'required' ) #> />
							<# } else if( 'upload' == field.type ){ #>
								<input type="file" name="field_extra_{{ field_index }}" placeholder="{{ field.value }}" class="form-control" <# true === field.required && print( 'required' ) #> />
							<# } else if( 'tel' == field.type ){ #>
								<input type="tel" name="field_extra_{{ field_index }}" placeholder="{{ field.value }}" class="form-control" <# true === field.required && print( 'required' ) #> />
							<# } else if( 'static' == field.type ){ #>
								{{{ field.value }}}
							<# } else if( 'radio' == field.type ){ #>
								<# _.each( field.value, function( value, index ){ #>
									<label>
										<input type="radio" name="field_extra_{{ field_index }}" value="{{ value }}" class="form-control" <# true === field.required && print( 'required' ) #> /> {{ value }}
									</label>
								<# }) #>
							<# } else if( 'select' == field.type ){ #>
								<select name="field_extra_{{ field_index }}" class="form-control" <# true === field.required && print( 'required' ) #>>
									<# _.each( field.value, function( value, index ){ #>
										<option value="{{ value }}"> {{ value }} </option>
									<# }) #>
								</select>
							<# } else if( 'checkbox' == field.type ){ #>
								<# _.each( field.value, function( value, index ){ #>
									<label>
										<input type="checkbox" name="field_extra_{{ field_index }}[]" value="{{ value }}" class="form-control"/> {{ value }}
									</label>
								<# }) #>
							<# } #>
							</div>
						</div>
					<# }) #>

					<# if( data.field_captcha_active === 'yes' ) { #>
                        <?php $recaptcha_version = Builder_Contact::get_instance()->get_option('recapthca_public_key'); ?>
                        <# var recaptcha_version = '<?php echo esc_attr($recaptcha_version); ?>'; #>
						<div class="builder-contact-field builder-contact-field-captcha">
                            <# if( '' !== data.field_captcha_label && undefined != data.field_captcha_label ) { #>
                            <label class="control-label">{{{ data.field_captcha_label }}} <span class="required">*</span></label>
                            <# } #>
                            <div class="control-input">
								 <div class="themify_captcha_field<?php echo 'v2'===$recaptcha_version?' g-recaptcha':''; ?>" data-sitekey="<?php echo esc_attr( Builder_Contact::get_instance()->get_option( 'recapthca_public_key' ) ); ?>" data-ver="<?php echo esc_attr($recaptcha_version); ?>"></div>
							</div>
						</div>
					<# } #>

					<# if( data.field_sendcopy_active==='yes' ) { #>
					<div class="builder-contact-field builder-contact-field-sendcopy">
						<div class="control-label">
							<div class="control-input checkbox">
								<label class="send-copy">
									<input type="checkbox" name="send-copy" value="1" /> {{{data.field_sendcopy_label}}}
								</label>
							</div>
						</div>
					</div>
					<# } #>

					<# if ( data.field_optin_active === 'yes' ) { #>
					<div class="builder-contact-field builder-contact-field-optin">
						<div class="control-label">
							<div class="control-input checkbox">
								<label class="optin">
									<input type="checkbox" name="optin" value="1" /> {{{data.field_optin_label}}}
								</label>
							</div>
						</div>
					</div>
					<# } #>

					<# if( 'accept' ===data.gdpr ) { #>
					<div class="builder-contact-field builder-contact-field-gdpr">
						<div class="control-label">
							<div class="control-input checkbox">
								<label class="field-gdpr">
									<input type="checkbox" name="gdpr" value="1" required/> {{{data.gdpr_label}}}<span class="required">*</span>
								</label>
							</div>
						</div>
					</div>
					<# } #>


					<div class="builder-contact-field builder-contact-field-send">
						<div class="control-input builder-contact-field-send-{{ data.field_send_align }}">
							<button type="submit" class="btn btn-primary"> <i class="fa fa-cog fa-spin"></i>{{{ data.field_send_label }}}</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	<?php
	}
}

Themify_Builder_Model::register_module( 'TB_Contact_Module' );
