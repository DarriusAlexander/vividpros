<?php

/**
 * The plugin options management class
 *
 * @link       http://themify.me
 * @since      1.0.0
 *
 * @package    PTB
 * @subpackage PTB/includes
 */
class PTB_Search_Options {

    private static $cache_key = 'ptb_search_cache';

    public static function get_name($key, $multi = false) {
        $fields = array(
            'has' => __('Has', 'ptb-search'),
            'thumbnail' => __('Featured Image', 'ptb-search'),
            'comments' => __('Comments', 'ptb-search'),
            'category' => !$multi ? __('Cateogry', 'ptb-search') : __('Cateogries', 'ptb-search'),
            'post_tag' => !$multi ? __('Tag', 'ptb-search') : __('Tags', 'ptb-search'),
            'title' => __('Title', 'ptb-search'),
            'author'=>__('Author','ptb-search'),
            'date'=> __('Post Date','ptb-search'),
            'button'=>__('Button','ptb-search')
        );
        return isset($fields[$key]) ? $fields[$key] : false;
    }

    public static function is_multy($check) {

        return $check == 'checkbox' || $check == 'multiselect';
    }

    public static function set_cache($cache) {
        return update_option(self::$cache_key, $cache, 'no');
    }

    public static function get_cache() {
        return get_option(self::$cache_key);
    }

    public static function remove_cache() {
        return delete_option(self::$cache_key);
    }

    public static function get_query_cache($post_type, $query) {
        $cache = self::get_cache();
        if (isset($query['f'])) {
            unset($query['f']);
        }
        $query = md5(serialize($query));
        return isset($cache['response']) && isset($cache['response'][$post_type]) && isset($cache['response'][$post_type][$query]) ? $cache['response'][$post_type][$query] : false;
    }

    public static function set_query_cache($post_type, $query, $result) {
        $cache = self::get_cache();
        if (isset($query['f'])) {
            unset($query['f']);
        }
        $query = md5(serialize($query));
        $cache['response'][$post_type][$query] = $result;
        return self::set_cache($cache);
    }

}
