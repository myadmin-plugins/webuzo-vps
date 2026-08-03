<?php

// --------------------------------------------------------------------------------
// Softaculous - Softaculous Development Kit
// --------------------------------------------------------------------------------
// // https://www.softaculous.com
// --------------------------------------------------------------------------------
//
// Description :
//   Softaculous_SDK is a Class of Softaculous that allows users to Install and Upgrade
//	 Scripts provided by Softaculous. It also also allows users to Remove, Backup & Restore
//	 the installations made on the server.
//
////////////////////////////////////////////////////////////////////////////////////

if (!defined('SOFTACULOUS')) {
    define('SOFTACULOUS', 1);
}

/*
** Softaculous SDK
** Refer the following guide for examples :
** https://www.softaculous.com/docs/SDK
*/

/**
 * Class Softaculous_SDK
 */
class Softaculous_SDK
{
    // The Login URL
    public $login = '';

    public $debug = 0;

    public $error = [];

    // THE POST DATA
    public $data = [];

    public $scripts = [];
    public $iscripts = [];

    // If some cookies need to be set for this
    public $cookie;

    // Response Format [serialize] [xml] [json]
    //public $format = 'serialize';
    public $format = 'json';

    public $userpass = '';

    /**
     * A Function to Login with Softaculous Parameters.
     *
     * @package      API
     * @author       Jigar Dhulla
     * @param        string $url URL of which response is needed
     * @param        array $post POST DATA
     * @param array $cookies
     * @param bool $header
     * @return string $resp Response of URL
     * @since         4.1.3
     */
    public function curl($url, $post = [], $cookies = [], $header = false)
    {

        // Set the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);

        // Turn off the server and peer verification (TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // Follow redirects
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if (!empty($post)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if ($this->userpass != '') {
            curl_setopt($ch, CURLOPT_USERPWD, "{$this->userpass}");
        }

        // Is there a Cookie
        if (!empty($this->cookie)) {
            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
        }

        // We ONLY need this for directadmin to get the session cookie else we need the Header DISABLED
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Get response from the server.
        $resp = curl_exec($ch);

        // Did we get the file ?
        if ($resp === false) {
            $this->error[] = 'cURL Error : '.curl_error($ch);
        }

        curl_close($ch);
        return $resp;
    }

    /**
     * A Function to Login with Softaculous Parameters.
     *
     * @package      API
     * @author       Jigar Dhulla
     * @param        string $act Actions
     * @param        array $post POST DATA
     * @return       string $resp Response of Actions
     * @since     	 4.1.3
     */
    public function curl_call($act, $post = [])
    {
        $url = $this->login;

        $tmp_url = parse_url($url);
        // This is to set the cookie for Directadmin
        if (isset($tmp_url['port']) && $tmp_url['port'] == '2222' && empty($this->cookie)) {
            $cmd_login = $tmp_url['scheme'].'://'.$tmp_url['host'].':'.$tmp_url['port'].'/CMD_LOGIN';
            if ($this->userpass != '') {
                $username = mb_substr($this->userpass, 0, mb_strpos($this->userpass, ':'));
                $password = mb_substr($this->userpass, mb_strpos($this->userpass, ':'));
                error_log("Softaculous SDK curl_call parsed username {$username} password {$password}");
                $cmd_post = [
                    'username' => $username,
                    'password' => $password,
                    'referer'  => '/',
                ];
            } else {
                $cmd_post = [
                    'username' => $tmp_url['user'],
                    'password' => $tmp_url['pass'],
                    'referer'  => '/',
                ];
            }

            $res = $this->curl($cmd_login, $cmd_post, [], 1);

            $res = explode("\n", $res);

            // Find the cookies
            foreach ($res as $k => $v) {
                if (preg_match('/^'.preg_quote('set-cookie:', '/').'(.*?)$/is', $v, $mat) && empty($this->cookie)) {
                    $this->cookie = trim($mat[1]);
                }
            }
        }

        // Add the ?
        if (false === strpos($url, '?')) {
            $url .= '?';
        }

        // Login Page with Softaculous Parameters
        $url .= $act;

        // Set the API mode
        if (false === strpos($url, 'api=')) {
            $url = $url.'&api='.$this->format;
        }

        return $this->curl($url, $post);
    }

    /**
     * A Function to Login with Softaculous Parameters.
     *
     * @package      API
     * @author       Jigar Dhulla
     * @param        string $act Actions
     * @param        array $post POST DATA
     * @return       string $resp Response of Actions
     * @since     	 4.1.3
     */
    public function curl_unserialize($act, $post = [])
    {
        $resp = $this->curl_call($act, $post);

        return @unserialize($resp);
    }

    /**
     * A Function that will INSTALL scripts. If the DATA is empty script information is returned
     *
     * @package        API
     * @author        Jigar Dhulla
     * @param        int $sid Script ID
     * @param        array $data DATA to POST
     * @param array $autoinstall
     * @return string $resp Response of Action. Default: Serialize
     * @since        4.1.3
     */
    public function install($sid, $data = [], $autoinstall = [])
    {

        // Get the Scripts List
        $this->list_installed_scripts();

        // Script present ?
        if (empty($this->iscripts[$sid])) {
            $this->error[] = 'Script Not Found';
            return false;
        }

        // Is JS / PERL or PHP
        if ($this->iscripts[$sid]['type'] == 'js') {
            $act = '&act=js&soft='.$sid;
        } elseif ($this->iscripts[$sid]['type'] == 'perl') {
            $act = '&act=perl&soft='.$sid;
        } elseif ($this->iscripts[$sid]['type'] == 'java') {
            $act = '&act=java&soft='.$sid;
        } else {
            $act = '&act=software&soft='.$sid;
        }

        if (!empty($autoinstall)) {
            if ($this->format == 'serialize') {
                $act = $act.'&autoinstall='.rawurlencode(base64_encode(serialize($autoinstall)));
            } else {
                $act = $act.'&autoinstall='.rawurlencode(base64_encode(json_encode($autoinstall)));
            }
        }

        // Submit Details
        if (!empty($data)) { // If empty DATA, return script information
            $data['softsubmit'] = 1;
        }

        return $this->curl_call($act, $data);
    }

    /**
     * A Function that will IMPORT existing installations in Softaculous
     *
     * @package		API
     * @author		Jigar Dhulla
     * @param		int $sid Script ID
     * @param		array $data DATA to POST
     * @return		string $resp Response of Actions. Default: Serialize
     * @since		4.1.3
     */
    public function import($sid, $data = [])
    {

        // Get the Scripts List
        $this->list_installed_scripts();

        // Script present ?
        if (empty($this->iscripts[$sid])) {
            $this->error[] = 'Script Not Found';
            return false;
        }

        // Action for Import
        $act = '&act=import&soft='.$sid;

        // Submit details
        $data['softsubmit'] = 1;

        // Get response from the server.
        return $this->curl_call($act, $data);
    }

    /**
     * A Function that will UPDATE scripts
     *
     * @package		API
     * @author		Jigar Dhulla
     * @param		string $insid Installation ID
     * @param		array $data DATA to POST
     * @return		string $resp Response of Actions. Default: Serialize
     * @since		4.1.3
     */
    public function upgrade($insid, $data = [])
    {
        // Action for Upgrade
        $act = '&act=upgrade&insid='.$insid;

        if (!empty($data)) { // If empty DATA, return upgrade information of the installation
            // Submit Details
            $data['softsubmit'] = 1;
        }

        // Get response from the server.
        return $this->curl_call($act, $data);
    }

    /**
     * A Function that will Restore the Backup
     *
     * @package		API
     * @author		Jigar Dhulla
     * @param		string $name Backup File Name
     * @param		array $data DATA to POST
     * @return		string $resp Response of Actions. Default: Serialize
     * @since		4.1.3
     */
    public function restore($name, $data = [])
    {

        // Action for restore
        $act = '&act=restore&restore='.$name;

        // Submit details
        $data['restore_ins'] = 1;

        // Get response from the server.
        return $this->curl_call($act, $data);
    }

    /**
     * A Function that will Remove the Installation
     *
     * @package		API
     * @author		Jigar Dhulla
     * @param		string $insid Installation ID
     * @param		array $data DATA to POST
     * @return		string $resp Response of Actions. Default: Serialize
     * @since		4.1.3
     */
    public function remove($insid, $data = [])
    {

        // Action for Remove
        $act = '&act=remove&insid='.$insid;

        // Submit details
        $data['removeins'] = 1;

        // Get response from the server.
        return $this->curl_call($act, $data);
    }

    /**
     * A Function that will Backup the Installation. Backup process will go in background.
     * You will receive an email in case of any error
     *
     * @package		API
     * @author		Jigar Dhulla
     * @param		string $insid Installation ID
     * @param		array $data DATA to POST
     * @return		string $resp Response of Actions. Default: Serialize
     * @since		4.1.3
     */
    public function backup($insid, $data = [])
    {

        // Action for Backup
        $act = '&act=backup&insid='.$insid;

        // Submit details
        $data['backupins'] = 1;

        // Get response from the server.
        return $this->curl_call($act, $data);
    }

    /**
     * A Function that will remove the Backup of the Installation. Remove Backup process will go in background.
     * You will receive an email in case of any error
     *
     * @package		API
     * @author		Divij Satra
     * @param		string $backup_file Backup File Name e.g webmail.376_48118.2013-01-23_23-11-41.tar.gz
     * @return		string $resp Response of Actions. Default: Serialize
     * @since		4.1.9
     */
    public function remove_backup($backup_file)
    {

        // Action for Backup
        $act = '&act=backups&remove='.$backup_file;

        // Get response from the server.
        return $this->curl_call($act);
    }

    /**
     * A Function that will save the Backup File of the Installation at given path.
     *
     * @package		API
     * @author		Divij Satra
     * @param		string $download_file Backup File Name e.g webmail.376_48118.2013-01-23_23-11-41.tar.gz
     * @param		string $path Path where Backup File will be saved e.g '/opt'
     * @return		void
     * @since		4.1.9
     */
    public function download_backup($download_file, $path = null)
    {

        // Action for Backup
        $act = '&act=backups&download='.$download_file;

        if (null !== $path) {
            if (!is_dir($path)) {
                echo 'The path you provided does not exist please check if the directory exists';
                exit;
            } else {
                $chk = mb_substr($path, -1);
                if ($chk != '/') {
                    $path .= '/';
                }
            }
        } else {
            $path = '';
        }

        $resp = $this->curl_call($act);

        $fp = fopen($path.$download_file, 'wb+');

        fwrite($fp, $resp);

        fclose($fp);

        // Get response from the server.
        echo 'File saved at '.$path . $download_file;
    }

    /**
     * A Function that will list installations
     *
     * @package		API
     * @author		Jigar Dhulla
     * @param		bool $showupdates. [True : Show only installations with update.]
     * @return		array $resp Installations
     * @since		4.1.3
     */
    public function installations($showupdates = false)
    {

        // Get response from the server.
        $resp = $this->curl_call('act=installations&showupdates='.$showupdates);
        $file = $this->curl('http://api.softaculous.com/scripts.php?in='.$this->format);
        if ($this->format == 'serialize') {
            $_resp = unserialize($resp);
        } elseif ($this->format == 'json') {
            $_resp = json_decode($resp, true);
        }
        //if (!is_array($_resp) || !isset($_resp['installations']))
        //myadmin_log('webhosting', 'info', "Softaculous->installations(" . ($showupdates == true ? 'true' : 'false') . ") returned: {$resp}", __LINE__, __FILE__);
        return $_resp['installations'];
    }

    /**
     * A Function that will list scripts
     *
     * @package		API
     * @author		Jigar Dhulla
     * @return array|bool
     * @since		4.1.3
     */
    public function list_scripts()
    {
        if (!empty($this->scripts)) {
            return true;
        }

        // Get response from the server.
        $file = $this->curl('http://api.softaculous.com/scripts.php?in='.$this->format);
        if ($this->format == 'serialize') {
            $this->scripts = unserialize($file);
        } elseif ($this->format == 'json') {
            $this->scripts = json_decode($file, true);
        }

        if (empty($this->scripts)) {
            $this->error[] = 'Scripts were not loaded.';
            return false;
        } else {
            return true;
        }
    }

    /**
     * A Function that will list Backups
     *
     * @package		API
     * @author		Jigar Dhulla
     * @return		array $resp Backups
     * @since		4.1.3
     */
    public function list_backups()
    {

        // Get response from the server.
        $resp = $this->curl_call('act=backups');
        if ($this->format == 'serialize') {
            $resp = unserialize($resp);
        } elseif ($this->format == 'json') {
            $resp = json_decode($resp, true);
        }
        return $resp['backups'];
    }

    /**
     * A Function that will list installed scripts
     *
     * @package		API
     * @author		Jigar Dhulla
     * @return array|bool
     * @since		4.1.3
     */
    public function list_installed_scripts()
    {
        if (!empty($this->iscripts)) {
            return true;
        }

        // Get response from the server.
        $resp = $this->curl_call('');
        if ($this->format == 'serialize') {
            $resp = unserialize(trim($resp));
        } elseif ($this->format == 'json') {
            $resp = json_decode($resp, true);
        }

        $this->iscripts = $resp['iscripts'];

        if (empty($this->iscripts)) {
            $this->error[] = 'Installed Scripts were not loaded.';
            return false;
        } else {
            return true;
        }
    }

    /**
     * Prints result
     *
     * @category	 Debug
     * @param        Array $data
     * @return       array
     */
    public function r_print($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}

// This is for backward compatibility
class Softaculous_API extends Softaculous_SDK
{
}
