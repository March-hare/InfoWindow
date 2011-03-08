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
	
	var category_map = [];

	return {
	
		tabbed : (function(incidentData){
			
			var incident = incidentData.incident,
		
				incidentVerified = (incident.incidentverified == 1) ? "<span class=\"r_verified\">verified</span>" : "<span class=\"r_unverified\">unverfied</span>",
				
				incidentLocationName = (incident.locationname != "") ? incident.locationname : "None",
				
				incidentDate = this.helper._get_date_time(incident.incidentdate),
	
				imageContent = this.helper._image_content(incidentData),
	
				//TODO: introduce custom form fields
				customForm = this.helper._custom_form_content(incident.incidentid),
				
				showCustomForm = (customForm.length > 0) ? true : false,
				
				showImages = (imageContent.length > 0) ? true : false,
				
				$tabs,
				
				content = 	"<div class=\"iw_hd clearingfix\">"+
									"<h6 class=\"iw_title\">"+
										"<a href=\"<?php echo url::base(); ?>reports/view/"+
											incident.incidentid+"\">"+incident.incidenttitle+
										"</a>"+
									"</h6>"+
									incidentVerified+
							"</div>"+
							"<div id=\"iw-tabs\">"+
							"<ul id=\"iw-tabs-nav\" class=\"iw_nob\"><li><a href=\"#tab1\">Details</a></li>";
			
			if(showImages)
				content += "<li><a href=\"#tab2\">Images</a></li>";
			
			
			if(showCustomForm)
				content += "<li><a href=\"#tab3\">Custom Form</a></li>";
			
			
			
				content += "</ul>"+
							"<div id=\"tab1\" class=\"iw_tab\">"+
								"<div class=\"iw_details report_detail\">"+
									this.helper._description_content(incidentData)+
								"</div>"+
					   		"</div>";
					   		
				if(showImages){
					content += "<div id=\"tab2\" class=\"iw_tab\">"+
			   						"<ul class=\"iw_nob iw_media\">"+
			   							imageContent+
			   						"</ul>"+
								"</div>";
				}
				
				if(showCustomForm){
					content += "<div id=\"tab3\" class=\"iw_tab\">"+customForm+"</div>";
				}
			
			content += "<div class=\"iw_ft clearingfix\">"+
							"<ul class=\"iw_nob iw_meta report_detail\">"+
								"<li class=\"iw_loc r_location\">"+incidentLocationName+"</li>"+
								"<li class=\"iw_date r_date\">"+incidentDate+"</li>"+
							"</ul>"+
						"</div><!-- /div.iw_ft -->"+
					"</div><!-- /div#iw-tabs -->";
			
			
			/* 
				[Now we set the lat and lon]
			---------------------------------------------------------------------------*/
			$("#iw-lat").text(incident.locationlatitude);
			$("#iw-lon").text(incident.locationlongitude);
	
				$("#iw-content").empty().html(content);
				
				$tabs = $("#iw-tabs");
				
				$tabs.tabs({selected:0, fx: { opacity: 'toggle' } });
				
				$tabs.find(".iw_media_image").find("a").click(function(e){
				
					var $trigger = $(this),
						title = $trigger.attr("title"),
						url = $trigger.attr("href");
				
					$.colorbox({href : url, title : title});
					
					e.preventDefault();
				});
				
		}),
		
		helper : {
				_get_date_time : (function(iDate){
					var dateArr = iDate.split(" "),
					 	theDate = new Date(dateArr[0]),
					 	theTime = dateArr[1].slice(0,-3);
					 						
					return theTime + " " + theDate.toDateString();
					
				
				}),
				_custom_form_content : (function(incidentid){
					var form = "";
					
					jQuery.ajax({
					
						type : "GET",
						url : "<?php echo url::base(); ?>api?task=customforms&by=fields&id="+incidentid,
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
											type = field.meta.type,
											value = "",
											valuesCol = field.values,
											meta = field.meta,
											valuesLen = valuesCol.length;
										if( (type != 8) && (type != 9) ){ //don't need to show div fields
											if(valuesLen > 1){
											
												for(var j = 0; j < valuesLen; j++){
													value += valuesCol[j]+", ";
												}//end for
											
												value = value.slice(0,-2); //Remove last character
											
											}else{
												value = valuesCol[0];
											}
											
											form += "<li class=\"custom-form-item\"><strong>"+field.meta.name+":</strong> "+value+"</li>";					
										}//end if	
										
									
									}//end for
									
									
									
								}
								form += "</ul>";
								
								
								
								
								
							}//end if(!data.error)
						}
					});
				
					return form;
				
				}), // end _custom_form_content
			
				_image_content : (function(incidentData){
					var media = incidentData.media,
						
						mediaLen = media.length,
						
						item, //Media Item
						
						i = 0, //Happy Looping
						
						content = "", //Final return value
						
						images = []; //Will be pushing in images here
					
					
					//First let's organize or media types
					for(i; i<mediaLen;i++){
						
						item = media[i];
				
						if(item.type == "1"){
						//Media Image
							images.push({
								incidenttitle : incidentData.incident.incidenttitle, 
								thumb: "<?php echo url::base()."media/uploads/"; ?>"+item.thumb, 
								url : "<?php echo url::base()."media/uploads/"; ?>"+item.link
							});
								
						}
						
					}//end for
					
					//Now let us figure out what content to build out.
					if(images.length > 0){
						
						//Build the image html
						
						var len = images.length,
							image;
						
						for(i=0;i<len;i++){
							image = images[i];
							content += "<li class=\"iw_media_image\"><a target=\"_image\" rel=\"iwgroup\" href=\""+image.url+"\" title=\""+image.incidenttitle+"\"><img src=\""+image.thumb+"\" alt=\""+image.incidenttitle+"\" /></a></li>";
						}
						
					}
						
					return content; //Return the final image html
					
				
				}),// end _media_content
				_description_content : (function(incidentData){
					
					
					var media = incidentData.media,
						len = media.length,
						links = [],
						content = "",
						i=0;
					
					
					//List the description
					
						content += "<h2 class=\"iw_media_title iw_desc_title\">Description</h2><div class=\"iw_desc\">"+incidentData.incident.incidentdescription+"</div>";
					
					
					//Get the links
					
						if(len > 0){
							var item;
							
							for(i; i < len; i++){
								item = media[i];
								
								if( ( item.type != "1" ) && ( item.link != undefined ) ){
									
									links.push({
									
										url : item.link
									
									});
								}
								
							}//end for
							
							if(links.length > 0){
								var link;
								len = links.length;
								content += "<h2 class=\"iw_media_title iw_link_title\">Links</h2><ul>";
								
								for(i=0;i<len;i++){
									
									link = links[i];
									content += "<li class=\"iw_media_link\"><a target=\"_web\" href=\""+link.url+"\">"+link.url+"</a></li>";
								
								}//endfor
								content += "</ul>";
							
							}//endif
							
						}
					
						//Get the categories
					
						content += "<h2 class=\"iw_media_title iw_category_title\">Categories</h2>"+
							"<ul class=\"iw_category_list\">"+this._incident_categories(incidentData)+"</ul>";
					
					return content;
				
				}),
				/* 
					Builds the category html
					@param incidentData incidentData object
					@return string
				*/
				_incident_categories : (function(incidentData){
					
					var categories = incidentData.categories,
						len = categories.length,
						i = 0,
						category,
						content = "";
						
					for(i; i < len; i++){
						
						category = this._category(categories[i].category.id);
						
						if(category){
						content += "<li class=\"iw_category\">"+
										"<a title=\""+category.title+"\" class=\"r_category\" href=\"<?php echo url::base()."reports/?c=" ?>"+category.id+"\">";
						if(category.image_thumb!=undefined && category.image_thumb!= null){
							//we have a category thumb
							content += "<span class=\"r_cat-box\">"+
										"<img src=\"<?php echo url::base()."media/uploads/"; ?>"+category.image_thumb+"\" alt=\""+category.title+"\" /></span>";
						}else{				
							content += "<span class=\"r_cat-box\" style=\"background-color:#"+category.color+"\"></span>";
						}
							content += "<span class=\"r_cat-desc\">"+category.title+"</span>"+
										"</a>"+
									"</li>";
						}//endif					
					}
					
					return content;
				}),
				
				/* 
					Returns category object by id 
					@param cat_id Category id
					@return category object
				*/
				_category : (function(cat_id){
					var len = 0, category;
					
					if(category_map.length == 0){
						//We don't have the category_map stored, let's build it first
						jQuery.ajax({
					      type: "GET",
					      url: "<?php echo url::base()."api?task=categories"; ?>",
					      async: false,
					      dataType: "json",
					      success : function(data){
								if(data.error.code == "0"){
								
									var categories = data.payload.categories,
										catLen = categories.length,
										i = 0,
										category,
										key;
									
									for(i; i<catLen;i++){
										
										category = categories[i].category;
										
										category_map.push({id:category.id,category:category});
										
									}
								}
							}	
					  	
					  	});
					}
					
					len = category_map.length;
					
					for(var i = 0; i < len; i++){
						//Determine which category we're searching for
						if(cat_id == category_map[i].id){
							//Category found, set the category variable and break out of the loop
							category = category_map[i].category;
							break;
						}
					}
					
					return category; //Category Object
					
				})
			
			}// end helper
		
	
	}; //end return (for incident_content)



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
		lArr = link.split("view/"),
		id = lArr[1],
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
	
	 selectedFeature = event.feature;
            // Since KML is user-generated, do naive protection against
            // Javascript.

			zoom_point = event.feature.geometry.getBounds().getCenterLonLat();
			lon = zoom_point.lon;
			lat = zoom_point.lat;

			var content = "<div class=\"iw\">"+
						"<div id=\"iw-content\" class=\"clearingfix\"></div>"+
						  	"<div class=\"iw_ft_meta clearingfix\">"+
						  		"<ul id=\"iw_coord\" class=\"iw_nob iw_al\">"+
						  			"<li id=\"iw-lon\">00</li>"+
						  			"<li id=\"iw-lat\">00</li>"+
						  		"</ul>"+
						  		"<ul class=\"iw_nob iw_ar\">"+
							  		"<li class=\"iw_more\">"+
								  		"<a href='"+event.feature.attributes.link+"'>"+
								  			"<?php echo Kohana::lang('ui_main.more_information');?>"+
								  		"</a>"+
							  		"</li>"+
							  		"<li>"+
								  		"<ul class=\"iw_nob\">"+
									  		"<li class=\"iw_zi\">"+
										  		"<a title=\"Zoom In\" class=\"iconic plus-alt\" href='javascript:zoomToSelectedFeature("+ lon + ","+ lat +", 1)'>"+
										  			"<?php echo Kohana::lang('ui_main.zoom_in');?>"+
										  		"</a>"+
									  		"</li>"+
									  		"<li class=\"iw_zo\">"+
									  			"<a title=\"Zoom Out\" class=\"iconic minus-alt\" href='javascript:zoomToSelectedFeature("+ lon + ","+ lat +", -1)'>"+
									  				"<?php echo Kohana::lang('ui_main.zoom_out');?>"+
									  			"</a>"+
									  		"</li>"+
								  		"</ul>"+
							  		"</li>"
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
		//Don't want to step on popular jquery plugins
		$.getScript("<?php echo url::base(); ?>plugins/InfoWindow/media/js/jquery.ui.widget.min.js");
		$.getScript("<?php echo url::base(); ?>plugins/InfoWindow/media/js/jquery.ui.tabs.min.js");
	}	
	if($.fn.colorbox === undefined){
		//Don't want to step on popular jquery plugins
		$.getScript("<?php echo url::base(); ?>plugins/InfoWindow/media/js/jquery.colorbox-min.js");
	}
	
});
	