<?php defined('SYSPATH') or die('No direct script access.');


class InfoWindow {
	
	public function __construct(){
		
		Event::add("system.pre_controller",array($this,"add"));
		
	}

	public function add(){
		if( Router::$controller == "main" ) {
			plugin::add_stylesheet("infowindow/views/css/infowindow");
			Event::add("ushahidi_action.main_footer",array($this,"register_script"));
		}
	}
	
	public function register_script(){
		plugin::add_javascript("infowindow/media/js/jquery.pagination");
		echo plugin::render("javascript");
		echo "<script src=\"".URL::base()."infowindow\"></script>";
	}
}



new InfoWindow;