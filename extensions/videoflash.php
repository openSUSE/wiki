<?php
/**
*               last change: 2011-03-16
*       Available under the GFDL (http://www.gnu.org/copyleft/fdl.html) as source code was taken from  http://www.mediawiki.org/wiki/Extension:VideoFlash
*       Modification by SignpostMarv Martin
*       html5 added by Frank Forte
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
*                                                                              *
* Ex:     (use class or style attribute to set width/height)                   ************
*   from url http://website.com/video.ogv  (ovg, mp4, webm allowed)                       *
*   <videoflash type="html5" class="anything">http://website.com/video.ogv</videoflash>   *
*                                                                                         *
*******************************************************************************************/ 
 
$wgExtensionFunctions[] = 'wfVideoFlash';
$wgExtensionCredits['parserhook'][] = array(
        'name' => 'VideoFlash',
        'description' => 'VideoFlash (YouTube, GoogleVideo, Dailymotion, sevenload...)',
        'author' => 'Alberto Sarullo, SignpostMarv Martin, Frank Forte',
        'url' => 'http://www.mediawiki.org/wiki/Extension:VideoFlash'
);
 
function wfVideoFlash() {
        global $wgParser;
        $wgParser->setHook('videoflash', 'renderVideoFlash');
}
 
# The callback function for converting the input text to HTML output
function renderVideoFlash($input, $args) {
        // append new services to the array with the key being the intended value of the type attribute on the <videoflash> tag
        $url = array();
        $url['youtube'    ] = 'http://www.youtube.com/v/%1$s?fs=%5$u';
        $url['googlevideo'] = 'http://video.google.com/googleplayer.swf?docId=%1$d';
        $url['dailymotion'] = 'http://www.dailymotion.com/swf/%1$s';
        $url['sevenload'  ] = 'http://en.sevenload.com/pl/%1$s/%2$ux%3$u/swf';
        $url['revver'     ] = 'http://flash.revver.com/player/1.0/player.swf?mediaId=%1$u';
        $url['blip'       ] = 'http://blip.tv/play/%1$s';
        $url['vimeo'      ] = 'http://www.vimeo.com/moogaloop.swf?clip_id=%1$d&amp;server=www.vimeo.com&amp;fullscreen=1&amp;show_title=1&amp;show_byline=0&amp;show_portrait=0';
        $url['metacafe'   ] = 'http://www.metacafe.com/fplayer/%1$d/' . (isset($args['vid']) ? $args['vid'] : '') . '.swf';
        $url['viddler'    ] = 'http://www.viddler.com/player/%1$s';
        $url['megavideo'  ] = 'http://www.megavideo.com/v/%1$s';
	$url['html5'  ] = '%1$s';
	// Chinese Local Videos. To fight against GFW.
	$url['youku'      ] = 'http://player.youku.com/player.php/sid/%1$s/.swf';
	$url['tudou'      ] = 'http://www.tudou.com/v/%1$s/';
	$url['sina'       ] = 'http://you.video.sina.com.cn/api/sinawebApi/outplayrefer.php/vid=%1$s/s.swf';
	$url['qq'         ] = 'http://static.video.qq.com/TPout.swf?vid=%1$s&auto=1';
	$url['bilibili'   ] = 'http://static.loli.my/miniloader.swf?aid=%1$u';
	$url['acfun'      ] = 'http://static.acfun.tv/ACFlashPlayer.swf?aid=%1$u';	
	$url['ku6'        ] = 'http://player.ku6.com/refer/%1$s../v.swf';
	$url['56'         ] = 'http://player.56.com/v_%1$s.swf';
	$url['sohu'           ] = 'http://tv.sohu.com/upload/swf/20120628/PlayerShell.swf?autoplay=false&skinNum=1&id=%1$d&topBar=1&shareBtn=1&likeBtn=1&topBar=1&sogouBtn=0';
        $url['yinyuetai'      ] = 'http://player.yinyuetai.com/video/player/%1$d/v_0.swf';
        $url['ifeng'          ] = 'http://v.ifeng.com/include/exterior.swf?guid=%1$s&AutoPlay=false';
	$url['xiyou'           ] = 'http://player.xiyou.cntv.cn/%1$s.swf';
	$url['pomoho'          ] = 'http://resources.pomoho.com/swf/out_player.swf?flvid=%1$d&outall=true';

	// add more service here.

        // if the embed code for a service requires flashvars attributes, you can add them here
        $flashvars = array();
	$flashvars['revver'] = 'mediaId=%1$u&affiliateId=0';
 
        $type       = isset($args['type'],$url[$args['type']]) ? $args['type'] : 'youtube';
        $media_url  = isset($url[$type]) ? $url[$type] : $url['youtube'];
        $flash_vars = isset($flashvars[$type]) ? $flashvars[$type] : '';
 
        $input_array = explode('|', htmlspecialchars($input));
        $id     = current($input_array);
        $width  = (count($input_array) > 1 && is_numeric($input_array[1])) ? $input_array[1] : 425;
        $height = (count($input_array) > 2 && is_numeric($input_array[2])) ? $input_array[2] : 350;
        $fullscreen = (isset($args['fullscreen']) ? $args['fullscreen'] : 'true') === 'false' ? false : true;
 
                if(strtolower($type) == 'html5')
                {
                        // I recommend CSS to set the video size, i.e. <videoflash type="html5" style="width:200px;height:150px">url</videoflash>
                        $output = '<video';
                        foreach($args as $attribute=>$value)
                        {
                                 $output .= ' '.$attribute.'="'.$value.'"';
                        }
 
                     $output .= '><source src="'.$id.'"></video><p style="font-size:80%;padding:0;margin:0;">(Right click to control movie)</p>';
                     return $output;
		}
                else
                {
                         $output = '<object width="%2$u" height="%3$u">'
                                .' <param name="movie" value="'.$url[$type].'" />'
                                .' <param name="allowFullScreen" value="%4$s" />'
                                .' <param name="wmode" value="transparent" />'
                                .' <embed src="'.$url[$type] . '" type="application/x-shockwave-flash" wmode="transparent"'
                                .' width="%2$u" height="%3$u" allowfullscreen="%4$s"'
                                        . ' flashvars="' . $flash_vars . '"></embed></object>';
                         return sprintf($output,$id,$width,$height,$fullscreen ? 'true' : 'false', (integer)$fullscreen);
                }
}
?>
