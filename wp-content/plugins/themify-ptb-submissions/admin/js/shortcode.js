(function ($) {
    'use strict';
    
    
    $(document).on('PTB.insert_shortcode', function (e, $data) {

        if ($data.setting.type === 'frontend') {
            return '[ptb_submission "' + $data.data.frontend_post_type + '"]';
        }
        return false;
    });


}(jQuery));