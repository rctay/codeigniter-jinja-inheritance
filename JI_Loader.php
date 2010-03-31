<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class JI_Loader extends CI_Loader {
	/**
	 * Since there aren't any hooks for loading views, we decorate view().
	 **/
	function view($view, $vars = array(), $return = FALSE) {
		return parent::view($view, $vars, $return);
	}
}

?>
