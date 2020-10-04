(function ($) {
    'use strict';

    var GetList = function () {
        var $wrapper = $('#ptb-search-list-form #the-list');
        var $form = $wrapper.closest('form');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {'action': 'ptb_search_list'},
            beforeSend: function () {
                $form.addClass('ptb-search-wait');
            },
            complete: function () {
                $form.removeClass('ptb-search-wait');
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
            if ($wr.find('#ptb-search-edit-form').length > 0) {
                GetList();
            }
        });

        $('body').delegate('.ptb_search_templates a.ptb_search_delete', 'click', function (e) {
            e.preventDefault();
            if (confirm(ptb_js.template_delete)) {
                var $form = $(this).closest('form');
                $.ajax({
                    url: this,
                    type: 'POST',
                    beforeSend: function () {
                        $form.addClass('ptb-search-wait');
                    },
                    complete: function () {
                        $form.removeClass('ptb-search-wait');
                    },
                    success: function (resp) {
                        if (resp) {
                            resp = $(resp).find('#the-list').html();
                            $('#ptb-search-list-form #the-list').html(resp);
                        }
                    }
                });
            }
        });
        
        $(document).on('PTB.template_drag_end', function (event, $item, $ui, $type) {
            if ($type !== 'search') {
                return false;
            }
            var exclude = new Array('taxonomies', 'custom_image', 'custom_text','has');
            var $data = $item.data('type');

            if ($.inArray($data, exclude) === -1 && $('#ptb_row_wrapper').find('[data-type="' + $data + '"]').length > 2) {
                if ($item.hasClass('ptb_is_metabox')) {
                    var $name = $item.find('input,select').attr('name');
                    if ($name && $('#ptb_row_wrapper').find('[data-type="' + $data + '"] [name="' + $name + '"]').length < 2) {
                        return false;
                    }
                }
                alert(ptb_search.module + ' ' + $item.find('.ptb_module_name').text());
                $item.remove();
                return false;
            }
        });

        $('body').delegate('#ptb-search-form-save', 'submit', function (e) {
            e.preventDefault();
            var $form = $(this);
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function () {
                    $form.addClass('ptb-search-wait');
                },
                complete: function () {
                    $form.removeClass('ptb-search-wait');
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


    });
}(jQuery));