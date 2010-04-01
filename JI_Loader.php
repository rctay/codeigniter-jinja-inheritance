<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * A DAG implementation.
 */
class JI_Block {
	var $_name;
	var $_parent;
	var $_blocks;

	var $content;

	function __construct($name, $parent = NULL) {
		$this->_name = $name;
		$this->_blocks = array();
		$this->content = '';

		if ($parent !== NULL) {
			$parent->add_block($this);
			$this->_parent = $parent;
		}
	}

	function get_block($name) {
		foreach ($this->_blocks as $id=>$child) {
			if ($child->_name === $name) {
				return $child;
			} else {
				$block = $child->get_block($name);
				if ($block !== NULL) {
					return $block;
				}
			}
		}
		return NULL;
	}

	function has_block_named($name) {
		return $this->get_block($name) !== NULL;
	}

	function has_block($block) {
		return $this->get_block($block->_name) !== NULL;
	}

	function add_block($block) {
		if ($this->has_block($block)) {
			return 1;
		}
		$this->_blocks[] = $block;
	}
}

/**
 * An orphaned DAG - only can have children.
 */
class Head_JI_Block extends JI_Block {
	function __construct() {
		parent::__construct(NULL);
	}
}

/**
 * A linked list implementation - always 1 edge for each vertex.
 */
class JI_View {
	var $_name;
	var $_blocks;
	var $_prev;
	var $_next;

	// Canonicalizes a view's name; currently not used
	// Code taken from Loader::_ci_load().
	function _canonical_view_name($_ci_view) {
		$_ci_ext = pathinfo($_ci_view, PATHINFO_EXTENSION);
		$_ci_file = ($_ci_ext == '') ? $_ci_view.EXT : $_ci_view;

		return $_ci_file;
	}

	// Check that $name is unique with _canonical_view_name().
	function __construct($name) {
		$this->_name = $name;
		$this->_prev = NULL;
		$this->_next = NULL;
		$this->_blocks = new Head_JI_Block();
	}

	function chain_view($new_view) {
		$new_view->_prev = $this;	// a reference already
		$this->_next = $new_view;
	}

	function has_child() {
		return $this->_next !== NULL;
	}
}

class JI_Loader extends CI_Loader {
	var $_views;
	var $_current_view;
	var $_current_block;

	/**
	 * Since there aren't any hooks for loading views, we decorate view().
	 **/
	function view($view, $vars = array(), $return = FALSE) {
		// init our stuff.
		$this->_views =& new JI_View($view);
		$this->_current_view = $this->_views;
		$this->_current_block = $this->_current_view->_blocks;

		while ($this->_current_view !== NULL) {
			$this->_current_block = $this->_current_view->_blocks;
			$str = parent::view($this->_current_view->_name, $vars,
				// for the last run (base view/template), echo, unless
				// user wants a string returned.
				$this->_current_view->_next!==NULL || $return
			);

			$this->_current_view = $this->_current_view->_next;
		}

		// Return the last run - the base template.
		if ($return) {
			return $str;
		}
	}

	//---------------------------------------------------------------------
	//                     Methods to be used in views
	//---------------------------------------------------------------------

	function extends_view($baser_view) {
		if ($this->_current_view->has_child()) {
			show_error("You cannot place more than one &lt;?php extends_view() ?&gt; in the same view.");
		}

		// chain for use later.
		$new_view = new JI_View($baser_view);
		$this->_current_view->chain_view($new_view);
	}

	function start_block($block_name) {
		if ($this->_current_block->has_block_named($block_name)) {
			show_error("A block with the name {$block_name} has already been specified.");
		}

		$new_block =& new JI_Block($block_name, $this->_current_block);
		$this->_current_block->add_block($new_block);
		$this->_current_block = $new_block;

		ob_start();
	}

	function end_block($block_name = NULL) {
		if ($block_name !== NULL && $block_name !== $this->_current_block->_name) {
			show_error("Incorrect block name specified to &lt;?php end_block() ?&gt;.");
		}

		$this->_current_block->content = ob_get_clean();

		if (!$this->_current_view->has_child()) {
			// We're the base template - collate output from all
			// parents, moving upwards from the base view
			$curr = $this->_current_view->_prev;
			while ($curr !== NULL) {
				$parent_block = $curr->_blocks->get_block($this->_current_block->_name);

				// if the parent template didn't override a
				// block in the base template's block, don't
				// have to do anything; otherwise, take on the
				// parent block's contents.
				if ($parent_block !== NULL) {
					$this->_current_block->content = $parent_block->content;
				}

				$curr = $curr->_prev;
			}
			// echo regardless of $return option; let Loader::view()
			// handle output buffering.
			echo $this->_current_block->content;
		}

		$this->_current_block = $this->_current_block->_parent;
	}
}

?>
