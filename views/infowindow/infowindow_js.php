var num_pages = 1,
	paginate = false,
	incidents = [],
	popup;


/* 
	[Incident Content]
	Sets the inner html of the infowindow-content div.
	Various Options:
		1 - tabbed : Tabbed Content Structure using jQuery UI .tabs()
	
---------------------------------------------------------------------------*/
var incident_content = (function(){


	return {
	
		tabbed : (function(incidentData){
		
			var incident = incidentData.incident,
		
				incidentVerified = (incident.incidentverified == 1) ? "<span class=\"r_verified\">verified</span>" : "<span class=\"r_unverified\">unverfied</span>",
	
				media = (incidentData.media),
	
				showMedia = (media.length > 0) ? true : false,
		
				content = 	"<div id=\"infowindow-tabs\">"+
							"<h5 class=\"infowindow_title\">"+
								"<a href=\"/reports/view/"+incident.incidentid+"\">"+incident.incidenttitle+"</a>"+
							"</h5>"+
							"<ul id=\"tabs-nav\"><li><a href=\"#tab1\">Description</a></li>";
			if(showMedia){
				content += "<li><a href=\"#tab2\">Media</a></li>";
			}
				content += "</ul>"+
							"<div id=\"tab1\">"+
								"<ul class=\"incident_items\">"+
						   			"<li>Date\\Time: <time>"+incident.incidentdate+"</time></li>"+
						   			"<li class=\"desc\"><h6>Description:</h6>"+incident.incidentdescription+"</li>"+
						   			"<li class=\"verified\"><strong>Verified:</strong> "+incidentVerified+"</li>"+
						   			"<li class=\"loc\"><strong>Location:</strong> "+incident.locationname+"</li>"+
						   			"<li class=\"lat\"><h6>Latitude:</h6>"+incident.locationlatitude+"</span></li>"+
						   			"<li class=\"lon\"><h6>Longitude:</h6>"+incident.locationlongitude+"</span></li>"+
						   		"</ul>"+
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
					content += "<div id=\"tab2\">"+
			   						"<ul class=\"incident_items\">"+
			   							"<li class=\"media\">"+mediaContent+"</li>"+
			   						"</ul>"+
								"</div>";
				}
			content += "</div><!-- /div.infowindow_tabs -->";
		
	
	
				$("#infowindow-content").empty().html(content);
			
				$("#infowindow-tabs").tabs({selected:0, fx: { opacity: 'toggle' } });
		
		
		})
	
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
	
	$("#infowindow-content").after("<div id=\"pagination-wrap\" />");
	
	$("#pagination-wrap").pagination(num_items,{
		items_per_page : 1, //Show only one item at a time.
		callback : pageCallback, // Callback for every page click,
		num_edge_entries : 1
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

			var content = "<div class=\"infowindow\"><div class=\"infowindow_list\">"+event.feature.attributes.name+"<div style=\"clear:both;\"></div></div>";
		    content += "\n<div class=\"infowindow_meta\"><a href='"+event.feature.attributes.link+"'><?php echo Kohana::lang('ui_main.more_information');?></a><br/><a href='javascript:zoomToSelectedFeature("+ lon + ","+ lat +", 1)'><?php echo Kohana::lang('ui_main.zoom_in');?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='javascript:zoomToSelectedFeature("+ lon + ","+ lat +", -1)'><?php echo Kohana::lang('ui_main.zoom_out');?></a></div>";
			content = content + "</div>";
			
			content = "<div class=\"infowindow\">"+
						"<div id=\"infowindow-content\" class=\"clearingfix\"></div>"+
					  	"<div class=\"infowindow_meta\">"+
					  		"<a href='"+event.feature.attributes.link+"'>"+
					  			"<?php echo Kohana::lang('ui_main.more_information');?>"+
					  		"</a><br/>"+
					  		"<a href='javascript:zoomToSelectedFeature("+ lon + ","+ lat +", 1)'>"+
					  			"<?php echo Kohana::lang('ui_main.zoom_in');?>"+
					  		"</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='javascript:zoomToSelectedFeature("+ lon + ","+ lat +", -1)'><?php echo Kohana::lang('ui_main.zoom_out');?></a>"+
					  	"</div>"+
					  "</div>";	
			
			get_content(event.feature);
			
			if (content.search("<script") != -1)
			{
                content = "Content contained Javascript! Escaped content below.<br />" + content.replace(/</g, "&lt;");
            }
            
            popup = new OpenLayers.Popup.FramedCloud("chicken", 
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
       	    
       	    popup.updateSize();
}
jQuery(function($){

	if($.fn.tabs === undefined){
		$.getScript("<?php echo url::base(); ?>plugins/infowindow/media/js/jquery.ui.widget.min.js");
		$.getScript("<?php echo url::base(); ?>plugins/infowindow/media/js/jquery.ui.tabs.min.js");
	}
});
	