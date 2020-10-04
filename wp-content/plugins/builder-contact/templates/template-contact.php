<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Contact
 *
 * Access original fields: $args['mod_settings']
 */

if (TFCache::start_cache($args['mod_name'], self::$post_id, array('ID' => $args['module_ID']))):
    $fields_default = array(
        'mod_title_contact' => '',
        'layout_contact' => 'style1',
        'field_name_label' => empty($args['mod_settings']['field_name_label']) && !empty($args['mod_settings']['field_name_placeholder']) ? '' : __('Name', 'builder-contact'),
        'field_name_placeholder' => '',
        'field_email_label' => empty($args['mod_settings']['field_email_label']) && !empty($args['mod_settings']['field_email_placeholder']) ? '' : __('Email', 'builder-contact'),
        'field_email_placeholder' => '',
        'field_subject_label' => empty($args['mod_settings']['field_subject_label']) && !empty($args['mod_settings']['field_subject_placeholder']) ? '' : __('Subject', 'builder-contact'),
        'field_subject_placeholder' => '',
        'gdpr' => '',
        'gdpr_label' => __('I consent to my submitted data being collected and stored', 'builder-contact'),
        'field_captcha_label' => __('Captcha', 'builder-contact'),
        'field_extra' => '{ "fields": [] }',
        'field_order' => '{}',
        'field_message_label' => empty($args['mod_settings']['field_message_label']) && !empty($args['mod_settings']['field_message_placeholder']) ? '' : __('Message', 'builder-contact'),
        'field_message_placeholder' => '',
        'field_sendcopy_label' => __('Send Copy', 'builder-contact'),
        'field_send_label' => __('Send', 'builder-contact'),
        'field_send_align' => 'left',
        'animation_effect' => '',
        'css_class_contact' => '',
        'field_message_active' => 'yes',
        'field_subject_active' => '',
        'field_subject_require' => '',
        'field_name_require' => '',
        'field_email_require' => '',
        'field_email_active' => 'yes',
        'field_name_active' => 'yes',
        'field_sendcopy_active' => '',
        'field_captcha_active' => '',
        'field_optin_active' => '',
        'field_optin_label' => __( 'Subscribe to my newsletter.', 'builder-contact' ),
        'provider' => '', // Optin service provider
    );

    $fields_args = wp_parse_args($args['mod_settings'], $fields_default);
    unset($args['mod_settings']);
    $field_message_active =  'yes' === $fields_args['field_message_active'];
    $field_subject_active = 'yes' === $fields_args['field_subject_active'];
    $field_email_active = 'yes' === $fields_args['field_email_active'];
    $field_name_active = 'yes' === $fields_args['field_name_active'];
    $field_subject_require =  $field_subject_active && 'yes' === $fields_args['field_subject_require'];
    $field_name_require =  $field_name_active && 'yes' === $fields_args['field_name_require'];
    $field_email_require =  $field_email_active && 'yes' === $fields_args['field_email_require'];
    $field_sendcopy_active = 'yes' === $fields_args['field_sendcopy_active'];
    $field_captcha_active = 'yes' === $fields_args['field_captcha_active'];

    $field_extra = json_decode( $fields_args['field_extra'], true );
    $field_order = json_decode( $fields_args['field_order'], true );

	$container_class = apply_filters('themify_builder_module_classes', array(
        'module', 'module-' . $args['mod_name'], $args['module_ID'], 'contact-' . $fields_args['layout_contact'], self::parse_animation_effect($fields_args['animation_effect'], $fields_args), $fields_args['css_class_contact']
                    ), $args['mod_name'], $args['module_ID'], $fields_args);
    if(!empty($args['element_id'])){
	$container_class[] = 'tb_'.$args['element_id'];
    }
    if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
	$container_class[] = $fields_args['global_styles'];
    }

    $container_props = apply_filters('themify_builder_module_container_props', array(
        'id' => $args['module_ID'],
        'class' => implode(' ', $container_class),
	), $fields_args, $args['mod_name'], $args['module_ID']);

    ?>
    <!-- module contact -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
	<?php $container_props=$container_class=null;?>
        <?php if ($fields_args['mod_title_contact'] !== ''): ?>
            <?php echo $fields_args['before_title'] . apply_filters('themify_builder_module_title', $fields_args['mod_title_contact'], $fields_args) . $fields_args['after_title']; ?>
        <?php endif; ?>

        <?php do_action('themify_builder_before_template_content_render'); ?>

        <form action="<?php echo admin_url('admin-ajax.php'); ?>" class="builder-contact" id="<?php echo $args['module_ID']; ?>-form" method="post" data-post-id="<?php echo esc_attr( $args['builder_id'] ); ?>" data-element-id="<?php echo esc_attr( $args['element_id'] ); ?>">
            <div class="contact-message"></div>
	    <div class="builder-contact-fields">
	    <?php if($field_name_active):?>
                <div class="builder-contact-field builder-contact-field-name builder-contact-text-field" data-order="<?php echo isset($field_order['field_name_label'])?$field_order['field_name_label']:'' ?>">
                    <label class="control-label" for="<?php echo $args['module_ID']; ?>-contact-name"><span class="tb-label-span"><?php if ($fields_args['field_name_label'] !== ''): ?><?php echo $fields_args['field_name_label']; ?> </span><?php if( $field_name_require ){ ?><span class="required">*</span><?php } ?><?php endif; ?></label>
                    <div class="control-input">
                        <input type="text" name="contact-name" placeholder="<?php echo $fields_args['field_name_placeholder']; ?>" id="<?php echo $args['module_ID']; ?>-contact-name" value="" class="form-control" <?php echo $field_name_require ?  'required' : '' ?>/>
                    </div>
                </div>
	    <?php endif; ?>
	    <?php if($field_email_active):?>
                <div class="builder-contact-field builder-contact-field-email builder-contact-text-field" data-order="<?php echo isset($field_order['field_email_label'])?$field_order['field_email_label']:'' ?>">
                    <label class="control-label" for="<?php echo $args['module_ID']; ?>-contact-email"><span class="tb-label-span"><?php if ($fields_args['field_email_label'] !== ''): ?><?php echo $fields_args['field_email_label']; ?> </span><?php if( $field_email_require ){ ?><span class="required">*</span><?php } ?><?php endif; ?></label>
                    <div class="control-input">
                        <input type="text" name="contact-email" placeholder="<?php echo $fields_args['field_email_placeholder']; ?>" id="<?php echo $args['module_ID']; ?>-contact-email" value="" class="form-control" <?php echo $field_email_require ?  'required' : '' ?> />
                    </div>
                </div>
	    <?php endif; ?>
	    <?php if ($field_subject_active) : ?>
                    <div class="builder-contact-field builder-contact-field-subject builder-contact-text-field" data-order="<?php echo isset($field_order['field_subject_label'])?$field_order['field_subject_label']:'' ?>">
                        <label class="control-label" for="<?php echo $args['module_ID']; ?>-contact-subject"><span class="tb-label-span"><?php echo $fields_args['field_subject_label']; ?></span> <?php if( $field_subject_require ){ ?><span class="required">*</span><?php } ?></label>
                        <div class="control-input">
                            <input type="text" name="contact-subject" placeholder="<?php echo $fields_args['field_subject_placeholder']; ?>" id="<?php echo $args['module_ID']; ?>-contact-subject" value="" class="form-control" <?php echo $field_subject_require ?  'required' : '' ?> />
                        </div>
                    </div>
                <?php endif; ?>

		<?php if ( $field_message_active ) : ?>
		    <div class="builder-contact-field builder-contact-field-message builder-contact-textarea-field" data-order="<?php echo isset($field_order['field_message_label'])?$field_order['field_message_label']:'' ?>">
			<label class="control-label" for="<?php echo $args['module_ID']; ?>-contact-message"><span class="tb-label-span"><?php if ($fields_args['field_message_label'] !== ''): ?><?php echo $fields_args['field_message_label']; ?> </span><span class="required">*</span><?php endif; ?></label>
			<div class="control-input">
			    <textarea name="contact-message" placeholder="<?php echo $fields_args['field_message_placeholder']; ?>" id="<?php echo $args['module_ID']; ?>-contact-message" rows="8" cols="45" class="form-control" required></textarea>
			</div>
		    </div>
		<?php endif; ?>

                <?php if(is_array($field_extra['fields'])): ?>
                    <?php foreach( $field_extra['fields'] as $field_index => $field ): ?>
                        <?php
                        $field['value'] = isset( $field['value'] ) ? $field['value'] : '';
                        $field['label'] = isset( $field['label'] ) ? $field['label'] : '';
                        $required = isset( $field['required'] ) && true === $field['required'];
                        ?>
                        <div class="builder-contact-field builder-contact-field-extra builder-contact-<?php echo $field['type']; ?>-field" data-order="<?php echo isset($field_order[$field['label']])?$field_order[$field['label']]:(isset($field['order'])?$field['order']:'') ?>">
                            <label class="control-label" for="field_extra_<?php echo $field_index; ?>">
                                <?php echo $field['label']; ?>
                                <?php if( $required): ?>
                                    <span class="required">*</span>
                                <?php endif; ?>
                            </label>
                            <div class="control-input">
                                <?php if( 'textarea' === $field['type'] ): ?>
                                    <textarea name="field_extra_<?php echo $field_index; ?>" id="field_extra_<?php echo $field_index; ?>" placeholder="<?php echo esc_html($field['value']); ?>" rows="8" cols="45" class="form-control" <?php echo $required?  'required' : '' ?> ></textarea>
                                <?php elseif( 'text' === $field['type'] ): ?>
                                    <input type="text" name="field_extra_<?php echo $field_index; ?>" id="field_extra_<?php echo $field_index; ?>" placeholder="<?php echo esc_html($field['value']); ?>" class="form-control" <?php echo $required?  'required' : '' ?> />
                                <?php elseif( 'upload' === $field['type'] ): ?>
                                    <input type="file" name="field_extra_<?php echo $field_index; ?>" id="field_extra_<?php echo $field_index; ?>"  class="form-control" <?php echo $required?  'required' : '' ?> />
                                <?php elseif( 'tel' === $field['type'] ): ?>
                                    <input type="tel" name="field_extra_<?php echo $field_index; ?>" id="field_extra_<?php echo $field_index; ?>" placeholder="<?php echo esc_html($field['value']); ?>" class="form-control" <?php echo $required?  'required' : '' ?> />
                                <?php elseif( 'static' === $field['type'] ): ?>
                                    <?php echo do_shortcode( $field['value'] ); ?>
				<?php elseif(!empty($field['value'])):?>
				    <?php if( 'radio' === $field['type'] ): ?>
					<?php foreach( $field['value'] as $value ): ?>
					    <label>
						<input type="radio" name="field_extra_<?php echo $field_index; ?>" value="<?php echo esc_attr($value); ?>" class="form-control" <?php echo $required?  'required' : '' ?> /> <?php echo $value; ?>
					    </label>
					<?php endforeach; ?>
				    <?php elseif( 'select' === $field['type'] ): ?>
					<select id="field_extra_<?php echo $field_index; ?>" name="field_extra_<?php echo $field_index; ?>" class="form-control" <?php echo $required?  'required' : '' ?>>
						<?php if(!$required):?><option value=""><?php _e('Please select one' , 'themify')?></option><?php endif;?>
					    <?php foreach( $field['value'] as $value ): ?>
						<option value="<?php echo strip_tags($value); ?>"> <?php echo strip_tags($value); ?> </option>
					    <?php endforeach; ?>
					</select>
				    <?php elseif( 'checkbox' === $field['type'] ): ?>
					<?php foreach( $field['value'] as $value ): ?>
					    <label>
						<input type="checkbox" name="field_extra_<?php echo $field_index; ?>[]" value="<?php echo esc_html($value); ?>" class="form-control <?php echo $required?  'required' : '' ?>" <?php echo $required?  'required' : '' ?> /> <?php echo $value; ?>
					    </label>
					<?php endforeach; ?>
				    <?php endif; ?>
				<?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
				<?php $order_index = 9999; ?>

                <?php if ($field_sendcopy_active) : ?>
                    <div class="builder-contact-field builder-contact-field-sendcopy" data-order="<?php echo ++$order_index; ?>">
                        <div class="control-label">
                            <div class="control-input checkbox">
                                <label class="send-copy">
                                    <input type="checkbox" name="contact-sendcopy" id="<?php echo $args['module_ID']; ?>-sendcopy" value="1" /> <?php echo $fields_args['field_sendcopy_label']; ?>
                                </label>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

				<?php if ( $fields_args['field_optin_active'] ) : ?>
					<?php
					if ( ! class_exists( 'Builder_Optin_Services_Container' ) )
						include_once( THEMIFY_BUILDER_INCLUDES_DIR. '/optin-services/base.php' );
					$optin_instance = Builder_Optin_Services_Container::get_instance()->get_provider( $fields_args['provider'] );
					if ( $optin_instance ) : ?>
						<div class="builder-contact-field builder-contact-field-optin" data-order="<?php echo ++$order_index; ?>">
							<div class="control-label">
								<div class="control-input checkbox">
									<input type="hidden" name="contact-optin-provider" value="<?php echo esc_attr( $fields_args['provider'] ); ?>" />
									<?php
									foreach ( $optin_instance->get_options() as $provider_field ) :
										if ( isset( $provider_field['id'] ) && isset( $fields_args[ $provider_field['id'] ] ) ) : ?>
											<input type="hidden" name="contact-optin-<?php echo $provider_field['id']; ?>" value="<?php echo esc_attr( $fields_args[ $provider_field['id'] ] ); ?>" />
										<?php endif;
									endforeach;
									?>
									<label class="optin">
										<input type="checkbox" name="contact-optin" id="<?php echo $args['module_ID']; ?>-optin" value="1" /> <?php echo $fields_args['field_optin_label']; ?>
									</label>
								</div>
							</div>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( 'accept' === $fields_args['gdpr'] ) : ?>
					<div class="builder-contact-field builder-contact-field-gdpr" data-order="<?php echo ++$order_index; ?>">
						<div class="control-label">
							<div class="control-input checkbox">
								<label class="field-gdpr">
									<input type="checkbox" name="gdpr" value="1" required/> <?php echo $fields_args['gdpr_label']; ?> <span class="required">*</span>
								</label>
							</div>
						</div>
					</div>
				<?php endif; ?>

                <?php if ($field_captcha_active && Builder_Contact::get_instance()->get_option('recapthca_public_key') != '' && Builder_Contact::get_instance()->get_option('recapthca_private_key') != '') : ?>
                    <?php $recaptcha_version = Builder_Contact::get_instance()->get_option('recapthca_version','v2'); ?>
                    <div class="builder-contact-field builder-contact-field-captcha" data-order="<?php echo ++$order_index; ?>">
						<?php if('v3' !== $recaptcha_version) : ?>
                        <label class="control-label" for="<?php echo $args['module_ID']; ?>-contact-captcha"><?php echo $fields_args['field_captcha_label']; ?> <span class="required">*</span></label>
						<?php endif; ?>
                        <div class="control-input">
                            <div class="themify_captcha_field<?php echo 'v2'===$recaptcha_version?' g-recaptcha':''; ?>" data-sitekey="<?php echo esc_attr(Builder_Contact::get_instance()->get_option('recapthca_public_key')); ?>" data-ver="<?php echo esc_attr($recaptcha_version); ?>"></div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="builder-contact-field builder-contact-field-send" data-order="<?php echo ++$order_index; ?>">
                    <div class="control-input builder-contact-field-send-<?php echo $fields_args['field_send_align']; ?>">
                        <button type="submit" class="btn btn-primary"> <i class="fa fa-cog fa-spin"></i> <?php echo $fields_args['field_send_label']; ?> </button>
                    </div>
                </div>
            </div>
            <script type="text/javascript">
				// To load orders instantly, even don't wait for document ready
				(function($){
					var mylist = $('#<?php echo $args['module_ID']?>').first().find('.builder-contact-fields'),
						listitems = mylist.children('div').get();

					listitems.sort(function (a, b) {
						var compA = $(a).attr('data-order') ? parseInt( $(a).attr('data-order') ) : $(a).index(),
							compB = $(b).attr('data-order') ? parseInt( $(b).attr('data-order') ) : $(b).index();
						return ( compA < compB ) ? -1 : ( compA > compB ) ? 1 : 0;
					});
					$.each(listitems, function (idx, itm) {
						mylist.append(itm);
					});
				})(jQuery);
			</script>
        </form>

        <?php do_action('themify_builder_after_template_content_render'); ?>
    </div>
    <!-- /module contact -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>
