<?php

// pagination results
  $total_results = $search_results['totalNumResults'];
  $pagination_results = $search_results['pagination'];
  $records_per_page = $search_results['numberPerPage'];
  $record_start = $search_results['recordNumberStart'];
  $record_end = $search_results['recordNumberEnd'];
  $current_page = $pagination_results['currentPageNum'];
  $page_url = $pagination_results['HREFfirstPage'];
  if(!strpos($page_url,'?')){
    $page_url.= '?';
  }
  $first_page = 1;
  $last_page = max(1,ceil($total_results/10));
  $second_page = min(2,$last_page);
  $third_page = min(3,$last_page);
  $prev_page = max(1,$current_page-1);
  $two_prev = max(1,$current_page-2);
  $three_prev = max(1,$current_page-3);
  $second_to_last = max(1,$last_page-1);
  $third_to_last = max(1,$last_page-2);
  $next_page = min($last_page,$current_page+1);
  $two_next = min($last_page,$current_page+2);
  $three_next = min($last_page,$current_page+3);

  $all_pages = array($current_page,$first_page,$second_page,$third_page,$prev_page,$last_page,$second_to_last,$third_to_last,$next_page,$two_prev,$three_prev,$two_next,$three_next);
  $all_pages = array_filter(array_unique($all_pages));
  sort($all_pages);
  if($current_page > $second_page + 5){
    $all_pages[2] = '...';
  }
  if($current_page < $second_to_last - 5){
    $all_pages[count($all_pages)-3] = '...';
  }

// create pagination html
  $pagination_html = "";
  $pagination_html.= '<div class="pagination">';
  if($total_results > 0){
    foreach($all_pages as $single_page){
      if($current_page == $single_page || $single_page == '...'){
        if($single_page == '...'){
          $pagination_html.= '<span class="page">';
        }
        else{
          $pagination_html.= '<span class="page active">'; 
        }
        $pagination_html.= $single_page;
        $pagination_html.= '</span>';
      }
      else {
        $pagination_html.= '<a class="page first" href="'.($the_search_url).'search_url='.rawurlencode($page_url.'&page='.$single_page).'#topSearch">';
        $pagination_html.= $single_page;
        $pagination_html.= '</a>';
      }
    }
    // previous and next page buttons
    $pagination_html.= '<div class="paginationToggle">';
    if($current_page > 1){
      $pagination_html.= '<a class="previousPage" href="'.($the_search_url).'search_url='.rawurlencode($page_url.'&page='.($current_page-1)).'#topSearch">';
      $pagination_html.= '<img src="/images/uploads/left.jpg" />';
      $pagination_html.= '</a>';
    }
    if($current_page < $last_page){
      $pagination_html.= '<a class="nextPage" href="'.($the_search_url).'search_url='.rawurlencode($page_url.'&page='.($current_page+1)).'#topSearch">';
      $pagination_html.= '<img src="/images/uploads/right.jpg" />';
      $pagination_html.= '</a>';
    }
    $pagination_html.= '</div>';
  }
  $pagination_html.= '</div>';

?>