<?php

  // grab organization list and convert to JS array for bootstrap
  $org_results = $this->EE->db->query("SELECT name FROM organizations");
  $orgs = array();
  foreach($org_results->result_array() as $row){
    $orgs[] = $row['name'];
  }
  $js_orgs = json_encode($orgs);

?>
<script type="text/javascript">
$(document).ready(function() {

  /*  
  show/hide sidebar items 
  remove sidebar items if they are empty 
  hide all sidebar items except 'Your Selections' AND 'Environment Type' 
  */
  $('.sectionHeading').on('click', function() {
    $(this).parent().children('div.projectInfo').stop().toggle(200);
    $(this).children('span').toggleClass('projectBrowseCollapse');
    return false;
  });
  //$('.sectionHeading:gt(2)').trigger('click');
  $('.projectBrowseSelection:not(:has(li))').each(function(){
    $(this).parent().remove();
  });
  $('.projectSidebar:not(:has(div.projectInfo))').each(function(){
    $(this).remove();
  });
  $('.projectInfo ul:not(:has(li))').each(function(){
    $(this).parent().parent().remove();
  });

  // initially hide extra info about each project
  $('.toshow').hide();
  // show more info about each project
  $('.showDetails').on('click', function() {
    $(this).hide();
    $('.topAssociate').hide();
    $(this).parent().children('.toshow').show();
    return false;
  });
  // hide info about each project
  $('.closeDetails').on('click', function() {
    $(this).parent().hide();
    $('.topAssociate').show();
    $(this).parent().parent().children('.showDetails').show();
    return false;
  });

  // fancybox plugin for add yourself forms
  $(".fancybox").fancybox();


  // predictive search for organization fields
  var organizations = <?php echo $js_orgs; ?>;
  $('input[name=organization]').typeahead({source: organizations, items:6})

  // claim your name in the project options window!
  $('input[name=name]').on('change', function(){
    var role = $(this).attr('title');
    $('input[value="'+role+'"]').attr('checked','checked');
    $('input[name=organization]').val('');
    $('input[name=organization]').val($(this).siblings('.person_organization').val());
  });
  $('.fancybox').on('click',function(){
    $('input[name=organization]').val('');
  });

  // reset add yourself form
  $('.reset_me').on('click',function(){
    $('.form_add_yourself').trigger('reset');
  });

  // add yourself form validation and submit
  $('.form_add_yourself').each(function(){
    $(this).validationEngine();
    var myTarget = $(this).parent();
    $(this).ajaxForm({
      target: myTarget,
      url: '{site_url}process_edits_from_detail.php'
    });
  });

});
</script>