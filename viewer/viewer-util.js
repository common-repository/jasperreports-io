function jrio_call_viewer($viewerId)
{
	$viewerFrame = jQuery("#jrio_viewer_frame");
	$viewerFrame.contents().find("body").html('');
	$viewerFrame.width('100%');

	var $viewer = jQuery($viewerId);
	
	jQuery.ajax({
		url: $viewer.data('jrioUrl'),
		type: 'POST',
		data: JSON.stringify($viewer.data('jrioData')),
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function($data){
			jQuery('#jrio_viewer_frame').attr('src', $viewer.data('viewerUrl') + $data.requestId);
			jQuery('#jrio_viewer_frame').css('width', '100%'); // twentytwenty theme needs this
			jQuery('#jrio_viewer').show();
		},
		error: function(){
            alert('An error occurred trying to execute a report.');           
		}
		});	
}
