(function ($) {
    'use strict';
    
    $(document).ready(function(){
        $( ".ptb_relation_multiply" ).each(function(){
            var $this = $(this),
                $input = $(this).closest('.ptb_relation_autocomplete_wrap').find('input[type="hidden"]');
            $(this).children('ul').sortable({
                items: 'li',
                connectWith:$this,
                placeholder:'ptb_relation_ui_state_highlight',
                cursor: 'move',
                revert: 100,
                sort: function (event, ui) {
                    $('.ptb_relation_ui_state_highlight').height( ui.item.outerHeight(true)).width( ui.item.outerWidth(true));
                },
                stop: function (event, ui) {
                    var $terms = [];
                    $this.find('li').each(function(){
                        $terms.push($(this).data('id'));
                    });
                    $input.val($terms.join( ", " ));
                }
            });
        });
    
        $( ".ptb_relation_autocomplete" ).each(function(){
            var $this = $(this),
                $parent = $(this).closest('.ptb_relation_autocomplete_wrap'),
                $input = $parent.find('input[type="hidden"]'),
                $multiply = $(this).data('multiply'),
                $ajax = $(this).data('ajax');
            $(this).autocomplete({
                minLength: 2,
                source: function( request, response ) {
                    var term = $.trim(request.term);                                                 
                    $.getJSON($ajax,{
                      term: term
                    }, 
                    function( data, status, xhr ) {
                        response( data );
                    } );
                },
                select: function( event, ui ) {
                    if($multiply){
                        var terms = $input.val().split(', ');
                        if($.inArray(ui.item.id.toString(),terms)===-1){
                            terms.push( ui.item.id );
                            $input.val(terms.join( ", " ));
                            $this.val('');
                            $parent.find('ul').append('<li><span class="ptb_relation_term">'+ui.item.value+'</span><span data-id="'+ui.item.id+'" class="ti-close ptb_relation_remove_term"></span></li>');
                        }
                    }
                    else{
                      $input.val(ui.item.id);
                      this.value = ui.item.value;
                    }
                  return false;
                }
              }).focus(function(){ 
                  $(this).autocomplete("search");
            });
        });

        $('.ptb_relation_many').delegate('.ptb_relation_remove_term','click',function(e){
            e.preventDefault();
            if(confirm(ptb_relation.confirm)){
                var $parent = $(this).closest('.ptb_relation_autocomplete_wrap'),
                    $input = $parent.find('input[type="hidden"]'),
                    $val = $.trim($input.val()).split(', '),
                    $index = $val.indexOf($(this).data('id').toString());
                $(this).closest('li').remove();
                if($index!==-1){
                    $val.splice($index,1);
                    $input.val($val.join( ", " ));
                }
            }
        });

        function escRegExp(str) {
            return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
        }


        $.expr[":"].contains = $.expr.createPseudo(function(arg) {
            return function( elem ) {
                return $(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
            };
        });
        $('body').delegate('.ptb_relation_searh_posts','keyup',function(){
            var $text = $.trim($(this).val()),
                $container = $(this).closest('form').find('.ptb_relation_posts_wrap label');
                if($text){
                    $container.hide();
                    $container.filter(':contains(' + escRegExp($text).toUpperCase()  + ')').show();
                }
                else{
                    $container.show();
                }
        });

        $('body').delegate('.ptb_relation_uncheck','click',function(e){
            e.preventDefault();
            $(this).closest('form').find('.ptb_relation_posts_wrap input').removeAttr('checked');
        });

        $('body').delegate('#ptb_relation_select_posts','submit',function(e){
            e.preventDefault();
            var $posts = $(this).serializeArray(),
                $parent = $('.ptb_current_ajax').closest('.ptb_relation_autocomplete_wrap'),
                $input = $parent.find('input[type="hidden"]'),
                $textbox = $parent.find('input.ptb_relation_autocomplete'),
                $multiply = $textbox.data('multiply'),
                terms = $input.val().split(', '),
                $selected = '';
                for(var $i in $posts){
                    var $item = $posts[$i],
                        $id = $item.value.toString();
                    if($.inArray($id,terms)===-1){
                        terms.push( $id );
                        if($multiply){
                            $selected+='<li><span class="ptb_relation_term">'+$('#ptb-related-post-'+$id).closest('label').text()+'</span><span data-id="'+$id+'" class="ti-close ptb_relation_remove_term"></span></li>'; 
                        }
                    }
                }
                if($selected || !$multiply){
                    if($multiply){
                        $parent.find('ul').append($selected); 
                    }
                    else{
                        $textbox.val($.trim($('#ptb-related-post-'+$id).closest('label').text()));
                    }
                    $input.val(terms.join( ", " ));

                }
            $('.ptb_close_lightbox').trigger('click');
        });
    });

}(jQuery));
 