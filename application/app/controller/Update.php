<?php
namespace app\app\controller;
use think\Db;
use think\Cache;

class Update extends Base{

 
    private static function compareVersion($version1, $version2){
        if ($version1 == $version2) {
            return 0;
        }
        $version1Array = explode(".", $version1);
        $version2Array = explode(".", $version2);
        $index = 0; 
        $l = count($version1Array);
        $l2 = count($version2Array);
        $minLen = min($l, $l2);
        $diff = 0;
        while ($index < $minLen && ($diff = intval($version1Array[$index]) - intval($version2Array[$index])) == 0) {
            $index++;
        }
        if ($diff == 0) {
            for ($i = $index; $i < $l; $i++) {
                if (intval($version1Array[$i]) > 0) {
                    return 1;
                }
            }
            for ($i = $index; $i < $l2; $i++) {
                if (intval($version2Array[$i]) > 0) {
                    return -1;
                }
            }
            return 0;
        } else {
            return $diff > 0 ? 1 : -1;
        }
    }
}