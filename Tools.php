<?php


/**
 *
 *  * This is an iumio Framework component
 *  *
 *  * (c) RAFINA DANY <dany.rafina@iumio.com>
 *  *
 *  * iumio Framework, an iumio component [https://iumio.com]
 *  *
 *  * To get more information about licence, please check the licence file
 *
 */


namespace iumioFramework\Setup\Requirements;

session_start();
/**
 * Class Tools
 * @package iumioFramework\Setup\Requirements
 * @category Framework
 * @licence  MIT License
 * @link https://framework.iumio.com
 * @author   RAFINA Dany <dany.rafina@iumio.com>
 */
class Tools
{
    public static $php_accept               = 7;
    public static $framework_build_accept   = 201738;
    public static $framework_version_accept = "0.3.8";
    public static $libs                     =   array(
        "public/components/libs/jquery/"       =>  array("jQuery libs not found or have the wrong permissions : 
        check if 'composer install' command has be done or set the READ + WRITE + EXECUTION permissions"),
        "public/components/libs/font-awesome/" =>  array("Font-awesome libs not found or have the wrong permissions 
        : check if 'composer install' command has be done or set the READ + WRITE + EXECUTION permissions"),
        "public/components/libs/bootstrap/"    =>  array("Bootstrap libs not found or have the wrong permissions 
        : check if 'composer install' command has be done or set the READ + WRITE + EXECUTION permissions"),
        "vendor/smarty/"               =>  array("Smarty libs not found or have the wrong permissions 
        : check if 'composer install' command has be done or set the READ + WRITE + EXECUTION permissions"),
    );

    public static $php_extensions           =   array(
        "zip"                               =>
            "libzip is not loaded or not installed : check your extension installation"
    );

    public static $apache_extensions        =   array(
        "mod_rewrite"                       =>
            "mod_rewrite is not found in your server: Please install this extension"
    );

    /** Check the php version
     * @return string If php version is compatible
     */
    final public static function checkPhpVersion()
    {
        $phpv = phpversion();

        if ($phpv >= self::$php_accept) {
            return (json_encode(array("code" => 200, "results" => "OK", "phpv" => $phpv)));
        } else {
            return (json_encode(array("code" => 500, "results" => "NOK", "phpv" => $phpv, "msg" =>
                "The version of PHP is incompatible with iumio Framework. You must have at least version 7.0.0
                 of PHP on your web server.")));
        }
    }

    /** Check the iumio Framework version
     * @return string If iumio Framework version is compatible
     */
    final public static function checkFrameworkBuildVersion()
    {
        $f = json_decode(file_get_contents(__DIR__."/../../elements/config_files/core/framework.config.json"));

        if (!property_exists($f, 'installation')) {
            return (json_encode(array("code" => 500, "results" => "NOK", "fv" => "unknow",
                "msg" => "Property [installation] is undefined in framework.config.json file")));
        }

        if ($f->installation != null) {
            return (json_encode(array("code" => 500, "results" => "NOK", "fv" => "unknow",
                "msg" => "Cannot use iumio installer because you have already one app installed.")));
        }

        $v = $f->edition_version;
        $build = $f->edition_build;
        $e = $f->edition_fullname;
        $st = $f->edition_stage;

        if ($build >= self::$framework_build_accept) {
            $_SESSION['version'] = trim($v);
            return (json_encode(array("code" => 200, "results" => "OK", "fv" => trim($v), "edition" => $e,
                'stage' => $st)));
        } else {
            return (json_encode(array("code" => 500, "results" => "NOK", "fv" => trim($v),
                "msg" => "The version of iumio Framework is incompatible with iumio installer.
                 You must have at least version " . self::$framework_version_accept .
                    " of iumio Framework on your web server.")));
        }
    }


    /**
     * Check the correct permission in directory :
     * /elements
     * /apps
     * @return int Correct permissions or not
     */
    final public static function checkPermission()
    {
        $base =  __DIR__."/../../";
        if (!self::checkIsExecutable($base."elements/") ||
            !self::checkIsReadable($base."elements/") || !self::checkIsWritable($base."elements/")) {
            return (json_encode(array("code" => 500, "results" => "NOK", "wr" => "elements", "msg" =>
                "Folder elements/ does not have correct permission. Must be read, write, executable permission")));
        }

        if (!self::checkIsExecutable($base."apps/") || !self::checkIsReadable($base."apps/") ||
            !self::checkIsWritable($base."apps/")) {
            return (json_encode(array("code" => 500, "results" => "NOK", "wr" => "apps/", "msg" =>
                "Folder apps/ does not have correct permission. Must be read, write, executable permission")));
        }

        if (!self::checkIsExecutable($base."vendor/") || !self::checkIsReadable($base."vendor/") ||
            !self::checkIsWritable($base."vendor/")) {
            return (json_encode(array("code" => 500, "results" => "NOK", "wr" => "vendor/", "msg" =>
                "Folder vendor/ does not have correct permission. Must be read, write, executable permission")));
        }

        if (!self::checkIsExecutable($base."public/components/libs") ||
            !self::checkIsReadable($base."public/components/libs") ||
            !self::checkIsWritable($base."public/components/libs")) {
            return (json_encode(array("code" => 500, "results" => "NOK", "wr" => "public/components/libs/", "msg" =>
                "Folder public/components/libs/ does not have correct permission. 
                Must be read, write, executable permissions")));
        }

        return (json_encode(array("code" => 200, "results" => "OK")));
    }

    /** Check if dir is empty or not
     * @param $dir string Dir path
     * @return bool|null If empty or not
     */
    final public static function isDirEmpty($dir)
    {
        if (!is_readable($dir)) {
            return (null);
        }
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                return (false);
            }
        }
        return (true);
    }

    /**
     * Check if librairies required are installed
     * @return int Are installed or not
     */
    final public static function checkLibrariesRequired()
    {
        $base =  __DIR__."/../../";
        foreach (self::$libs as $lib => $val) {
            if (!is_dir($base.$lib) || !self::checkIsReadable($base.$lib) ||
                !self::checkIsWritable($base.$lib) || self::isDirEmpty($base.$lib)) {
                return (json_encode(array("code" => 500, "results" => "NOK", "libsr" => $lib, "msg" => $val)));
            }
        }

        foreach (self::$php_extensions as $lib => $val) {
            if (!extension_loaded($lib)) {
                return (json_encode(array("code" => 500, "results" => "NOK", "libsr" => $lib, "msg" => $val)));
            }
        }

        return (json_encode(array("code" => 200, "results" => "OK")));
    }

    /** Check if element is readable
     * @param string $path Element path
     * @return bool Is element is readable or not
     */
    final public static function checkIsReadable($path)
    {
        return (is_readable($path));
    }


    /** Check if element is executable
     * @param string $path Element path
     * @return bool Is element is executable or not
     */
    final public static function checkIsExecutable($path)
    {
        return (is_executable($path));
    }

    /** Check if element is writable
     * @param string $path Element path
     * @return bool Is element is writable or not
     */
    final public static function checkIsWritable($path)
    {
        return (is_writable($path));
    }

    /**
     * Processing to create app
     * @param $appname string The application name
     * @param $temp string If template is required
     * @param $version string iumio Framework version
     * @return string App process is a successs
     * @throws \Exception
     */
    final public static function createAppProcess($appname, $temp, $version)
    {
        $base = __DIR__."/../../";
        include_once $base . "vendor/iumio/iumio-framework/Core/Server/Server.php";
        $temdirbase = $base. "vendor/iumio/iumio-framework/Core/Additional/Manager/Module/App/AppTemplate";
        $tempdir = ($temp == "0")? $temdirbase.'/notemplate/{appname}' : $temdirbase.'/template/{appname}';
        \iumioFramework\Core\Server\Server::copy(
            $tempdir,
            $base."apps/".$appname,
            'directory'
        );
        $napp = $base."apps/".$appname;

        // APP
        $f = file_get_contents($napp."/{appname}.php.local");
        $str = str_replace("{appname}", $appname, $f);
        file_put_contents($napp."/{appname}.php.local", $str);
        rename($napp."/{appname}.php.local", $napp."/$appname.php");

        // RT
        $f = file_get_contents($napp."/Routing/default.merc");
        $str = str_replace("{appname}", $appname, $f);
        file_put_contents($napp."/Routing/default.merc", $str);

        // MASTER
        $f = file_get_contents($napp."/Masters/DefaultMaster.php.local");
        $str = str_replace("{appname}", $appname, $f);
        file_put_contents($napp."/Masters/DefaultMaster.php.local", $str);
        rename($napp."/Masters/DefaultMaster.php.local", $napp."/Masters/DefaultMaster.php");

        // REGISTER TO APP CORE
        $f = json_decode(file_get_contents($base."/elements/config_files/core/apps.json"));
        $lastapp = 0;

        if (!is_object($f)) {
            $f = new \stdClass();
        }

        foreach ($f as $one => $val) {
            $lastapp++;
        }

        $f->$lastapp = new \stdClass();
        $f->$lastapp->name = $appname;
        $f->$lastapp->enabled = "yes";
        $f->$lastapp->prefix = "";
        $f->$lastapp->class = "\\".$appname."\\".$appname;
        $ndate = new \DateTime('UTC');
        $f->$lastapp->creation = $ndate;
        $f->$lastapp->update = $ndate;
        $f = json_encode($f, JSON_PRETTY_PRINT);
        file_put_contents($base."/elements/config_files/core/apps.json", $f);
        if ($temp == "1") {
            \iumioFramework\Core\Server\Server::copy(
                $base."/apps/".
                $appname."/Front/Resources/",
                $base."/public/components/apps/dev/".strtolower($appname),
                'directory',
                true
            );
        }

        self::initialJSON();
        unset($_SESSION['version']);
        return ("OK");
    }

    /**
     * Build framework.config.json
     */
    final protected static function initialJSON()
    {
        $base = __DIR__."/../../";
        $f = json_decode(file_get_contents($base."/elements/config_files/core/framework.config.json"));
        $f->installation = new \DateTime();
        $f->location = realpath($base);
        $f->default_env = "dev";
        $rs = json_encode($f, JSON_PRETTY_PRINT);
        file_put_contents($base."/elements/config_files/core/framework.config.json", $rs);
    }
}


/**
 * Check url parameters
 */
if (isset($_REQUEST) && isset($_REQUEST["action"])) {
    if ($_REQUEST["action"] == "phpv") {
        echo (\iumioFramework\Setup\Requirements\Tools::checkPhpVersion());
    } elseif ($_REQUEST["action"] == "fv") {
        echo (\iumioFramework\Setup\Requirements\Tools::checkFrameworkBuildVersion());
    } elseif ($_REQUEST["action"] == "wr") {
        echo (\iumioFramework\Setup\Requirements\Tools::checkPermission());
    } elseif ($_REQUEST["action"] == "libsr") {
        echo (\iumioFramework\Setup\Requirements\Tools::checkLibrariesRequired());
    } elseif ($_REQUEST["action"] == "createapp") {
        if (isset($_REQUEST["appname"], $_REQUEST["template"], $_SESSION["version"]) &&
            $_REQUEST["appname"] != "" && $_REQUEST["template"] != "" && $_SESSION["version"] != "") {
            echo (\iumioFramework\Setup\Requirements\Tools::createAppProcess(
                $_REQUEST["appname"],
                $_REQUEST["template"],
                $_SESSION["version"]
            ));
        } else {
            echo (json_encode(array("code" => 500, "results" => "NOK", "msg" => "Missing required parameter")));
        }
    } else {
        echo (json_encode(array("code" => 500, "results" => "NOK", "msg" => "No valid route")));
    }
}
