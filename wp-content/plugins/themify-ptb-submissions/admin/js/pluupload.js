function ptb_create_pluploader(obj) {

    'use strict';

    var $this = obj,
            id1 = $this.attr("id"),
            imgId = id1.replace("plupload-upload-ui", ""),
            $ = jQuery;
    var pconfig = [],
            $form = $this.closest('form'),
            $error = $form.find('.ptb-submission-error');
    pconfig = JSON.parse(JSON.stringify(ptb_plupload_config));
    pconfig["browse_button"] = imgId;
    pconfig["container"] = $this.closest('form').attr('id');
    pconfig["file_data_name"] = $this.data('name') ? $this.data('name') : pconfig["file_data_name"];
    pconfig["multipart_params"] = {'post_type': '', '_nonce': '', 'action': ''};
    if ($this.data('formats')) {
        pconfig['filters'] = $this.data('formats');
    }

    var uploader = new plupload.Uploader(pconfig);

    uploader.bind('Init');
    uploader.init();
    uploader.bind('FilesAdded', function (up, files) {
        if ($this.data('confirm') && !confirm($this.data('confirm'))) {
            return;
        }
        uploader.settings.multipart_params.post_type = $form.find('input[name="post_type"]').val();
        uploader.settings.multipart_params.action = $form.find('input[name="action"]').val();
        uploader.settings.multipart_params._nonce = $form.find('input[name="_nonce"]').val();
        up.refresh();
        up.start();
        $form.addClass('ptb-frontend-wait');
    });
    uploader.bind('Error', function (up, error) {
        if (error.message) {
            $error.text(error.message);
        }
        return;
    });
    uploader.bind('FileUploaded', function (up, file, response) {
        $form.removeClass('ptb-frontend-wait');
        if (response) {
            var json = JSON.parse(response['response']);
            if (json.error) {
                $error.text(json.error);
            }
            else if (json.success) {
                var $loader = $form.find('.ptb-frontend-loader');
                $loader.fadeOut().addClass('done').fadeIn('fast', function () {
                    if (json.redirect) {
                        window.location.href = json.redirect;
                    }
                    else {
                        setTimeout(function () {
                            $loader.fadeOut(2000, function () {
                                $('.ptb_close_lightbox').trigger('click');
                            });
                        }, 1000);
                    }
                });

            }
        }
    });
}
;
 