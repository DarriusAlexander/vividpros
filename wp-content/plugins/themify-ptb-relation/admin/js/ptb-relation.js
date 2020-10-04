(function ($) {
    'use strict';

    var GetList = function () {
        var $wrapper = $('#ptb-relation-list-form #the-list');
        var $form = $wrapper.closest('form');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {'action': 'ptb_relation_list'},
            beforeSend: function () {
                $form.addClass('ptb-relation-wait');
            },
            complete: function () {
                $form.removeClass('ptb-relation-wait');
            },
            success: function (resp) {
                if (resp) {
                    resp = $(resp).find('#the-list').html();
                    $wrapper.html(resp);
                }
            }
        });
    };
  
    $(document).ready(function () {
        $(document).on('PTB.close_lightbox', function (e, $this) {
            var $wr = $($this).next('#ptb_lightbox_container');
            if ($wr.find('#ptb-relation-edit-form').length > 0) {
                GetList();
            }
        });

        $('body').delegate('.ptb_relation_templates a.ptb_relation_delete', 'click', function (e) {
            e.preventDefault();
            if (confirm(ptb_js.template_delete)) {
                var $form = $(this).closest('form');
                $.ajax({
                    url: this,
                    type: 'POST',
                    beforeSend: function () {
                        $form.addClass('ptb-relation-wait');
                    },
                    complete: function () {
                        $form.removeClass('ptb-relation-wait');
                    },
                    success: function (resp) {
                        if (resp) {
                            resp = $(resp).find('#the-list').html();
                            $('#ptb-relation-list-form #the-list').html(resp);
                        }
                    }
                });
            }
        });
        
        $('body').delegate('#ptb-relation-form-save', 'submit', function (e) {
            e.preventDefault();
            var $form = $(this);
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function () {
                    $form.addClass('ptb-frontend-wait');
                },
                complete: function () {
                    $form.removeClass('ptb-frontend-wait');
                },
                success: function (resp) {
                    if (resp) {
                        $form.closest('.ptb_lightbox_inner').fadeOut('normal', function () {
                            $(this).html(resp).fadeIn();
                        });
                    }
                }
            });
        });
       
        $('body').delegate('.ptb_relation_mode input[type="radio"]', 'change', function () {
            var $p = $(this).closest('.ptb_back_active_module_content');
            $p.find('fieldset').slideUp();
            $p.find('#'+$(this).data('id')+'_').slideDown();
        });
        $(document).on('PTB.template_load', function () {
            $('.ptb_relation_mode input[type="radio"]:checked').trigger('change');
        });
    });
}(jQuery));