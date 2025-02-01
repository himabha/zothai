<?php
if ($wo['loggedin'] == false) {
  header("Location: " . Wo_SeoLink('index.php?link1=welcome'));
  exit();
}
$wo['description'] = 'Group Board';
$wo['keywords']    = 'Group Board';
$wo['page']        = 'open-space';
$wo['title']       = 'Group Board';
$wo['content']     = Wo_LoadPage('open-space/content');