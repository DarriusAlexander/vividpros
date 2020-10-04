<?php
/**
 * Created by PhpStorm.
 * User: shahnuralam
 * Date: 8/12/18
 * Time: 2:17 AM
 */

namespace WPDM;


class API
{
    function __construct()
    {
        add_action( 'rest_api_init', array($this, 'introduceEndpoints'));
    }

    function introduceEndpoints(){

        //wpdm/v1/search-package
        register_rest_route( 'wpdm/v1', '/search-package', array(
            'methods' => 'GET',
            'callback' => array($this, 'searchPackages'),
        ) );

        //wpdm/v1/link-templates
        register_rest_route( 'wpdm/v1', '/link-templates', array(
            'methods' => 'GET',
            'callback' => array($this, 'linkTemplates'),
        ) );

        //wpdm/v1/categories
        register_rest_route( 'wpdm/v1', '/categories', array(
            'methods' => 'GET',
            'callback' => array($this, 'categories'),
        ) );
    }
}