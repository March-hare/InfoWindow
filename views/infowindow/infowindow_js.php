var num_pages = 1,
	paginate = false,
	incidents = [],
	popup;


/* 
	[Incident Content]
	Sets the inner html of the iw-content div.
	Various Options:
		1 - tabbed : Tabbed Content Structure using jQuery UI .tabs()
	
---------------------------------------------------------------------------*/
var incident_content = (function(){


	return {
	
		tabbed : (function(incidentData){
		
			var incident = incidentData.incident,
		
				incidentVerified = (incident.incidentverified == 1) ? "<span class=\"r_verified\">verified</span>" : "<span class=\"r_unverified\">unverfied</span>",
	
				media = (incidentData.media),
	
				//TODO: introduce custom form fields
				customForm = this._custom_form_content(incident.incidentid),
				
				showCustomForm = (customForm.length > 0) ? true : false,
				
				showMedia = (media.length > 0) ? true : false,
				
				
				
				content = 	"<div class=\"iw_hd clearingfix\">"+
									"<h6 class=\"iw_title\">"+
										"<a href=\"<?php echo url::base(); ?>reports/view/"+
											incident.incidentid+"\">"+incident.incidenttitle+
										"</a>"+
									"</h6>"+
									incidentVerified+
							"</div>"+
							"<div id=\"iw-tabs\">"+
							"<ul id=\"iw-tabs-nav\" class=\"iw_nob\"><li><a href=\"#tab1\">Description</a></li>";
			
			if(showMedia)
				content += "<li><a href=\"#tab2\">Media</a></li>";
			
			
			if(showCustomForm)
				content += "<li><a href=\"#tab3\">Custom Form</a></li>";
			
			
			
				content += "</ul>"+
							"<div id=\"tab1\" class=\"iw_tab\">"+
								"<div class=\"iw_details report_detail\">"+
									incident.incidentdescription+
								"</div>"+
					   		"</div>";
					   		
				if(showMedia){
					var mediaType = media[0].type,
						mediaContent = "";
					
					
					switch(mediaType){
						case "1":
							mediaContent = "<a target=\"_blank\" href=\""+media[1].thumb_url+"\"><img src=\""+ media[1].thumb_url + "\" alt=\""+incident.incidenttitle+"\" /></a>";
							break;
						default:
							mediaContent = "OTHER";
					}
					content += "<div id=\"tab2\" class=\"iw_tab\">"+
			   						"<ul class=\"iw_nob iw_media\">"+
			   							"<li class=\"media\">"+mediaContent+"</li>"+
			   						"</ul>"+
								"</div>";
				}
				
				if(showCustomForm){
					content += "<div id=\"tab3\" class=\"iw_tab\">"+customForm+"</div>";
				}
			
			content += "<div class=\"iw_ft clearingfix\">"+
							"<ul class=\"iw_nob iw_meta report_detail\">"+
								"<li class=\"iw_lat\">"+incident.locationlatitude+"</li>"+
								"<li class=\"iw_lon\">"+incident.locationlongitude+"</li>"+
								"<li class=\"iw_loc r_location\">"+incident.locationname+"</li>"+
								"<li class=\"iw_date r_date\">"+incident.incidentdate+"</li>"+
							"</ul>"+
						"</div><!-- /div.iw_ft -->"+
					"</div><!-- /div#iw-tabs -->";
			
			
	
	
				$("#iw-content").empty().html(content);
			
				$("#iw-tabs").tabs({selected:0, fx: { opacity: 'toggle' } });
		
		
		}),
		_custom_form_content : (function(incidentid){
			var form = "";
			
			jQuery.ajax({
			
				type : "GET",
				url : "http://jphaiti.local/api?task=customforms&by=fields&id="+incidentid,
				async : false,
				dataType : "json",
				success : function(data){
					
					if(data.error.code == 0){
						var fields = data.payload.customforms.fields,
							len = fields.length;
							
							form = "<ul class=\"iw_cf iw_nob\">";
						
						if(len > 0){ // Do we have custom form fields? 
							for(var i = 0; i < len; i++){
								var field = fields[i],
									value = "",
									valuesCol = field.values,
									meta = field.meta,
									valuesLen = valuesCol.length;
								
								if(valuesLen > 1){
								
									for(var j = 0; j < valuesLen; j++){
										value += valuesCol[j]+", ";
									}//end for
								
									value = value.slice(0,-2); //Remove last character
								
								}else{
									value = valuesCol[0];
								}
								
								form += "<li class=\"custom-form-item\"><strong>"+field.meta.field_name+":</strong> "+value+"</li>";
								
								
							
							}//end for
							
							
							
						}
						form += "</ul>";
						
						
						
						
						
					}//end if(!data.error)
				}
			});
		
			return form;
		
		}) // end _custom_form_content
	
	};



})();



