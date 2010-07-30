<?php
/**
*	Available under the GFDL (http://www.gnu.org/copyleft/fdl.html) as source code was taken from  http://www.mediawiki.org/wiki/Extension:VideoFlash
*	Modification by SignpostMarv Martin
*/ 
/*******************************************************************************
*                                                                              *
* VideoFlash Extension by Alberto Sarullo, based on YouTube (Iubito) extension *
* http://www.mediawiki.org/wiki/Extension:VideoFlash                           *
*                                                                              * 
*                                                                              * 
* Tag :                                                                        *
*   <videoflash>v</videoflash>                                                 *
*                                                                              *
* Ex :                                                                         *
*   from url http://www.youtube.com/watch?v=4lhyH5TsuPg                        *
*   <videoflash>4lhyH5TsuPg</videoflash>                                       *
*                                                                              *
* Ex:                                                                          *
*   from url http://video.google.it/videoplay?docid=1811233136844420765        *
*   <videoflash type="googlevideo">1811233136844420765</videoflash>            *
*                                                                              *
* Ex:                                                                          *
*   from url http://en.sevenload.com/videos/7DQGFhH/Sexy-Tussis                *
*   <videoflash type="sevenload">7DQGFhH</videoflash>                          *
*                                                                              *
* Ex:                                                                          *
*   from url http://one.revver.com/watch/138657                                *
*   <videoflash type="revver">138657</videoflash>                              *
*                                                                              *
********************************************************************************/ 
 
$wgExtensionFunctions[] = 'wfVideoFlash';
$wgExtensionCredits['parserhook'][] = array(
        'name' => 'VideoFlash',
        'description' => 'VideoFlash (YouTube, GoogleVideo, Dailymotion, sevenload...)',
        'author' => 'Alberto Sarullo, SignpostMarv Martin',
        'url' => 'http://www.mediawiki.org/wiki/Extension:VideoFlash'
);
 
function wfVideoFlash() {
        global $wgParser;
        $wgParser->setHook('videoflash', 'renderVideoFlash');
}
 
 
# The callback function for converting the input text to HTML output
function renderVideoFlash($input, $args) {
	$url = array();
	$url['youtube'    ] = 'http://www.youtube.com/v/%1$s';
#	$url['googlevideo'] = 'http://video.google.com/googleplayer.swf?docId=%1$u';
#
# Changed the above line to contain $d as google video now often provides a negative docId
#

#	$url['googlevideo'] = 'http://video.google.com/googleplayer.swf?docId=%1$d';
#
# Changed the above line again to contain $s now it works with all googlevideos
#
	$url['googlevideo'] = 'http://video.google.com/googleplayer.swf?docId=%1$d';
	$url['dailymotion'] = 'http://www.dailymotion.com/swf/%1$s';
        $url['vimeo'      ] = 'http://vimeo.com/%1$s';
	$url['sevenload'  ] = 'http://en.sevenload.com/pl/%1$s/%2$ux%3$u/swf';
	$url['revver'     ] = 'http://flash.revver.com/player/1.0/player.swf?mediaId=%1$u';
	$url['blip'       ] = 'http://blip.tv/play/%1$s';
	$url['youku'      ] = 'http://player.youku.com/player.php/sid/%1$s/.swf';
        $url['vimeo'      ] = 'http://vimeo.com/moogaloop.swf?clip_id=%1$s&;server=www.vimeo.com&fullscreen=1&show_title=1&show_byline=1&show_portrait=0&color="';
        $url['metacafe'   ] = 'http://www.metacafe.com/fplayer/%1$d/' . $args['vid'] . '.swf';
 
        // add here other similar services
 
		$flashvars = array();
		$flashvars['revver'] = 'mediaId=%1$u&affiliateId=0';
 
        $type = isset($args['type'],$url[$args['type']]) ? $args['type'] : 'youtube';
			$media_url = isset($url[$type]) ? $url[$type] : $url['youtube'];
			$flash_vars = isset($flashvars[$type]) ? $flashvars[$type] : '';
		list($id,$width,$height,$style) = explode('|',htmlspecialchars($input));
			$width = is_numeric($width) ? $width : 425;
			$height = is_numeric($height) ? $height : 350;
			$style = is_string($style) ? $style : '';
		$fullscreen = isset($args['fullscreen']) ? $args['fullscreen'] : 'true';
			switch($fullscreen)
			{
				case 'false':
					$fullscreen = 'false';
				break;
				case 'true':
				case 1:
				default:
					$fullscreen = 'true';
				break;
			}
        $output = '<object width="%2$u" height="%3$u" style="%4$s" class="flash_video">'
                .' <param name="movie" value="'.$url[$type].'">'
				.' <param name="allowfullscreen" value="' . $fullscreen . '" />'
                .' <param name="wmode" value="transparent"></param>'
                .' <embed src="'.$url[$type] . '" type="application/x-shockwave-flash" wmode="transparent"'
                .' width="%2$u" height="%3$u" allowfullscreen="' . $fullscreen . '" style="%4$s"'				
				. ' flashvars="' . $flash_vars . '"></embed></object>';
 
        return sprintf($output,$id,$width,$height,$style);
 }
?>
