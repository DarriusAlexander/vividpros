(function ($) {
    'use strict';
    $(document).ready(function () {
        InitSubmit();
    });

    var InitSubmit = function () {

        $('#ptb-submission-edit-form').submit(function (e) {
            e.preventDefault();
            var $form = $(this);
            var $success = $form.find('.ptb-submission-success-text');
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                dataType: 'json',
                data: $form.serialize(),
                beforeSend: function () {
                    $form.find('.ptb-submission-required-error').removeClass('ptb-submission-required-error');
                    $form.find('.ptb-submission-error-text').remove();
                    $success.html('');
                    $form.addClass('ptb-submission-wait');
                },
                complete: function () {
                    $form.removeClass('ptb-submission-wait');
                },
                success: function (resp) {
                    if (resp) {
                        if (resp.success) {
                            $success.html(resp.success);
                        }
                        else {
                            for (var $i in resp) {
                                var $input = $form.find('[name^="submission[' + $i + ']"]');
                                $input.addClass('ptb-submission-required-error');
                                $input.closest('td').append('<div class="ptb-submission-error-text">' + resp[$i] + '</div>');
                            }
                        }
                    }
                }
            });
        });
    };

}(jQuery));