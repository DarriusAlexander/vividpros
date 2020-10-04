(function ($) {
    'use strict';
    /* Custom Meta Box Date*/

    $(document).on('ptb_add_metabox_relation', function (e) {
        return {'many': 1,
                'post_type':''
               };
    });
    $(document).on('ptb_metabox_create_relation', function (e) {
        if(e.options.many){
            e.container.find('input[name="' + e.id + '_many"]').prop('checked', true);
        }
        if(e.options.post_type){
            e.container.find('#' + e.id + '_post_type option').prop('disabled',true);
        }
        e.container.find('#' + e.id + '_post_type option[value="' + e.options.post_type + '"]').prop({'selected':true,'disabled':false});
    });


    $(document).on('ptb_metabox_save_relation', function (e) {
        e.options.post_type = $('#' + e.id + '_post_type option:selected').not(':disabled').val();
        if(!e.options.post_type){
            e.options.deleted = true;
        }
        else{
            e.options.many = $('input[name="' + e.id + '_many"]:checked').val();
        }
    });


}(jQuery));
 