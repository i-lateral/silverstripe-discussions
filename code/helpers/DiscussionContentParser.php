<?php

/**
 * Dedicated text parser to handle parsing content submitted
 * to a discussion.
 * 
 * This parser handles converting URL's in the text to links,
 * unless they are links to images (in which case they generate
 * an img tag) or youtube/vimeo (in which case they generate
 * an embed code).
 * 
 * @author Mo <morven@ilateral.co.uk>
 * @package discussions
 */
class DiscussionContentParser extends TextParser
{

    /**
     * These classes are added to each image tag
     * on generation
     * 
     * @config
     * @var array
     */
    private static $image_classes = array(
        "img-responsive",
        "leftAlone"
    );

    /**
     * The physical width to set on all elements (such as img
     * tags and video embeds). This will result in a width="xx"
     * attribute being added to the element.
     *
     * @config
     * @var integer
     */
    private static $embed_width = 640;

    /**
     * The physical height to set on all elements (such as img
     * tags and video embeds). This will result in a height="xx"
     * attribute being added to the element.
     *
     * @config
     * @var integer
     */
    private static $embed_height = 360;

    /**
     * These classes are added to a div that wraps 
     * each youtube iframe
     * 
     * @config
     * @var array
     */
    private static $youtube_classes = array(
        "video-embed",
        "embed-responsive",
        "embed-responsive-16by9"
    );

    /**
     * These classes are added to a div that wraps 
     * each youtube iframe
     * 
     * @config
     * @var array
     */
    private static $vimeo_classes = array(
        "video-embed",
        "embed-responsive",
        "embed-responsive-16by9"
    );
    

    /**
     * Run this content through various checks to render common HTML tags.
     * Many thanks for the following stack overflow posts for these:
     * 
     * https://stackoverflow.com/questions/1960461/convert-plain-text-urls-into-html-hyperlinks-in-php
     * https://stackoverflow.com/questions/910912/extract-urls-from-text-in-php
     * https://stackoverflow.com/questions/3392993/php-regex-to-get-youtube-video-id
     * https://stackoverflow.com/questions/10488943/easy-way-to-get-vimeo-id-from-a-vimeo-url
     *
     * @return string
     */
    public function parse()
    {
        $regex = '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i'; 
        $file_categories = Config::inst()->get("File", "app_categories");

        preg_match_all($regex, $this->content, $matches);
        $urls = $matches[0];

        // Loop found URLs and manipulate
        foreach($urls as $url) 
        {
            $ext = pathinfo($url, PATHINFO_EXTENSION);

            if ($ext && in_array($ext, $file_categories["image"])) {
                if (is_array($this->config()->image_classes)) {
                    $classes = implode(" ", $this->config()->image_classes);
                } else {
                    $classes = "";
                }
                
                $this->content = str_replace(
                    $url,
                    '<img src="' . $url . '" class="' . $classes . '" />',
                    $this->content
                );
            } elseif (strpos($url, "youtube") !== false || strpos($url, "youtu.be") !== false) {
                $this->content = str_replace(
                    $url,
                    $this->embedYoutube(
                        $url,
                        $this->config()->embed_width,
                        $this->config()->embed_height
                    ),
                    $this->content
                );
            } elseif (strpos($url, "vimeo") !== false) {
                $this->content = str_replace(
                    $url,
                    $this->embedVimeo(
                        $url,
                        $this->config()->embed_width,
                        $this->config()->embed_height
                    ),
                    $this->content
                );
            } else {
                $this->content = str_replace(
                    $url,
                    '<a href="' . $url . '" target="_blank">' . $url . '</a>',
                    $this->content
                );
            }
        }
        
        return nl2br($this->content);
    }

    /**
     * Get youtube video ID from content
     *
     * @param $url The share link to extract the ID from
     * @return string
     */
    protected function getYouTubeID($url) {
        preg_match(
            "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/",
            $url,
            $matches
        );
        
        if(isset($matches[1])) {
            return $matches[1];
        } else {
            return "";
        }
    }

    /**
     * Get vimeo video ID from content
     *
     * @param $url The share link to extract the ID from
     * @return string
     */
    protected function getVimeoID($url) {
        preg_match(
            "/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([‌​0-9]{6,11})[?]?.*/",
            $url,
            $matches
        );
        
        if(isset($matches[5])) {
            return $matches[5];
        } else {
            return "";
        }
    }

     
    protected function embedYoutube($url, $width, $height)
    {
        if (is_array($this->config()->youtube_classes)) {
            $classes = implode(" ", $this->config()->youtube_classes);
        } else {
            $classes = "";
        }
        
        $embed = '<span class="' . $classes . '">';
        $embed .= '<iframe src="https://www.youtube.com/embed/' . $this->getYouTubeID($url) .'"';
        $embed .= 'width="' . $width . '" height="' . $height . '" ';
        $embed .= 'allowfullscreen></iframe></span>';
        return $embed;
    }
    
    protected function embedVimeo($url, $width, $height)
    {
        if (is_array($this->config()->vimeo_classes)) {
            $classes = implode(" ", $this->config()->vimeo_classes);
        } else {
            $classes = "";
        }

        $embed = '<span class="' . $classes . '">';
        $embed .= '<iframe src="https://player.vimeo.com/video/' . $this->getVimeoID($url) . '" ';
        $embed .= 'width="' . $width . '" height="' . $height . '" ';
        $embed .= 'allowfullscreen></iframe></span>';
        return $embed;
    }

}