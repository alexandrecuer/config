<?php
namespace emonhub;
/*
 All Emoncms code is released under the GNU Affero General Public License.
 See COPYRIGHT.txt and LICENSE.txt.
 
 ---------------------------------------------------------------------
 Emoncms - open source energy visualisation
 Part of the OpenEnergyMonitor project:
 http://openenergymonitor.org
 */

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');
class Config
{
    public static $config_file = "emonhub.conf";
    public static $logfile = "/var/log/emonhub/emonhub.log";
    public static $restart_log_name = "restart.log";

    public static $log_levels = array(
        1=>"DEBUG",
        2=>"INFO",
        3=>"WARNING",
        4=>"ERROR",
        5=>"CRITICAL"
    );
    public static function get_log_level(){
        if (is_file(Config::$config_file)) {
            $file = file_get_contents(Config::$config_file);
            preg_match('/^\s*loglevel = (.*)$/m', $file, $matches);
        }
        if(!empty($matches[1])) {
            return $matches[1];
        }else{
            return null;
        }
    }
    public static function sub_nav_tabs() {
        include_once "Lib/misc/nav_functions.php";
        $menu = load_menu();
        $tabs = '';
        if(!empty($menu['_config_tabs'])) {
            array_walk($menu['_config_tabs'], function(&$item, $key) {
                $item['markup'] = makeListLink($item);
            });
            $tab_items = array_column($menu['_config_tabs'], 'markup');
            $tabs = view('Modules/config/Views/tabs.php', array('items'=>$tab_items));
            // return <html> with tabs
        }
        return $tabs;
    }
}