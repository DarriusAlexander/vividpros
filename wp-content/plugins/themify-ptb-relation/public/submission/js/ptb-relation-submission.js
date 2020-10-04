(function ($) {
    'use strict';
        var InitAutoComplete = function () {
            function split( val ) {
                return val.split( /,\s*/ );
            }
            function extractLast( term ) {
                return split( term ).pop();
            }
            var $autocomplete = $('.ptb-relation-autocomplete');
            var $cache = [];
            $autocomplete.each(function () {
                var $this = $(this),
                    $post_type = $this.data('post_type'),
                    $multiply = $this.data('multyply');
                $cache[$post_type] = {};
                $this.autocomplete({
                    minLength: 2,
                    source: function (request, response) {
                        var term = request.term;
                        if (term in $cache[$post_type]) {
                            response($cache[$post_type][ term ]);
                            return;
                        }
                        $.ajax({
                            url: ajaxurl,
                            dataType: 'json',
                            type: 'POST',
                            data: {
                                term: request.term,
                                post_type: $post_type,
                                action: 'ptb_relation_submission_posts'
                            },
                            success: function (data) {
                                $cache[$post_type][ term ] = data;
                                response(data);
                            }
                        });
                    },
                    focus: function (event, ui) {
                        $this.autocomplete("search");
                        return false;
                    },
                    open: function () {
                        $("ul.ui-menu").width($(this).innerWidth());
                    },
                    select: function (event, ui) {
                        var $input = $this.next('input');
                        if($multiply){
                            var terms = $input.val().split(', ');
                            if($.inArray(ui.item.id.toString(),terms)===-1){
                                terms.push( ui.item.id );
                                $input.val(terms.join( ", " ));
                                $this.val('');
                                $this.closest('.ptb_relation_multiply').find('ul').append('<li><span class="ptb_relation_term">'+ui.item.label+'</span><span data-id="'+ui.item.id+'" class="ti-close ptb_relation_remove_term"></span></li>');
                            }
                        }
                        else{
                            $this.val(ui.item.label);
                            $input.val(ui.item.id);
                        }
                        return false;
                    }
                });
            });
    };
    $(document).ready(function () {
        InitAutoComplete(); 
        $('.ptb_relation_many').delegate('.ptb_relation_remove_term','click',function(e){
            e.preventDefault();
            if(confirm(ptb_relation.confirm)){
                var $parent = $(this).closest('.ptb_relation_submission_wrap'),
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
       
    });
      

}(jQuery));