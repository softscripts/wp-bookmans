var oTable;
jQuery(document).ready(function($){

var _custom_media = true,
      _orig_send_attachment = wp.media.editor.send.attachment;

  jQuery('.meta_upload').click(function(e) {
    var send_attachment_bkp = wp.media.editor.send.attachment;
    var button = $(this);
    var id = button.attr('id').replace('button_', '');
    _custom_media = true;
    wp.media.editor.send.attachment = function(props, attachment){
      if ( _custom_media ) {
        $("#"+id).val(attachment.url).select();
      } else {
        return _orig_send_attachment.apply( this, [props, attachment] );
      };
    }

    wp.media.editor.open(button);
    return false;
  });


	jQuery('.toggle-show').click(function(){
		var hash = jQuery(this).attr('href');
		var newtext = "";
		var text = jQuery(this).text();
		if(text == 'Choose from past XML files') {
			newtext = "Hide it now";			
		}
		else {
			newtext = "Choose from past XML files";
		}
		jQuery(hash).slideToggle();
		jQuery(this).text(newtext);
		return false;
	});

	jQuery('#previous_xml select').change(function(){
		var value = jQuery(this).val();
		if(value) {
			jQuery('.wb_import_xml').val(value);
		}
	});


	jQuery('#wb-submit').click(function(){
		var stat = 1;
		jQuery('.data-required').each(function() {
			var val = jQuery(this).val();
			if(val == '') {
				jQuery(this).addClass('wb-not-valid');
				stat = 0;
			}
			else {
				jQuery(this).removeClass('wb-not-valid');
			}
		});
	
		if(stat == 1) {
			jQuery('#wb_overlay').fadeIn();
			return true;
		}
		else {
			return false;
		}

	});

	jQuery('.data-required').keyup(function(){
		var val = jQuery(this).val();
			if(val == '') {
			jQuery(this).addClass('wb-not-valid');
		}
		else {
			jQuery(this).removeClass('wb-not-valid');
		}
	});

	jQuery('[data-dismiss="alert"]').click(function(){
		jQuery(this).parent().remove();
	});
	
if($('#dyntable').length > 0) {

	
		$('#commit_form_step').submit( function() {
		var fields =  oTable.$('input').serializeArray();

    $( "#commit_fields" ).val('');
    jQuery.each( fields, function( i, field ) {
			var cons = ",";
			if(i==0) { cons = ""; }
      $( "#commit_fields" ).val(function( index, val ) {
		    return val + cons + field.value;
			});
    });
     return true;
    } );
     
    oTable = $('#dyntable').dataTable({
			"sPaginationType": "full_numbers",
			"aaSortingFixed": [[0,'asc']]
		});

}


});
