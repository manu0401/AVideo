<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/Menu.php';
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/MenuItem.php';

class TopMenu extends PluginAbstract {
    const PERMISSION_CAN_EDIT = 0;


    public function getTags() {
        return array(
            PluginTags::$FREE,
        );
    }
    public function getDescription() {
        $txt = "Responsive Customized Top Menu";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/How-to-use-TopMenu-Plug-in' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $txt.$help;
    }

    public function getName() {
        return "TopMenu";
    }

    public function getUUID() {
        return "2e7866ed-2e02-4136-bec6-4cd90754e3a2";
    }    
    
    public function getPluginVersion() {
        return "2.2";   
    }
    
     public function getEmptyDataObject() {
        global $global;
        
        $obj = new stdClass();
        $obj->show_menu_items = true;
        
        $o = new stdClass();
        $o->type = [0=>'Do not compact top menu'];
        for ($i = 1; $i <= 10; $i++) {
            $o->type[$i] = "Compact top menus if it is greater then $i items";
        }
        $o->value = 4;
        $obj->compactMenuIfIsGreaterThen = $o;
        $obj->showBackToTopButton = true;
        
        return $obj;
     }
    
    public function updateScript() {
        global $mysqlDatabase;
        //update version 2.0
        $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? and COLUMN_NAME=?";
        $res = sqlDAL::readSql($sql,"s",array($mysqlDatabase, "topMenu_items", "menuSeoUrlItem"));
        $menuSeoUrlItem=sqlDAL::fetchAssoc($res);
        if(!$menuSeoUrlItem){
            sqlDal::writeSql("alter table topMenu_items add menuSeoUrlItem varchar(255) default ''"); 
        }
        return true;
    }

    public function getHeadCode() {
        global $global;
        $css = '<link href="' .getURL('plugin/TopMenu/style.css') . '" rel="stylesheet" type="text/css"/>';
        return $css;
    }
    
    public function getHTMLMenuRight() {
        global $global;
        include $global['systemRootPath'] . 'plugin/TopMenu/HTMLMenuRight.php';
    }
        
    public function getPluginMenu() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/TopMenu/pluginMenu.html';
        return file_get_contents($filename);
    }
    
    public function getHTMLMenuLeft() {
        global $global;
        include $global['systemRootPath'] . 'plugin/TopMenu/HTMLMenuLeft.php';
    }
    
    public function getidBySeoUrl($menuSeoUrlItem) {
        global $global;
        $sql="select id from topMenu_items where menuSeoUrlItem= ?"; 
        $res=sqlDal::readSql($sql, "s", array(($menuSeoUrlItem)));
        $menuId=sqlDAL::fetchAssoc($res);
        if(!isset($menuId['id']))
        return false;
        return $menuId['id'];
    }
    
    function getPermissionsOptions(){
        $permissions = array();
        $permissions[] = new PluginPermissionOption(TopMenu::PERMISSION_CAN_EDIT, __("TopMenu"), __("Can edit TopMenu plugin"), 'TopMenu');
        return $permissions;
    }
    
    static function canAdminTopMenu(){
        return Permissions::hasPermission(TopMenu::PERMISSION_CAN_EDIT,'TopMenu');
    }
    
    public function getGalleryActionButton($videos_id) {
        global $global;
        $obj = $this->getDataObject();
        include $global['systemRootPath'] . 'plugin/TopMenu/actionButtonGallery.php';
    }

    public function getNetflixActionButton($videos_id) {
        global $global;
        $obj = $this->getDataObject();
        include $global['systemRootPath'] . 'plugin/TopMenu/actionButtonNetflix.php';
    }
    
    public function getWatchActionButton($videos_id) {
        global $global, $video;
        $obj = $this->getDataObject();
        include $global['systemRootPath'] . 'plugin/TopMenu/actionButtonNetflix.php';
    }
    
    static function getExternalOptionName($menu_item_id){
        return "menu_url_{$menu_item_id}";
    }
    
    static function setVideoMenuURL($videos_id, $menu_item_id, $url) {
        $video = new Video('', '', $videos_id, true);
        $externalOptions = _json_decode($video->getExternalOptions()); 
        if(!is_object($externalOptions)){
            $externalOptions = new stdClass();
        }      
        $parameterName = self::getExternalOptionName($menu_item_id);
        $externalOptions->$parameterName = $url;
        $video->setExternalOptions(json_encode($externalOptions));
        return $video->save();
    }

    static function getVideoMenuURL($videos_id, $menu_item_id) {
        global $_getVideoMenuURL;
        if(!isset($_getVideoMenuURL)){
            $_getVideoMenuURL = array();
        }
        $index = "{$videos_id}_{$menu_item_id}";
        if(!empty($_getVideoMenuURL[$index])){
            return $_getVideoMenuURL[$index];
        }
        $video = new Video('', '', $videos_id);
             
        $parameterName = self::getExternalOptionName($menu_item_id);
        $externalOptions = _json_decode($video->getExternalOptions());
        if(empty($externalOptions)){
            $externalOptions = new stdClass();
        }
        if(!isset($externalOptions->$parameterName)){
            $externalOptions->$parameterName = '';
        }
        $_getVideoMenuURL[$index] = $externalOptions->$parameterName;
        return $_getVideoMenuURL[$index];
    }
    
    static public function thereIsMenuItemsActive(){
        $menu = Menu::getAllActive(Menu::$typeActionMenuCustomURL);
        if(!empty($menu)){
            return true;
        }
        $menu = Menu::getAllActive(Menu::$typeActionMenuCustomURLForLoggedUsers);
        if(!empty($menu)){
            return true;
        }
        $menu = Menu::getAllActive(Menu::$typeActionMenuCustomURLForUsersThatCanWatchVideo);
        if(!empty($menu)){
            return true;
        }
        $menu = Menu::getAllActive(Menu::$typeActionMenuCustomURLForUsersThatCanNotWatchVideo);
        if(!empty($menu)){
            return true;
        }
        return false;
    }
        
    public function getVideosManagerListButton() {
        if (!User::canUpload()) {
            return "";
        }
        
        $obj = $this->getDataObject();
        
        if(empty($obj->show_menu_items)){
            return '';
        }
        
        if(!self::thereIsMenuItemsActive()){
            return '';
        }
        
        $btn = '';
        
        $btn .= '<button type="button" class="btn btn-primary btn-light btn-sm btn-xs btn-block" onclick="avideoModalIframeSmall(webSiteRootURL+\\\'plugin/TopMenu/addVideoInfo.php?videos_id=\'+row.id+\'\\\');" ><i class="fas fa-edit"></i> Menu items</button>';

        return $btn;
    }

    public function getFooterCode(){
        global $global;
        
        $obj = $this->getDataObject();

        if(!isIframe() && !isEmbed()){
            echo '<link href="' . getURL('plugin/TopMenu/float.css') . '" rel="stylesheet" type="text/css"/>';
            if($obj->showBackToTopButton){
                include $global['systemRootPath'] . 'plugin/TopMenu/floatBackToTop.php';    
            }
            include $global['systemRootPath'] . 'plugin/TopMenu/floatMenu.php';
        }
    }
    
}
