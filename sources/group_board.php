<?php
if ($wo['loggedin'] == false) {
  header("Location: " . Wo_SeoLink('index.php?link1=welcome'));
  exit();
}
$wo['description'] = 'Group Board';
$wo['keywords']    = 'Group Board';
$wo['page']        = 'group-board';
$wo['title']       = 'Group Board';
$wo['content']     = Wo_LoadPage('group-board/content');