<?php
/**
 * Adds a custom admin area to manage discussions and categories.
 * 
 * This will be disabled if the CMS is installed (so that discussions
 * can be managed via their relevant pages). 
 *
 * @package Discussions
 * @author Mo <morven@i-lateral.co.uk>
 */
class DiscussionAdmin extends ModelAdmin {
    
    private static $url_segment = 'discussions';
    
    private static $menu_title = 'Discussions';
    
    private static $menu_priority = 10;
    
    private static $managed_models = array(
        'Discussion',
        'DiscussionCategory',
    );
    
    private static $model_importers = array();

}