function set_incidents(url){

	jQuery.ajax({
      type: "GET",
      url: url,
      async: false,
      dataType: "json",
      success : function(data){
			incidents = data.payload.incidents;
		}	
  	
  	});


}



//TODO: Figure out how to get complete bounds count.
//TODO: Display Title, description, lat lon, link to report
function set_cluster_content(feature){
	
	
	
	var content = " ",
		link = feature.attributes.link,
		lArr = link.split("&"),
		sw = lArr[1],
		ne = lArr[2],
		url= "<?php echo URL::base(); ?>api?task=incidents&by=bounds&c=0&"+sw+"&"+ne+"&limit="+feature.attributes.count;
	
	set_incidents(url);
	
	paginate = true; //ENABLE PAGINATION
	
}


// TODO 1: ajax call for individual incident information
// Display Title, description, link to report, lat/lon information would be good too. 

function set_single_content(feature){
	
	var link = feature.attributes.link,
		lArr = link.split("/"),
		id = lArr[5],
		url = "<?php echo URL::base();?>api?task=incidents&by=incidentid&id="+id;
		
	set_incidents(url);
		
	paginate = false; //Disable pagination
	
}

function renderSingle(index){
	incident_content.tabbed(incidents[0]); //Only rendering one incident;
}
/* 
	[Event Handler for Paging]
---------------------------------------------------------------------------*/
function pageCallback(index,jq){
	
	incident_content.tabbed(incidents[index]);
	
	popup.updateSize();
	
	return false;
}

/* 
	[Pagination Initializer]
---------------------------------------------------------------------------*/
function initPagination(){
	var num_items = incidents.length;
	
	$("#iw-content").after("<div id=\"pagination-wrap\" />");
	
	$("#pagination-wrap").pagination(num_items,{
		items_per_page : 1, //Show only one item at a time.
		callback : pageCallback, // Callback for every page click,
		num_edge_entries : 1,
		num_display_entries:7
	});
	

}

/* 
	[Get Content]
	Method determines if it should display clustered content or individual content
	
---------------------------------------------------------------------------*/
function get_content(feature){
	
	var cluster = ( ( feature.attributes.count ) && ( feature.attributes.count > 1 ) ) ? true : false;
	
	if( !cluster ){
		set_single_content(feature);
	}else{
		set_cluster_content(feature);
	}
	
}


function onFeatureSelect(event){
	
	 selectedFeature = event;
            // Since KML is user-generated, do naive protection against
            // Javascript.

			zoom_point = event.feature.geometry.getBounds().getCenterLonLat();
			lon = zoom_point.lon;
			lat = zoom_point.lat;

			var content = "<div class=\"iw\">"+
						"<div id=\"iw-content\" class=\"clearingfix\"></div>"+
						  	"<div class=\"iw_ft_meta clearingfix\">"+
						  		"<ul class=\"iw_nob\">"+
							  		"<li class=\"iw_more\">"+
								  		"<a href='"+event.feature.attributes.link+"'>"+
								  			"<?php echo Kohana::lang('ui_main.more_information');?>"+
								  		"</a>"+
							  		"</li>"+
							  		"<li class=\"iw_zi\">"+
								  		"<a href='javascript:zoomToSelectedFeature("+ lon + ","+ lat +", 1)'>"+
								  			"<?php echo Kohana::lang('ui_main.zoom_in');?>"+
								  		"</a>"+
							  		"</li>"+
							  		"<li class=\"iw_zo\">"+
							  			"<a href='javascript:zoomToSelectedFeature("+ lon + ","+ lat +", -1)'>"+
							  				"<?php echo Kohana::lang('ui_main.zoom_out');?>"+
							  			"</a>"+
							  		"</li>"+
						  		"</ul>"+
						  	"</div>"+
					  "</div>";	
			
			get_content(event.feature);
			
			if (content.search("<script") != -1)
			{
                content = "Content contained Javascript! Escaped content below.<br />" + content.replace(/</g, "&lt;");
            }
            
            popup = new OpenLayers.Popup.FramedCloud("iw-bubble", 
				event.feature.geometry.getBounds().getCenterLonLat(),
				new OpenLayers.Size(100,100),
				content,
				null, true, onPopupClose);
           
            event.feature.popup = popup;
            
            
            
            map.addPopup(popup);
                      	
           	
       	
       		if(paginate)
       			initPagination();
       		else
       			renderSingle();
       	    
       	    if(map.getCurrentSize().h < 400){
       	    	jQuery("#iw-content").addClass("small-map");
       	    }
       	    popup.updateSize();
}
jQuery(function($){

	if($.fn.tabs === undefined){
		$.getScript("<?php echo url::base(); ?>plugins/infowindow/media/js/jquery.ui.widget.min.js");
		$.getScript("<?php echo url::base(); ?>plugins/infowindow/media/js/jquery.ui.tabs.min.js");
	}
});
	