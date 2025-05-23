<?php
if (!class_exists('Ihc_Pagination')):

class Ihc_Pagination{

	private $base_url;
	private $param_name;
	private $current_page;
	private $total_items;
	private $items_per_page;
	private $link_class = 'ihc-user-pagination';
	private $selected_link_class = 'ihc-user-pagination selected';
	private $class_item_break = 'ihc-user-pagination';
	private $wrapper_class = 'iump-pagination-wrapper';
	private $is_unset = FALSE;

	public function __construct($input=array()){
		/*
		 * @param array
		 * @return none
		 */
		if (!empty($input) && is_array($input)){
			$required = array('base_url', 'param_name', 'total_items', 'items_per_page', 'current_page');
			foreach ($required as $key){
				if (empty($input[$key])){
					$this->is_unset = TRUE;
				}
				$this->$key = $input[$key];
			}
		} else {
			$this->is_unset = TRUE;
		}
	}

	public function output(){
		/*
		 * @param none
		 * @return string
		 */
		if ($this->is_unset){
			return '';
		}

		$output = '';
		$total_pages = ceil($this->total_items/$this->items_per_page);
		if ($total_pages<2){
			 return '';
		}

		if ($total_pages<=5){
			//show all the links
			for ($i=1; $i<=$total_pages; $i++){
				$show_links[] = $i;
			}
		} else {
			// we want to show only first, last, and the first neighbors of current page
			$show_links = array(1, $total_pages, $this->current_page, $this->current_page+1, $this->current_page-1);
		}

		for ($i=1; $i<=$total_pages; $i++){
			if (in_array($i, $show_links)){
				$href = add_query_arg($this->param_name, $i, $this->base_url);
				$class = ($this->current_page==$i) ? $this->selected_link_class : $this->link_class;
				$output .= "<a href='" . esc_url($href) . "' class='".esc_attr($class)."'>" . esc_html($i) . '</a>';
				$dots_on = TRUE;
			} else {
				if (!empty($dots_on)){
					$output .= '<span class="' . esc_attr($this->class_item_break) . '">...</span>';
					$dots_on = FALSE;
				}
			}
		}

		/// Back link
		if ($this->current_page>1){
			$prev_page = $this->current_page - 1;
			$href = add_query_arg($this->param_name, $prev_page, $this->base_url);
			$output = "<a href='" . esc_url($href) . "' class='" . esc_attr($this->link_class) . "'> < </a>" . $output;
		}
		///Forward link
		if ($this->current_page<$total_pages){
			$next_page = $this->current_page + 1;
			$href = add_query_arg($this->param_name, $next_page, $this->base_url);
			$output = $output . "<a href='" . esc_url($href) . "' class='" . esc_attr($this->link_class) . "'> > </a>";
		}

		//Wrappers
		$output = "<div class='" . esc_attr($this->wrapper_class) . "'>" . $output . "</div><div class='ihc-clear'></div>";
		return $output;
	}


}//end of class

endif;
