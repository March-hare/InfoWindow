<?php defined('SYSPATH') or die('No direct script access.');


class InfoWindow {
	
	public function __construct(){
		
		Event::add("system.pre_controller",array($this,"add"));
		
	}

	public function add(){
		if( Router::$controller == "main" ) {
			plugin::add_stylesheet("InfoWindow/views/css/infowindow");
			Event::add("ushahidi_action.main_footer",array($this,"register_script"));
		}
	}
	
	public function register_script(){
		plugin::add_javascript("InfoWindow/media/js/jquery.pagination");
		echo plugin::render("javascript");
		echo "<script src=\"".url::base()."infowindow\"></script>";
	}
}



new InfoWindow;