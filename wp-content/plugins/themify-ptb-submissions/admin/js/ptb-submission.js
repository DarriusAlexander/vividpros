(function ($) {
    'use strict';
    function getParameterByName(url, name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                results = regex.exec(url);
        return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    }
    var InitSelect = function ($select) {
        $select.chosen({
            disable_search_threshold: 7,
            search_contains: true,
            width: "100%",
        }).on('change', function (e, params) {
            $(e.currentTarget).find('option:not(:first)').prop('disabled', params.selected === 'all').trigger('chosen:updated');
        }).on('chosen:showing_dropdown', function (e) {
            $(e.currentTarget).closest('.ptb_dragged').css('z-index', 110);
        }).on('chosen:hiding_dropdown', function (e) {
            $(e.currentTarget).closest('.ptb_dragged').css('z-index', 100);
        });
    };
    var GetList = function () {
        var $wrapper = $('#ptb-submission-list-form #the-list');
        var $form = $wrapper.closest('form');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {'action': 'ptb_submission_list'},
            beforeSend: function () {
                $form.addClass('ptb-frontend-wait');
            },
            complete: function () {
                $form.removeClass('ptb-frontend-wait');
            },
            success: function (resp) {
                if (resp) {
                    resp = $(resp).find('#the-list').html();
                    $wrapper.html(resp);
                }
            }
        });
    };

    $(document).on('PTB.close_lightbox', function (e, $this) {
        var $wr = $($this).next('#ptb_lightbox_container');
        if ($wr.find('#ptb-submission-edit-form').length > 0) {
            GetList();
        }
    });

    $(document).on('PTB.template_drag_end', function (event, $item, $ui, $type) {
        if ($type !== 'frontend') {
            return false;
        }
        var exclude = new Array('taxonomies', 'custom_image', 'custom_text');
        var $data = $item.data('type');

        if ($.inArray($data, exclude) == -1 && $('#ptb_row_wrapper').find('[data-type="' + $data + '"]').length > 2) {
            if ($item.hasClass('ptb_is_metabox')) {
                var $name = $item.find('input,select,textarea').attr('name');
                if ($name && $('#ptb_row_wrapper').find('[data-type="' + $data + '"] [name="' + $name + '"]').length < 2) {
                    InitSelect($item.find('select.ptb-select'));
                    return false;
                }
            }
            alert(ptb_submission.module + ' ' + $item.find('.ptb_module_name').text());
            $item.remove();
            return false;
        }
        InitSelect($item.find('select.ptb-select'));
    });
    $(document).on('PTB.template_load', function (event, $type) {
        if ($type === 'frontend') {
            InitSelect($('#ptb_row_wrapper .ptb-select'));
            if ($('.ptb_new-themplate').length > 0) {
                var email = $('#ptb_cmb_user_email').clone();
                var username = $('#ptb_cmb_user_name').clone();
                var password = $('#ptb_cmb_user_password').clone();
                $('.ptb_back_col.first .ptb_module_holder')
                        .append(email)
                        .append(username)
                        .append(password).find('.ptb_empty_holder_text').hide();
            
                $('.ptb_back_module_panel #ptb_cmb_title,\n\
                    .ptb_back_module_panel #ptb_cmb_user_email,\n\
                    .ptb_back_module_panel #ptb_cmb_user_name,\n\
                    .ptb_back_module_panel #ptb_cmb_user_password').remove();
            }
        }
    });
    $(document).on('PTB.template_select_grid', function (event, $type, $grid) {
        var $select = $('#ptb_row_wrapper select.ptb-select');
        $select.each(function () {
            $(this).closest('div').html($(this));
        });
        InitSelect($select);
    });
    $(document).ready(function () {
        InitSelect($('select.ptb-select'));
        $('.ptb-submission-tabs a').click(function (e) {
            e.preventDefault();
            if (!$(this).hasClass('nav-tab-active')) {
                $(this).closest('.ptb-submission-tabs').find('.nav-tab-active').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                $('.ptb-submission-tabs- li').hide();
                $($(this).attr('href')).show();
            }
        });
        $('.ptb-submission-tabs a').first().trigger('click');
        $('body').delegate('#ptb-submission-form-save', 'submit', function (e) {
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
        $('body').delegate('.ptb_submission_templates a.ptb_submission_delete', 'click', function (e) {
            e.preventDefault();
            if (confirm(ptb_js.template_delete)) {
                var $form = $(this).closest('form');
                $.ajax({
                    url: this,
                    type: 'POST',
                    beforeSend: function () {
                        $form.addClass('ptb-frontend-wait');
                    },
                    complete: function () {
                        $form.removeClass('ptb-frontend-wait');
                    },
                    success: function (resp) {
                        if (resp) {
                            resp = $(resp).find('#the-list').html();
                            $('#ptb-submission-list-form #the-list').html(resp);
                        }
                    }
                });
            }
        });
        InitSubmissionPosts();
    });
    var InitSubmissionPosts = function () {
        if ($.fn.datepicker) {
            var Ajax = function ($url, $data) {
                var $wrap = $('.wrap');
                $.ajax({
                    url: $url,
                    type: 'POST',
                    data: $data,
                    beforeSend: function () {
                        $wrap.addClass('ptb-frontend-wait');
                    },
                    complete: function () {
                        $wrap.removeClass('ptb-frontend-wait');
                    },
                    success: function (resp) {
                        if (resp) {
                            $('#ptb-submission-posts-wrap').html(resp);
                        }
                    }
                });
            };
            $('.ptb-submission-datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                maxDate: new Date(),
                numberOfMonths: 1,
                onSelect: function (selected) {
                    if ($(this).attr('id') == 'ptb-submission-posts-from') {
                        $('#ptb-submission-posts-to').datepicker("option", "minDate", selected)
                    }
                    else {
                        $('#ptb-submission-posts-from').datepicker("option", "maxDate", selected)
                    }
                }
            });
            if ($.datepicker.regional[ptb_submission.lng]) {
                $.datepicker.setDefaults($.datepicker.regional[ptb_submission.lng]);
            }

            $('#ptb-submission-posts-filter').submit(function (e) {
                e.preventDefault();
                if (!e.isTrigger) {
                    $(this).find('input[name="paged"]').val(0);
                }
                Ajax(ajaxurl, $(this).serialize());
            });

            $('#ptb-submission-posts-wrap').delegate('.sortable a,.sorted a', 'click', function (e) {
                e.preventDefault();
                var $order = getParameterByName(this, 'order'),
                        $orderby = getParameterByName(this, 'orderby');
                $('#ptb-submission-posts-filter input[name="order"]').val($order);
                $('#ptb-submission-posts-filter input[name="orderby"]').val($orderby);
                $('#ptb-submission-posts-filter').trigger('submit');
            });

            $('#ptb-submission-posts-wrap').delegate('.pagination-links a', 'click', function (e) {
                e.preventDefault();
                $('#ptb-submission-posts-filter input[name="paged"]').val(getParameterByName(this, 'paged'));
                $('#ptb-submission-posts-filter').trigger('submit');
            });

            $('#ptb-submission-posts-wrap').delegate('.column-cb input', 'change', function () {
                var $checkboxes = $('#ptb-submission-posts-wrap').find('input[type="checkbox"]');
                $checkboxes.prop('checked', $(this).is(':checked'));
            });

            $('#ptb-submission-posts-wrap').delegate('a.ptb-submission-post-action', 'click', function (e) {
                e.preventDefault();
                if ($(this).hasClass('ptb-submission-post-delete') && !confirm(ptb_js.template_delete)) {
                    return false;
                }
                Ajax(this);
            });
            $('#ptb-submission-posts-form').delegate('input[type="submit"]', 'click', function (e) {
                e.preventDefault();
                var $form = $(this).closest('form'),
                        $action = $(this).closest('.bulkactions').find('select option:selected').val();
                if ($action != -1 && $form.find('input[name="posts[]"]:checked').length > 0) {
                    $form.find('input[name="paged"]').val(0);
                    $form.find('input[name="method"]').val($action);
                    Ajax(ajaxurl, $form.find('input').serialize());
                }
            });
        }
        else if ($('#ptb-submission-users-form').length > 0) {
            $('#ptb-submission-users-form input[type="submit"]').click(function (e) {
                var $form = $(this).closest('form'),
                        $action = $(this).closest('.bulkactions').find('select option:selected').val();
                if ($action == -1 || $form.find('input[name="users[]"]:checked').length == 0) {
                    e.preventDefault();
                }
            });
        }
    };
}(jQuery));