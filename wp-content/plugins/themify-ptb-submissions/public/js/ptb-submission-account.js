(function ($) {
    'use strict';
    var InitPagination = function () {
        $('.ptb-submission-account-page').delegate('.ptb_submission_pagenav a', 'click', function (e) {
            e.preventDefault();
            var $value = getParameterByName($(this).attr('href'), 'paged');
            if (!$value) {
                $value = 1;
            }
            var $form = $(this).closest('form');
            $form.find('input[type="hidden"][name="paged"]').val(parseInt($value)).trigger('submit');
        });
    };
    function getParameterByName(url, name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                results = regex.exec(url);
        return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    }

    var InitDelete = function () {

        $('.ptb-submission-account-page').delegate('a.ptb_submission_post_delete', 'click', function (e) {
            e.preventDefault();
            if (!confirm(ptb_submission.delete)) {
                return false;
            }
            var $wrapper = $(this).closest('.ptb-submission-account-page'),
                    $this = $(this);
            $.ajax({
                url: this,
                type: 'POST',
                data: {'action': 'ptb_account_remove_post'},
                beforeSend: function () {
                    $wrapper.addClass('ptb-submission-wait');
                },
                complete: function () {
                    $wrapper.removeClass('ptb-submission-wait');
                },
                success: function (resp) {
                    if (resp && resp == '1') {
                        var $table = $this.closest('table');
                        $this.closest('tr').remove();
                        if ($table.find('tr').length <= 1) {
                            $table.closest('form').find('input[type="hidden"][name="paged"]').val(1).trigger('submit');
                        }
                    }
                }
            });
        });
    };

    var InitDatePicker = function () {
        $('.ptb_submission_datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            maxDate: new Date(),
            numberOfMonths: 1,
            onSelect: function (selected) {
                if ($(this).attr('id') == 'ptb_submission_date_from') {
                    $('#ptb_submission_date_to').datepicker("option", "minDate", selected)
                }
                else {
                    $('#ptb_submission_date_from').datepicker("option", "maxDate", selected)
                }
            }
        });
        if ($.datepicker.regional[ptb_submission.lng]) {
            $.datepicker.setDefaults($.datepicker.regional[ptb_submission.lng]);
        }
    };

    var InitFilter = function () {

        $('.ptb_submission_account_filter').submit(function (e) {
            e.preventDefault();
            var $form = $(this);
            if (!e.isTrigger) {
                $form.find('input[type="hidden"][name="paged"]').val(1);
            }
            var $wrapper = $form.closest('.ptb-submission-account-page');
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function () {
                    $wrapper.addClass('ptb-submission-wait');
                },
                complete: function () {
                    $wrapper.removeClass('ptb-submission-wait');
                },
                success: function (resp) {
                    if (resp && resp != '0') {
                        $wrapper.find('.ptb_submission_table_wrapper').html(resp);
                    }
                }
            });
        });
    };

    $(document).ready(function () {
        InitPagination();
        InitDelete();
        InitDatePicker();
        InitFilter();
    });

}(jQuery));