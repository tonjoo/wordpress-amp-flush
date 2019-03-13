jQuery(document).ready(function(){
	jQuery("#amp-flush-form").submit(function(e){
		e.preventDefault();

		jQuery("#purge-amp-flush").empty().html('<i class="fa fa-spinner fa-pulse fa-lg fa-fw"></i>');

		var limit = jQuery("#jumlah_post").val();
		var day = jQuery("#amp-flush-start-date").val();
		var str="&limit="+limit+"&day="+day+"&action=amp_flush_ajax_get_post";
		jQuery("#label-flush").empty().html('Purged post from '+day+' until now');
		jQuery.ajax({
			type: "POST",
	        dataType: "html",
	        url: ajaxampflush.ajaxurl,
	        data: str,
	        success: function(data){
	          	jQuery("#amp-flush-result").empty();

	          	var big_data = [];
	           	big_data = jQuery.parseJSON(data);
	           	// console.log(big_data);
	           	var lengthdata = big_data.length;
	           	if(lengthdata < 1 ) {
	           		jQuery("#amp-flush-result").append("<tr><td>Tidak Ada Data </td></tr>");
	           		jQuery("#purge-amp-flush").empty().html('Purge');
	           	}
	         	jQuery.each(big_data, function(i, item) {
	         		var str="post_id="+item+"&action=amp_flush_ajax_process";
	         		jQuery("#purge-amp-flush").empty().html('<i class="fa fa-spinner fa-pulse fa-lg fa-fw"></i>');
	         		jQuery.ajax({
				        dataType: "html",
				        url: ajaxampflush.ajaxurl,
				        data: str,
				        success: function(data){
				     		jQuery("#amp-flush-result").append(data);
				     		if(i== ( lengthdata-1 ) ){
					    		jQuery("#purge-amp-flush").empty().html('Purge');
					    	}
				        },
				        error : function(jqXHR, textStatus, errorThrown) {
							// console.log('error id_post : '+item);
							// console.log("Error: " + jqXHR.statusText);
				        }
			    	});
			    	
			   
				});

	          
	        },
	        error : function(jqXHR, textStatus, errorThrown) {
	         	alert('error get data post');
	        }

    	});
    	
	});
});


jQuery(document).ready(function($){
	$( function() {
		var dateFormat = "yy-mm-dd",
		from = $( "#amp-flush-start-date" ).datepicker({
	        changeMonth: true,
	        "dateFormat" : dateFormat
	    });
	});
});


