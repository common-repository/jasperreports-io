jQuery('.jrio_report_frame').each(
	function ()
	{
		var $frame = jQuery(this);
		
		jQuery.ajax({
			url: $frame.data('jrioUrl'),
			type: 'POST',
			data: JSON.stringify($frame.data('jrioData')),
			contentType: 'application/json; charset=utf-8',
			dataType: 'json',
			success: function($data){
				$frame.attr('src', $frame.data('viewerUrl') + $data.requestId + '&frameId=' + $frame.attr('id'));
			},
			error: function(){
	            alert('An error occurred trying to execute a report.');           
			}
			});	
	}
);
