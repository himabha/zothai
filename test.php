<?php

   //$ffmpeg = trim(exec('which ffmpeg')); // or better yet:
//$ffmpeg = trim(exec('type -P ffmpeg'));

//if (empty($ffmpeg))
//{
//die('ffmpeg not available');
//}
//exec($ffmpeg . '-i …');



$video = '/home/richardw/public_html/upload/videos/test/tt.mp4';
$bitrate = '5000k';
 
//$command = "/usr/local/bin/ffmpeg -i $video -b:v $bitrate -bufsize $bitrate /home/richardw/public_html/upload/videos/test/tt_next1.mp4";
$command = "/usr/local/bin/ffmpeg -i $video -vf 'scale=trunc(iw/10)*2:trunc(ih/10)*2' -c:v libx265 -crf 28 /home/richardw/public_html/upload/videos/test/tt.mp4";

system($command);
 
echo "File has been converted";
?>