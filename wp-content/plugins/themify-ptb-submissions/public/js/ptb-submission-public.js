(function ($) {
    'use strict';
    var filesList = {},
            paramNames = {};
    var InitLanguageTabs = function () {
        $(document).on('click', '.ptb_language_tabs li', function (e) {
            e.preventDefault();
            if (!$(this).hasClass('ptb_active_tab_lng')) {
                var $tab = $(this).closest('ul');
                var $fields = $tab.next('ul');
                $tab.find('.ptb_active_tab_lng').removeClass('ptb_active_tab_lng');
                $(this).addClass('ptb_active_tab_lng');
                $fields.find('.ptb_active_lng').removeClass('ptb_active_lng');
                $fields.find('li').eq($(this).index()).addClass('ptb_active_lng');
            }
        });
    };

    function bytesToSize(bytes) {
        var sizes = ['n/a', 'B', 'KB', 'MB'];
        var i = +Math.floor(Math.log(bytes) / Math.log(1024));
        return  (bytes / Math.pow(1024, i)).toFixed(i ? 1 : 0) + ' ' + sizes[ isNaN(bytes) ? 0 : i + 1 ];
    }
    var InitFileUpload = function ($files) {

        $files.each(function () {
            var $parent_node = $(this).closest('.ptb-submission-text-option');
            $parent_node = $parent_node.length > 0 ? $parent_node : $(this).closest('.ptb_module');
            var $form = $(this).closest('form'),
                    $allow_extension = $(this).data('extension') ? new RegExp('(\\.|\/)(' + $(this).data('extension') + ')$', 'i') : /(\.|\/)(gif|jpe?g|png)$/i,
                    $max_file_size = $(this).data('size'),
                    $width = $(this).data('width'),
                    $height = $(this).data('height'),
                    node = $parent_node.find('.ptb-submission-priview'),
                    $this = $(this);
            $(this).fileupload({
                url: $form.attr('action'),
                type: 'POST',
                acceptFileTypes: $allow_extension,
                maxFileSize: $max_file_size,
                dataType: 'json',
                singleFileUploads: true,
                autoUpload: false,
                disableImageResize: /Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent),
                previewMaxWidth: $width ? $width : 100,
                previewMaxHeight: $height ? $height : 100,
                previewCrop: true
            }).on('fileuploadprocessalways', function (e, data) {
                var index = data.index,
                        file = data.files[index],
                        $fname = $('.ptb-submission-upload-btn[for="' + $this.attr('id') + '"]');
                node.next('.ptb-submission-file-danger').remove();
                node.hide('blind', 500, function () {
                    if (file.error) {
                        $fname.html($this.data('label'));
                        $(this).after($('<span class="ptb-submission-file-danger ptb-submission-error-text"/>').text(file.error));
                    }
                    else {
                        if (file.preview) {
                            $(this).html(file.preview);
                            if ($(this).closest('.ptb-submission-multi-text').length === 0 && $(this).closest('.ptb-submission-module-req').length === 0) {
                                $(this).append('<span data-slug="' + $this.attr('id') + '" class="fa fa-minus-circle ptb-submission-file-remove"></span>');
                            }
                            $(this).append('<span class="ptb-submission-file-name">' + file.name + '</span>')
                                    .append('<span class="ptb-submission-file-size">' + bytesToSize(file.size) + '</span>').width(data.previewMaxWidth).show('blind', 500);
                        }
                        else {
                            $(this).html('');
                        }

                        var $f = file.name.length > 12 ? file.name.substring(0, 12) + '...' : file.name;
                        $fname.html('<span>' + $f + '</span>');
                    }
                });
                if (!file.error) {
                    for (var i = 0; i < data.files.length; i++) {
                        var $key = $(this).attr('id');
                        filesList[$key] = data.files[i];
                        paramNames[$key] = e.delegateTarget.name;
                    }
                }
            });

        });
    };

    var InitFileRemove = function () {
        $('.ptb_module').delegate('.ptb-submission-file-remove', 'click', function (e) {
            e.preventDefault();
            var $preview = $(this).closest('.ptb-submission-priview');
            var $fwrap = $preview.find('.ptb-submission-file-wrap'),
                    $slug = $(this).data('slug');
            FileRemove($fwrap, $slug, $preview);
        });
    };

    var FileRemove = function ($fwrap, $key, $preview) {
        $preview.hide('blind', 500, function () {
            $(this).empty();
        });
        delete paramNames[$key];
        delete filesList[$key];
        $fwrap.html($fwrap.html());
        var $label = $('.ptb-submission-upload-btn[for="' + $key + '"');
        $label.html($label.data('label'));
        InitFileUpload($fwrap.find('.ptb-submission-file'));
    };

    var InitAutoComplete = function () {
        var $autocomplete = $('.ptb-submission-autocomplete');
        var $cache = [];
        $autocomplete.each(function () {
            var $slug = $(this).data('slug'),
                    $this = $(this),
                    $post_type = $(this).data('post_type');
            $cache[$slug] = {};
            $(this).autocomplete({
                minLength: 2,
                source: function (request, response) {
                    var term = request.term;
                    if (term in $cache[$slug]) {
                        response($cache[$slug][ term ]);
                        return;
                    }
                    $.ajax({
                        url: ajaxurl,
                        dataType: 'json',
                        type: 'POST',
                        data: {
                            term: request.term,
                            slug: $slug,
                            post_type: $post_type,
                            action: 'ptb_submission_terms'
                        },
                        success: function (data) {
                            $cache[$slug][ term ] = data;
                            response(data);
                        }
                    });
                },
                focus: function (event, ui) {
                    event.preventDefault();
                },
                open: function () {
                    $("ul.ui-menu").width($(this).innerWidth());
                },
                select: function (event, ui) {
                    $this.val(ui.item.label);
                    $this.next('input').val(ui.item.value);
                    return false;
                }
            });
        });
    };
  
    var InitPostTag = function(){
        function split( val ) {
            return val.split( /,\s*/ );
        }
        function extractLast( term ) {
            return split( term ).pop();
        }
        var $post_tags = $('.ptb_post_tag');
        if($post_tags.length>0){
            var $cache = [];
            $post_tags.each(function(){
                var $inputs = $(this).find('input[type="text"]'),
                    $post_type = $(this).closest('form').children('input[name="post_type"]').val(),
                    $regexp = /.*?\[.*?\]\[(.+?)\]$/;
                $inputs.each(function(){
                    var $this = $(this),
                        $slug = $this.prop('name').match($regexp);
                       
                        if($slug.length===0 || !$slug[1]){
                            return false;
                        }
                        $slug = $slug[1];
                         $cache[$slug] = {};
                    $(this).autocomplete({
                        minLength: 2,
                        source: function (request, response) {
                            var term = request.term;
                            if (term in $cache[$slug]) {
                                response($cache[$slug][ term ]);
                                return;
                            }
                            $.ajax({
                                url: ajaxurl,
                                dataType: 'json',
                                type: 'POST',
                                data: {
                                    term: extractLast(request.term),
                                    slug: $slug,
                                    post_type: $post_type,
                                    action: 'ptb_submission_tag_terms'
                                },
                                success: function (data) {
                                    $cache[$slug][ term ] = data;
                                    response(data);
                                }
                            });
                        },
                        focus: function (event, ui) {
                            event.preventDefault();
                        },
                        open: function () {
                            $("ul.ui-menu").width($(this).innerWidth());
                        },
                        select: function (event, ui) {
                            var terms = split( this.value );
                            if($.inArray(ui.item.label,terms)===-1){
                                terms.pop();
                                terms.push( ui.item.label );
                                terms.push( "" );
                                this.value = terms.join( ", " );  console.log(terms)
                            }
                            else{
                                var index = terms.indexOf(extractLast(this.value));
                                terms[index] = null;
                                this.value =  terms.join( ", " );
                            }
                            return false;
                         
                        }
                    });
                });
               
            });
        }
    };

    var InitMultiText = function () {
        var $multitext = $('.ptb-submission-text-option input,.ptb-submission-text-option textarea');
        $multitext.each(function () {
            var $name = $(this).attr('name');
            if ($name) {
                $(this).attr('name', $name + '[]');
            }
        });
        var $fields = $('.ptb-submission-multi-text li');
        $fields.each(function () {
            if ($(this).find('.ptb-submission-priview').length > 0) {
                var $id = MakeUniqueId();
                $(this).find('label').prop('for', $id);
                $(this).find('input[type="file"]').prop('id', $id);
            }
        });
        $fields = $multitext = null;
        $('body').delegate('.ptb-submission-option-add', 'click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $module = $(this).closest('.ptb-submission-multi-text'),
                $max =  $module.data('max'),
                $length =  $module.find('.ptb-submission-text-option').length;
                if($max &&  $length>=$max){
                    return;
                }
            var $li = $module.find('.ptb-submission-text-option').first().clone(),
                $wrapper = $module.children('ul');
            $li.find('input,textarea').val('');
            $li.find('.ptb-submission-required-error').removeClass('ptb-submission-required-error');
            var $preview = $li.find('.ptb-submission-priview');
            $.event.trigger("ptb_submission_before_option_add", $li);
            if ($preview.length > 0) {
                var $id = MakeUniqueId();
                $li.find('.ptb-submission-error-text').remove();
                var $label = $li.find('label');
                $label.prop('for', $id);
                $label.html($label.data('label'));
                $li.find('input[type="file"]').prop('id', $id);
                $preview.empty().hide();
            }
            $li.appendTo($wrapper).hide().show('blind', 500);
            if ($preview.length > 0) {
                InitFileUpload($('#' + $id));
            }
            $.event.trigger("ptb_submission_after_option_add", $li);
            $wrapper.sortable("refresh");
            if($max &&  ($length+1)>=$max){
                $(this).fadeOut();
            }
        });
        $('body').delegate('.ptb-submission-remove', 'click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $option = $(this).closest('.ptb-submission-text-option'),
                $preview = $option.find('.ptb-submission-priview');
            if ($preview.length > 0) {
                var $fwrap = $preview.closest('.ptb-submission-text-option').find('.ptb-submission-file-wrap'),
                        $slug = $fwrap.find('input').attr('id');
                FileRemove($fwrap, $slug, $preview);
                if ($option.closest('ul').children('li').length == 1) {
                    $option.find('input').val('');
                    return false;
                }
            }
            var $wrapper = $(this).closest('.ptb-submission-multi-text'),
                $max =  $wrapper.data('max'),
                $length = $wrapper.find('.ptb-submission-text-option').length;
                if($max && ($length-1)<$max){
                    $wrapper.find('.ptb-submission-option-add').fadeIn();
                }
            $option.hide('blind', 500, function () {
                $(this).remove();
            });
        });
        InitSortable();
    };

    var MakeUniqueId = function () {
        return 'ptb_submission_' + Math.random().toString(36).substr(2, 9);
    };

    var InitSelect = function () {
        $('.ptb-select').chosen({
            disable_search_threshold: 7,
            search_contains: true,
            width: "100%"
        });
    };

    var InitCaptchaRefresh = function () {

        $('.ptb-submission-captcha-refresh').click(function (e) {
            e.preventDefault();
            var $form = $(this).closest('form');
            $form.addClass('ptb-submission-wait');
            var $img = $(this).closest('table').find('img');
            $img.attr('src', $img.attr('src')).load(function () {
                $form.removeClass('ptb-submission-wait');
            });
        });
    };

    var InitSubmit = function () {
        $('.ptb-submission-form').on('submit', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $validate = true;
            var $req = $form.find('.ptb-submission-module-req'),
                    $error = $form.find('.ptb-submission-form-error');
            $form.find('.ptb-submission-required-error,.ptb-submission-label-required').removeClass('ptb-submission-required-error ptb-submission-label-required');
            $form.find('.switch-html').trigger('click');
            $req.each(function () {
                var $label = $(this);
                var $input = $label.find('input,select,textarea');
                $input.each(function () {
                    var $er = false,
                            $name = $(this).attr('name');
                    if (!$name) {
                        return false;
                    }
                    if ($(this).is(':checkbox') || $(this).is(':radio')) {
                        if ($label.find('[name^="' + $name + '"]:checked').length > 0) {
                            return false;
                        }
                        $er = true;
                    }
                    else if (!$.trim($(this).val()) && $(this).closest('.chosen-container').length == 0) {
                        if ($.trim($(this).closest('.ptb_module').find('.ptb-submission-priview').html())) {
                            return false;
                        }
                        $er = true;
                    }
                    var $type = $(this).closest('.ptb_module').data('type');
                    var $check = $.event.trigger("ptb_submission_validate_" + $type, [$(this), filesList]);
                    if (typeof $check !== 'undefined') {
                        $er = $check;
                    }
                    if ($er) {
                        $(this).addClass('ptb-submission-required-error');
                        $label.addClass('ptb-submission-label-required');
                        $validate = false;
                    }
                });
            });
            if ($validate) {

                $error.html('');
                if (Object.keys(filesList).length > 0) {
                    var $filesList = [],
                            $paramNames = [];
                    var $multi = $('.ptb-submission-multi-text');
                    if ($multi.length > 0) {
                        var $li = $multi.find('>ul>li');
                        $li.each(function () {
                            var $inputs = $(this).find('input,textarea');
                            var $index = $(this).index();
                            $inputs.each(function () {
                                var $name = $(this).attr('name');
                                if ($name) {
                                    $name = $name.replace(/\[[0-9]*\]/i, '[' + $index + ']');
                                    $(this).attr('name', $name);
                                }
                            });
                        });
                    }
                    for (var $f in filesList) {
                        if ($('#' + $f).closest('.ptb-submission-multi-text').length > 0) {
                            var $index = $('#' + $f).closest('li').index();
                            paramNames[$f] = paramNames[$f].replace(/\[[0-9]*\]/i, '[' + $index + ']');
                        }
                        $paramNames.push(paramNames[$f]);
                        $filesList.push(filesList[$f]);
                    }
                    $form.addClass('ptb-submission-wait');
                    $('.ptb-submission-file', $form).fileupload('send',
                            {files: $filesList,
                                paramName: $paramNames,
                            }).complete(function () {
                        $form.removeClass('ptb-submission-wait');
                    }).success(function (resp) {
                        SubmitResult(resp, $form);
                    });
                }
                else {
                    $.ajax({
                        url: $form.attr('action'),
                        type: 'POST',
                        dataType: 'json',
                        data: $form.serialize(),
                        beforeSend: function () {
                            $form.addClass('ptb-submission-wait');
                        },
                        complete: function () {
                            $form.removeClass('ptb-submission-wait');
                        },
                        success: function (resp) {
                            SubmitResult(resp, $form);
                        }
                    });
                }
            }
            else {
                $error.html(ptb_submission.errors);
            }
        });
    };

    var SubmitResult = function (resp, $form) {
        var $errors = $form.find('.ptb-submission-errors'),
                $error = $form.find('.ptb-submission-form-error');
        $errors.html('');
        $form.find('.ptb-submission-error-text').remove();
        if (resp) {
            if (resp.success) {
                if (resp.r) {
                    window.location.href = resp.success;
                }
                else {
                    var $href = window.location.search ? window.location.href + '&' : window.location.href + '?';
                    $href += 'nonce=' + resp.nonce + '&form_id=' + resp.form_id;
                    window.location.href = $href;
                }
            }
            else if (resp.fee) {
                window.location.href = resp.fee;
            }
            else {

                for (var $i in resp) {

                    if ($i == 'captcha') {
                        $form.find('.ptb-submission-captcha-refresh').trigger('click');
                    }
                    if (typeof resp[$i] !== 'object') {
                        var $message = resp[$i];
                        resp[$i] = [];
                        resp[$i][0] = 1;
                    }
                    for (var $j in resp[$i]) {
                        var $input = $form.find('[name^="submission[' + $i + ']"]'),
                                $text = $j != 0 ? resp[$i][$j] : $message;

                        if ($input.length == 0) {
                            $input = $form.find('[name^="' + $i + '"]');
                        }
                        if ($input.length > 0) {
                            $input.addClass('ptb-submission-required-error');
                            $input.closest('.ptb_module').addClass('ptb-submission-label-required');
                            $input.first().closest('.ptb_back_active_module_input').append('<div class="ptb-submission-error-text">' + $text + '</div>');
                        }
                        else {
                            $errors.append('<div>' + $text + '</div>');
                        }
                    }
                }
                $error.html(ptb_submission.errors);
            }
        }
    };

    var InitSortable = function () {
        $('.ptb-submission-multi-text>ul').sortable({
            placeholder: "ui-state-highlight"
        });
    };

    var InitNumberValidate = function () {
        $('.ptb_number input').on('focusout', function () {
            var $min = parseFloat($(this).prop('min')),
                    $max = parseFloat($(this).prop('max')),
                    $val = parseFloat($(this).val());
            if ($val < $min) {
                $(this).val($min);
            }
            else if ($val > $max) {
                $(this).val($max);
            }
            if ($(this).hasClass('ptb_number_min')) {
                $max = parseFloat($(this).closest('.ptb_module').find('.ptb_number_max').val());
                if ($max <= $val) {
                    $(this).val($max - 1);
                }
            }
            else if ($(this).hasClass('ptb_number_max')) {
                $min = parseFloat($(this).closest('.ptb_module').find('.ptb_number_min').val());
                if ($min >= $val) {
                    $(this).val($min + 1);
                }
            }
        });
    };

    var InitAddTax = function () {
        $('form.add_temp_tax').submit(function (e) {
            e.preventDefault();
            var $this = $(this),
                    $input = $this.find('input[type="text"]'),
                    $error = false,
                    $multi = $(this).find('.ptb_language_tabs').length > 0;
            $this.find('.ptb-submission-error-text').remove();
            $this.find('.ptb-submission-required-error').removeClass('ptb-submission-required-error');
            $input.each(function () {
                if (!$.trim($(this).val())) {
                    if ($multi) {
                        var $wrapper = $(this).closest('.ptb_back_active_module_input');
                        if ($wrapper.find('.ptb-submission-error-text').length === 0) {
                            $wrapper.append('<span class="ptb-submission-error-text">' + ptb_submission.tax_all_errors + '<span>');
                        }
                    }
                    $error = true;
                    $(this).attr('placeholder', ptb_submission.tax_error).addClass('ptb-submission-required-error');
                    $(this).addClass('ptb-submission-required-error');
                }
            });
            if ($error) {
                return false;
            }
            $.ajax({
                url: $this.prop('action'),
                data: $this.serialize(),
                type: 'POST',
                dataType: 'json',
                beforeSend: function () {
                    $this.addClass('ptb-submission-wait');
                },
                complete: function () {
                    $this.removeClass('ptb-submission-wait');
                    $('#lightcase-overlay').trigger('click');
                },
                success: function (resp) {
                    if (resp && resp.slug) {
                        var $current = $('a#ptb_submission_lightbox_' + resp.slug),
                                $input = $current.closest('.ptb_back_active_module_input'),
                                $is_select = $input.find('select').length > 0,
                                $checkbox = !$is_select && ($input.find('input:radio').length > 0 || $input.find('input:checkbox').length > 0),
                                $autocomplete = !$is_select && !$checkbox && $input.find('.ptb-submission-autocomplete').length > 0;

                        if (resp.exists) {
                            for (var $i in resp.exists) {
                                if ($is_select) {
                                    var $select = $input.find('select');
                                    if (!$select.prop('multiple')) {
                                        $select.find('option:selected').prop('selected', false);
                                    }
                                    $select.find('option[value="' + $i + '"]').prop('selected', true);
                                }
                                else if ($checkbox) {
                                    $input.find('#ptb_tax_' + $i).prop('checked', true);
                                }
                                else if ($autocomplete) {
                                    $input.find('input[type="text"]').val(resp.exists[$i].title);
                                    $input.find('input[type="text"]').next('input').val($i);
                                }
                            }
                        }
                        if (resp.add) {

                            var $add = $.unique(resp.add[resp.lng]);
                            var $type = $checkbox ? ($input.find('input:radio').length > 0 ? 'radio' : 'checkbox') : false;
                            for (var $i in $add) {
                                if ($is_select) {
                                    var $el = $input.find('option[value="' + $add[$i].slug + '"]');
                                    if (!$input.find('select').prop('multiple')) {
                                        $input.find('option').removeAttr('selected');
                                    }
                                    if ($el.length > 0) {
                                        $el.prop('selected', true);
                                    }
                                    else {
                                        $input.find('select').prepend('<option value="' + $add[$i].slug + '" selected="selected">' + $add[$i].value + '</option>');
                                    }
                                }
                                else if ($checkbox) {

                                    if ($type === 'radio') {
                                        $input.find('input').prop('checked', false);
                                    }
                                    var $el = $input.find('input[value="' + $add[$i].slug + '"]');
                                    if ($el.length > 0) {
                                        $el.find('input').prop('checked', 'checked');
                                    }
                                    else {
                                        $input.prepend('<label class="ptb-submission-new-item"><input name="' + $input.find('input[name]').first().prop('name') + '" value="' + $add[$i].slug + '" type="' + $type + '" checked="checked" /><span>' + $add[$i].value + '</span></label>');
                                    }
                                }
                                else if ($autocomplete) {
                                    $input.find('input[type="text"]').val($add[$i].value);
                                }
                            }
                            var $new_value = {};
                            for (var $m in resp.add) {
                                $new_value[$m] = [];
                                for (var $n in resp.add[$m]) {
                                    $new_value[$m].push(resp.add[$m][$n].value);
                                }
                            }

                            var $hidden = $current.next('input'),
                                    $value = $hidden.val(),
                                    $value = $value ? JSON.parse(window.atob($value)) : [];
                            for (var $i in $value) {
                                for (var $j in $value[$i]) {
                                    $new_value[$i].push($value[$i][$j]);
                                }
                            }
                            $hidden.val(window.btoa(JSON.stringify($new_value)));
                        }
                        if ($is_select) {
                            $input.find('select').trigger("chosen:updated").next('.chosen-container').find('a,ul').effect("highlight", {}, 2000);
                        }
                        else {
                            $('.ptb-submission-new-item').effect("highlight", {}, 2000).removeClass('ptb-submission-new-item');
                        }
                    }
                }
            });
        });
    };

    $(document).on('ptb_loaded', function () {
        if ($('.ptb_is_single_lightbox .ptb_lightbox_add_tax').length > 0) {
            var $multitext = $('.ptb_is_single_lightbox').removeClass('ptb_is_single_lightbox').addClass('ptb_lightbox_add_tax_wrapper').find('input[type="text"]');
            $multitext.each(function () {
                var $name = $(this).attr('name');
                if ($name) {
                    $(this).attr('name', $name + '[]');
                }
            });
            InitSortable();
            InitAddTax();
        }
    });

    $(document).ready(function () {
        InitLanguageTabs();
        InitFileUpload($('input[type="file"].ptb-submission-file'));
        InitFileRemove();
        InitAutoComplete();
        InitPostTag();
        InitSelect();
        InitMultiText();
        InitCaptchaRefresh();
        InitNumberValidate();
        InitSubmit();
        $('form.ptb-submission-form').each(function () {
            var $preview = $(this).find('.ptb-submission-priview');
            $preview.each(function () {
                if ($(this).children('*').length > 0) {
                    $(this).show();
                }
            });
        });
    });

}(jQuery));