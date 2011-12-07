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
        $url['youku'      ] = 'http://player.youku.com/player.php/sid/%1$s/.swf';
        $url['vimeo'      ] = 'http://www.vimeo.com/moogaloop.swf?clip_id=%1$d&amp;server=www.vimeo.com&amp;fullscreen=1&amp;show_title=1&amp;show_byline=0&amp;show_portrait=0';
        $url['metacafe'   ] = 'http://www.metacafe.com/fplayer/%1$d/' . (isset($args['vid']) ? $args['vid'] : '') . '.swf';
        $url['viddler'    ] = 'http://www.viddler.com/player/%1$s';
        $url['megavideo'  ] = 'http://www.megavideo.com/v/%1$s';
        $url['html5'  ] = '%1$s';
 
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
