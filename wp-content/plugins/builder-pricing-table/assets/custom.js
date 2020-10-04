jQuery(document).ready(function () {
    var i=0;
    jQuery(document).on('mouseover', '#themify_builder_lightbox_parent', function () {
        
        if (i < 1) {
            jQuery('.textarea-feature').after('<br><small>Enter one line per each feature</small>');
            jQuery('.textarea-unfeature').after('<br><small>Unavailable feature list will appear greyed-out</small>');
            i++;
        }
    });
});