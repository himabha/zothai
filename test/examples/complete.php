<?php
/**
*	include FFmpeg class
**/
include DIRNAME(DIRNAME(__FILE__)).'/src/FFmpeg.php';

/**
*	get options from database
**/
$options = array(
	'duration'	=>	99,
	'position'	=>	0,
	'itsoffset'	=>	2,
);
/**
*	Create command
*/
/* $FFmpeg = new FFmpeg( '/usr/local/bin/ffmpeg' );
$FFmpeg->input( '/home/richardw/public_html/upload/videos/test/1.mp4' );
$FFmpeg->transpose( 0 )->vflip()->grayScale()->vcodec('h264')->frameRate('30000/1001');
$FFmpeg->acodec( 'aac' )->audioBitrate( '192k' );
foreach( $options AS $option => $values )
{
	$FFmpeg->call( $option , $values );
}
$FFmpeg->output( '/home/richardw/public_html/upload/videos/test/new.mp4' , 'mp4' );
print($FFmpeg->command); */


    
    	$FFmpeg = new FFmpeg;
    	//$FFmpeg->input( '/home/richardw/public_html/upload/videos/test/1.mp4' )->transpose( 2 )->output( '/home/richardw/public_html/upload/videos/test/new.mp4' )->ready();
    $FFmpeg->input( '/home/richardw/public_html/upload/videos/test/100mb.mp4' )->bitrate( '500k' )->output( '/home/richardw/public_html/upload/videos/test/Convert100mb.mp4' )->ready();