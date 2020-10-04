<?php
/**
 * Timeline TimelineJS template
 *
 * @var $items
 * @var $settings
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
extract( $settings );

$date_format = apply_filters( 'builder_timeline_graph_date_format', "Y,m,d G:i:s", $settings );

$data = array(
	'timeline' => array(
		'type' => 'default',
		'date' => array()
	)
);

$items = apply_filters( 'themify_builder_timeline_graph_items', $items, $settings );
foreach( $items as $item ) {
	$item_data = array(
		'startDate' => date( $date_format, strtotime( $item['date'] ) ),
		'endDate' => date( $date_format, strtotime( $item['date'] ) ),
		'headline' => isset( $item['link'] )
			? '<a href="' . $item['link'] . '">' . $item['title'] . '</a>'
			: $item['title'],
		'text' => $item['content'],
		'asset' => array(
			'media' => '',
			'credit' => '',
			'caption' => ''
		)
	);
	if( $item['hide_featured_image'] != 'yes' ) {
		preg_match( '/src=[\"\'](.*?)[\'\"]/', $item['image'], $matches );
		$item_data['asset']['media'] = isset( $matches[1] ) ? $matches[1] : '';
	}
	$data['timeline']['date'][] = $item_data;
}

$config = array(
	'type' => 'timeline',
	'width' => '100%',
	'height' => 650,
	'embed_id' => 'timeline-embed-' . $module_ID,
	'debug' => false,
	'lang' => substr( get_locale(), 0, 2 ),
	'css' => Builder_Timeline::get_instance()->url . 'assets/knight-lab-timelinejs/css/timeline.css',
	'js' => Builder_Timeline::get_instance()->url . 'assets/knight-lab-timelinejs/js/timeline-min.js',
	'start_at_end' => isset( $settings['start_at_end'] ) && $settings['start_at_end'] == true ? 1 : 0,
);
?>
<div class="timeline-embed" id="timeline-embed-<?php echo $module_ID; ?>" data-id="<?php esc_attr_e($module_ID); ?>" data-data="<?php esc_attr_e(base64_encode(json_encode( $data ))); ?>" data-config='<?php echo json_encode( $config ); ?>'></div>