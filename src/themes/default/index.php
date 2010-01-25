<?php

function datepickerformat()
{
	if(Config::get('duedateformat') == 2) $duedateformat = 'm/d/yy';
	elseif(Config::get('duedateformat') == 3) $duedateformat = 'dd.mm.yy';
	elseif(Config::get('duedateformat') == 4) $duedateformat = 'dd/mm/yy';
	else $duedateformat = 'yy-mm-dd';
	return $duedateformat;
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<HEAD>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title><?php mttinfo('title'); ?></title>
<link rel="stylesheet" type="text/css" href="<?php mttinfo('template_uri'); ?>style.css?v=@VERSION" media="all">
<?php if(Config::get('rtl')): ?>
<link rel="stylesheet" type="text/css" href="<?php mttinfo('template_uri'); ?>style_rtl.css?v=@VERSION" media="all">
<?php endif; ?>
<?php if(isset($_GET['pda'])): ?>
<meta name="viewport" id="viewport" content="width=device-width">
<link rel="stylesheet" type="text/css" href="<?php mttinfo('template_uri'); ?>pda.css?v=@VERSION" media="all">
<?php else: ?>
<link rel="stylesheet" type="text/css" href="<?php mttinfo('template_uri'); ?>print.css?v=@VERSION" media="print">
<?php endif; ?>
</HEAD>

<body>

<script type="text/javascript" src="jquery/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="jquery/jquery-ui-1.7.2.custom.min.js"></script>
<script type="text/javascript" src="ajax.lang.php?v=@VERSION"></script>
<script type="text/javascript" src="ajax.js?v=@VERSION"></script>
<script type="text/javascript" src="jquery/jquery.autocomplete.min.js"></script>
<script type="text/javascript" src="<?php mttinfo('template_uri'); ?>functions.js?v=@VERSION"></script>

<script type="text/javascript">
$().ready(function(){
	$("#tasklist").sortable({cancel:'span,input,a,textarea', delay: 150, update:orderChanged, start:sortStart, items:'> :not(.task-completed)'});
	$("#tasklist").bind("click", tasklistClick);
	$("#edittags").autocomplete('ajax.php?suggestTags', {scroll: false, multiple: true, selectFirst:false, max:8, extraParams:{list:function(){return curList.id}}});
	$("#priopopup").mouseleave(function(){$(this).hide()});
<?php
	if($needAuth)
	{
		echo "\tflag.needAuth = true;\n";
		if(is_logged()) echo "\tflag.isLogged = true;\n";
	}
	if(Config::get('autotag')) echo "\tflag.autoTag = true;\n";
	echo "\tloadLists(1, 1);\n";
?>
	$("#duedate").datepicker({dateFormat: '<?php echo datepickerformat(); ?>', firstDay: <?php echo Config::get('firstdayofweek'); ?>,
		showOn: 'button', buttonImage: '<?php mttinfo('template_uri'); ?>images/calendar.png', buttonImageOnly: true, changeMonth:true,
		changeYear:true, constrainInput: false, duration:'', nextText:'&gt;', prevText:'&lt;', dayNamesMin:lang.daysMin, 
		dayNames:lang.daysLong, monthNamesShort:lang.monthsLong });
<?php if(!isset($_GET['pda'])): ?>
	$("#page_taskedit").draggable({ handle:'h3', stop: function(e,ui){ flag.windowTaskEditMoved=true; tmp.editformpos=[$(this).css('left'),$(this).css('top')]; } }); 
	$("#page_taskedit").resizable({ minWidth:$("#page_taskedit").width(), minHeight:220, start:function(ui,e){editFormResize(1)}, resize:function(ui,e){editFormResize(0,e)}, stop:function(ui,e){editFormResize(2,e)} });
<?php else: ?>
	flag.pda = true;
<?php endif; ?>
<?php if(isset($_GET['singletab'])): ?>
	mytinytodo.addAction('listsLoaded', tplSingleTabLoaded);
	mytinytodo.addAction('listRenamed', tplSingleTabRenamed);
	mytinytodo.addAction('listAdded', tplSingleTabAdded);
<?php endif; ?>
<?php if(!isset($_GET['singletab']) && !isset($_GET['pda'])): ?>
	$("#lists ul").sortable({delay:150, update:listOrderChanged, items:'> :not(.mtt-tabs-button)'});
<?php endif; ?>
});
</script>

<div id="wrapper">
<div id="container">
<div id="body">

<h2><?php mttinfo('title'); ?></h2>

<div id="loading"><img src="<?php mttinfo('template_uri'); ?>images/loading1.gif"></div>

<div id="bar">
 <div id="msg"><span class="msg-text" onClick="toggleMsgDetails()"></span><div class="msg-details"></div></div>
 <div class="bar-menu">
 <span class="menu-owner">
   <a href="#settings" onClick="showSettings();return false;"><?php _e('a_settings');?></a>
 </span>
 <span class="bar-delim" style="display:none"> | </span>
 <span id="bar_auth">
  <span id="bar_public" style="display:none"><?php _e('public_tasks');?> |</span>
  <span id="bar_login"><a href="#login" class="nodecor" onClick="showAuth(this);return false;"><u><?php _e('a_login');?></u> <img src="<?php mttinfo('template_uri'); ?>images/arrdown.gif" border=0></a></span>
  <a href="#logout" id="bar_logout" onClick="logout();return false"><?php _e('a_logout');?></a>
 </span>
 </div>
</div>

<br clear="all">

<div id="page_tasks" style="display:none">

<div id="lists">
 <ul class="mtt-tabs <?php if(isset($_GET['singletab'])) echo "mtt-tabs-only-one"; ?>"></ul>
</div>

<div id="toolbar" class="mtt-htabs">
   <span id="rss_icon"><a href="#" title="<?php _e('rss_feed');?>"><img src="<?php mttinfo('template_uri'); ?>images/feed_bw.png" onMouseOver="this.src='<?php mttinfo('template_uri'); ?>images/feed.png'" onMouseOut="this.src='<?php mttinfo('template_uri'); ?>images/feed_bw.png'"></a></span>
   <span id="htab_newtask"><?php _e('htab_newtask');?> 
	<form onSubmit="return submitNewTask(this)"><input type="text" name="task" value="" maxlength="250" id="task"> <input type="submit" value="<?php _e('btn_add');?>"></form>
	<a href="#" onClick="showEditForm(1);return false;" title="<?php _e('advanced_add');?>"><img src="<?php mttinfo('template_uri'); ?>images/page_white_edit_bw.png" onMouseOver="this.src='<?php mttinfo('template_uri'); ?>images/page_white_edit.png'" onMouseOut="this.src='<?php mttinfo('template_uri'); ?>images/page_white_edit_bw.png'"></a>
	&nbsp;&nbsp;| <a href="#" class="htab-toggle" onClick="addsearchToggle(1);this.blur();return false;"><?php _e('htab_search');?></a>
   </span>
   <span id="htab_search" style="display:none"><?php _e('htab_search');?>
	<form onSubmit="return searchTasks()"><input type="text" name="search" value="" maxlength="250" id="search" onKeyUp="timerSearch()" autocomplete="off"> <input type="submit" value="<?php _e('btn_search');?>"></form>
	&nbsp;&nbsp;| <a href="#" class="htab-toggle" onClick="addsearchToggle(0);this.blur();return false;"><?php _e('htab_newtask');?></a> 
	<div id="searchbar"><?php _e('searching');?> <span id="searchbarkeyword"></span></div> 
   </span>
</div>

<h3>
<span id="sort" onClick="btnMenu(this);return false;" class="mtt-btnmenu"><span class="btnstr"></span> <img src="<?php mttinfo('template_uri'); ?>images/arrdown.gif"></span>
<span id="taskview" onClick="btnMenu(this);return false;" class="mtt-btnmenu"><span class="btnstr"><?php _e('tasks');?></span> (<span id="total">0</span>) &nbsp;<img src="<?php mttinfo('template_uri'); ?>images/arrdown.gif"></span>
<span id="tagcloudbtn" onClick="showTagCloud(this);"><span class="btnstr"><?php _e('tags');?></span> <img src="<?php mttinfo('template_uri'); ?>images/arrdown.gif"></span>
<span class="mtt-notes-showhide"><?php _e('notes');?> <a href="#" onClick="toggleAllNotes(1);this.blur();return false;"><?php _e('notes_show');?></a> / <a href="#" onClick="toggleAllNotes(0);this.blur();return false;"><?php _e('notes_hide');?></a></span>
</h3>

<div id="taskcontainer">
 <ol id="tasklist" class="sortable"></ol>
</div>

</div> <!-- end of page_tasks -->


<div id="page_taskedit" style="display:none">

<h3 class="mtt-inadd"><?php _e('add_task');?></h3>
<h3 class="mtt-inedit">
 <div id="taskedit-date" class="mtt-inedit">
  <span class="date-created" title="<?php _e('taskdate_created');?>"><span></span></span>
  <span class="date-completed" title="<?php _e('taskdate_completed');?>"> / <span></span></span>
 </div>
 <?php _e('edit_task');?>
</h3>

<form onSubmit="return saveTask(this)" name="edittask">
<input type="hidden" name="isadd" value="0">
<input type="hidden" name="id" value="">
<div class="form-row form-row-short"><span class="h"><?php _e('priority');?></span> <SELECT name="prio"><option value="2">+2</option><option value="1">+1</option><option value="0" selected>&plusmn;0</option><option value="-1">&minus;1</option></SELECT></div>
<div class="form-row form-row-short"><span class="h"><?php _e('due');?> </span> <input name="duedate" id="duedate" value="" class="in100" title="Y-M-D, M/D/Y, D.M.Y, M/D, D.M" autocomplete="off"></div>
<div class="form-row-short-end"></div>
<div class="form-row"><div class="h"><?php _e('task');?></div> <input type="text" name="task" value="" class="in500" maxlength="250"></div>
<div class="form-row"><div class="h"><?php _e('note');?></div> <textarea name="note" class="in500"></textarea></div>
<div class="form-row"><div class="h"><?php _e('tags');?></div>
 <table cellspacing="0" cellpadding="0" width="100%"><tr>
  <td><input type="text" name="tags" id="edittags" value="" class="in500" maxlength="250"></td>
  <td class="alltags-cell">
   <a href="#" id="alltags_show" onClick="toggleEditAllTags(1);return false;"><?php _e('alltags_show');?></a>
   <a href="#" id="alltags_hide" onClick="toggleEditAllTags(0);return false;" style="display:none"><?php _e('alltags_hide');?></a></td>
 </tr></table>
</div>
<div class="form-row" id="alltags" style="display:none;"><?php _e('alltags');?> <span class="tags-list"></span></div>
<div class="form-row form-bottom-buttons"><input type="submit" value="<?php _e('save');?>" onClick="this.blur()"> <input type="button" value="<?php _e('cancel');?>" onClick="cancelEdit();this.blur();return false"></div>
</form>

</div>  <!-- end of page_taskedit -->


<div id="authform" style="display:none">
<form onSubmit="doAuth(this);return false;">
 <div class="h"><?php _e('password');?></div><div><input type="password" name="password" id="password"></div><div><input type="submit" value="<?php _e('btn_login');?>"></div>
</form>
</div>

<div id="priopopup" style="display:none">
<span class="prio-neg" onClick="prioClick(-1,this)">&minus;1</span> <span class="prio-o" onClick="prioClick(0,this)">&plusmn;0</span>
<span class="prio-pos" onClick="prioClick(1,this)">+1</span> <span class="prio-pos" onClick="prioClick(2,this)">+2</span>
</div>

<div id="taskviewcontainer" class="mtt-btnmenu-container" style="display:none">
<ul>
 <li onClick="setTaskview(0)"><span id="view_tasks"><?php _e('tasks');?></span> (<span id="cnt_total">0</span>)</li>
 <li onClick="setTaskview('past')"><span id="view_past"><?php _e('f_past');?></span> (<span id="cnt_past">0</span>)</li>
 <li onClick="setTaskview('today')"><span id="view_today"><?php _e('f_today');?></span> (<span id="cnt_today">0</span>)</li>
 <li onClick="setTaskview('soon')"><span id="view_soon"><?php _e('f_soon');?></span> (<span id="cnt_soon">0</span>)</li>
</ul>
</div>

<div id="sortcontainer" class="mtt-btnmenu-container" style="display:none">
<ul>
 <li id="sortByHand" onClick="setSort(0)"><?php _e('sortByHand');?></li>
 <li id="sortByPrio" onClick="setSort(1)"><?php _e('sortByPriority');?></li>
 <li id="sortByDueDate" onClick="setSort(2)"><?php _e('sortByDueDate');?></li>
</ul>
</div>

<div id="tagcloud" style="display:none">
 <div id="tagcloudcancel" onClick="cancelTagFilter();tagCloudClose();"><?php _e('tagfilter_cancel');?></div>
 <div id="tagcloudload"><img src="<?php mttinfo('template_uri'); ?>images/loading1_24.gif"></div>
 <div id="tagcloudcontent"></div>
</div>

<div id="mylistscontainer" class="mtt-btnmenu-container mtt-menu-has-images" style="display:none">
<ul>
 <li onClick="addList()"><?php _e('list_new');?></li>
 <li class="mtt-need-list" onClick="renameCurList()"><?php _e('list_rename');?></li>
 <li class="mtt-need-list" onClick="deleteCurList()"><?php _e('list_delete');?></li>
 <li class="mtt-need-list" id="btnPublish" onClick="publishCurList()"><div class="menu-icon"></div><?php _e('list_publish');?></li>
 <li class="mtt-need-list" id="btnShowCompleted" onClick="showCompletedToggle()"><div class="menu-icon"></div><?php _e('list_showcompleted');?></li>
</ul>
</div>

<div id="taskcontextcontainer" class="mtt-btnmenu-container mtt-menu-has-images mtt-menu-has-submenu" style="display:none">
<ul>
 <li id="cmenu_edit"><b><?php _e('action_edit');?></b></li>
 <li id="cmenu_note"><?php _e('action_note');?></li>
 <li id="cmenu_prio" class="mtt-menu-indicator" submenu="priocontainer"><div class="submenu-icon"></div><?php _e('action_priority');?></li>
 <li id="cmenu_move" class="mtt-menu-indicator" submenu="listsmenucontainer"><div class="submenu-icon"></div><?php _e('action_move');?></li>
 <li id="cmenu_delete"><?php _e('action_delete');?></li>
</ul>
</div>

<div id="priocontainer" class="mtt-btnmenu-container mtt-menu-has-images" style="display:none">
<ul>
 <li id="cmenu_prio:-1">&minus;1</li>
 <li id="cmenu_prio:0">&plusmn;0</li>
 <li id="cmenu_prio:1">+1</li>
 <li id="cmenu_prio:2">+2</li>
</ul>
</div>

<div id="listsmenucontainer" class="mtt-btnmenu-container mtt-menu-has-images" style="display:none">
<ul>
</ul>
</div>

<div id="page_ajax" style="display:none"></div>

</div>
<div id="space"></div>
</div>

<div id="footer"><div id="footer_content">Powered by <strong><a href="http://www.mytinytodo.net/">myTinyTodo</a></strong> v@VERSION </div></div>

</div>
</body>
</html>