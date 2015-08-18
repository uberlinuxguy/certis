<div class="page_line"><?
// if we have more items than fit on a page, let's print the page numbers.
if($total_items > $items_per_page) {
	$url = API::printURL($view->module, $view->action, $view->req_id);
	if($current_page > 1) {
		print '<a class="page_prev" href="' . $url . 'page=' . ($current_page - 1) . '/">&lt;&lt;</a> ';
	} else {
		print '&lt;&lt; ';
	}


	for($i = 1; $i<ceil($total_items/$items_per_page)+1; $i++){
		if($i == $current_page) {
			print '<span class="page_selected">' . $i . "</span> ";
		} else {
			print '<a class="page_link" href="' . $url . 'page=' . $i . '/">' . $i . '</a> ';
		}


	}

	if($current_page < ceil(($total_items/$items_per_page))	 ) {
		print '<a class="page_next" href="' . $url . 'page=' . ($current_page + 1) . '/">&gt;&gt;</a> ';
	} else {
		print '&gt;&gt; ';
	}
}
?></div><br />
