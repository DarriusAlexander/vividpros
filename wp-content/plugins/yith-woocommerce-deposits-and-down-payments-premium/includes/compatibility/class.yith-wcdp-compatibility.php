<?php
if( !defined('ABSPATH')){
    exit;
}

if( !class_exists( 'YITH_WCDP_Compatibility')){

    class YITH_WCDP_Compatibility{

        protected static $_instance;

        public function __construct() {
           
            if( defined( 'YITH_YWPI_INIT' ) ){
                require_once( 'class.yith-wcdp-yith-pdf-invoice-compatibility.php' );
                YITH_WCDP_YITH_PDF_Invoice_Compatibility();
            }

            if( defined( 'YITH_YWDPD_PREMIUM' ) ){
                require_once( 'class.yith-wcdp-yith-dynamic-pricing-and-discounts-compatibility.php' );
                YITH_WCDP_YITH_Dynamic_Pricing_And_Discounts();
            }

            if( defined( 'YITH_WCEVTI_INIT' ) ){
                require_once( 'class.yith-wcdp-yith-event-tickets-compatibility.php' );
                YITH_WCDP_YITH_Event_Tickets();
            }

            if( defined( 'YITH_WCPO_INIT' ) ){
                require_once( 'class.yith-wcdp-yith-pre-order-compatibility.php' );
	            YITH_WCDP_YITH_Pre_Order();
            }

            if( defined( 'YITH_WCP_PREMIUM' ) ){
                require_once( 'class.yith-wcdp-yith-composite-products.php' );
                YITH_WCDP_YITH_Composite_Products();
            }
        }

        /**
         * @author YITHEMES
         * @since 1.0.0
         * @return YITH_WCDP_Compatibility unique access
         */
        public static function get_instance(){
            if( is_null( self::$_instance ) ){
                self::$_instance = new self();
            }

            return self::$_instance;
        }
    }
}

/**
 * @return YITH_WCDP_Compatibility
 */
function YITH_WCDP_Compatibility(){

    return  YITH_WCDP_Compatibility::get_instance();
}