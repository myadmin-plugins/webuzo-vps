<?php

// --------------------------------------------------------------------------------
// Webuzo - Softaculous Development Kit
// --------------------------------------------------------------------------------
// // http://www.webuzo.com
// --------------------------------------------------------------------------------
//
// Description :
//   Webuzo_API is a Class of Webuzo that allows users to perform action on all of the features
//   provided by Webuzo like managing FTP, Certificates, Domains, MX records, Email accounts,
//   Forwarders, Zoner files, SSH, IP Block, Installing Tomcat/Rockmongo/AWStats in addition to
//   installing, upgrading, removing, backing up & restoring the installations made on the
//   server.
//
////////////////////////////////////////////////////////////////////////////////////

if (!defined('SOFTACULOUS')) {
    define('SOFTACULOUS', 1);
}


if (!defined('WEBUZO')) {
    define('WEBUZO', 1);
}

include_once(dirname(__FILE__).'/sdk.php');

///////////////////////////////
///////// Webuzo API //////////
///////////////////////////////

class Webuzo_API extends Softaculous_API
{
    // The Login URL
    public $login = '';

    public $debug = 0;

    public $error = [];

    // THE POST DATA
    public $data = [];

    public $apps = []; // List of Apps

    public $installed_apps = []; // List of Installed Apps

    /**
     * Initalize API login
     *
     * @category	 Login
     * @param        string $user The username to LOGIN
     * @param        string $pass The password
     * @param        string $host The host to perform actions
     * @return       void
     */
    public function __construct($user = '', $pass = '', $host = '')
    {
        $this->login = 'https://'.$user.':'.$pass.'@'.$host.':2003/index.php';
    }


    /**
     * Configure webuzo
     *
     * @category	 Configure
     * @param        string $ip The IP Address on configure webuzo
     * @param        string $user The username for webuzo
     * @param        string $email The email for webuzo
     * @param        string $pass The password
     * @param        string $host The Primary domain
     * @param        string $ns1 The nameserver
     * @param        string $ns2 The nameserver
     * @param        string $license The License Key
     * @return       void
     */
    public function webuzo_configure($ip, $user, $email, $pass, $host, $ns1 = '', $ns2 ='', $license = '', $data = [])
    {
        $data['uname'] = $user;
        $data['email'] = $email;
        $data['pass'] = $pass;
        $data['rpass'] = $pass;
        $data['domain'] = $host;
        $data['ns1'] = $ns1;
        $data['ns2'] = $ns2;
        $data['lic'] = $license;
        $data['submit'] = 1;
        $data['api'] = 1;

        $this->login = 'http://'.$ip.':2004/install.php?';
        $return = $this->curl($this->login, $data);
        $this->chk_error();
        return $return;
    }

    /**
     * A Function that will INSTALL apps. If the DATA is empty script information is retured
     *
     * @package		API
     * @author		Jigar Dhulla
     * @param		int $sid Script ID
     * @param		array $data DATA to POST
     * @return		string $resp Response of Action. Default: Serialize
     * @since		4.1.3
     */
    public function install_app($appid)
    {

        // Get the Scripts List
        $this->list_apps();

        // Script present ?
        if (empty($this->apps[$appid])) {
            $this->error[] = 'App Not Found';
            return false;
        }

        $act = 'act=apps&app='.$appid.'&install=1';

        $resp = $this->curl_call($act);
        $this->chk_error();
        return $resp;
    }

    /**
     * List Services
     *
     * @category	 Database
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function list_services()
    {
        $act = 'act=services';

        $resp = trim($this->curl_call($act));
        $this->chk_error();
        return $resp;
    }

    /**
     * A Function that will REMOVE apps. If the DATA is empty script information is retured
     *
     * @package		API
     * @author		Jigar Dhulla
     * @param		int $sid Script ID
     * @param		array $data DATA to POST
     * @return		string $resp Response of Action. Default: Serialize
     * @since		4.1.3
     */
    public function remove_app($appid)
    {

        // Get the Scripts List
        $iapps = $this->list_installed_apps();

        // Script present ?
        if (empty($iapps[$appid])) {
            $this->error[] = 'App Not Found';
            return false;
        }

        $act = 'act=apps&app='.$appid.'&remove=1';

        $resp = $this->curl_call($act);
        $this->chk_error();
        return $resp;
    }

    /**
     * A Function that will list scripts
     *
     * @package		API
     * @author		Jigar Dhulla
     * @return		array $scripts List of Softaculous Scripts
     * @since		4.1.3
     */
    public function list_apps()
    {
        if (!empty($this->apps)) {
            return true;
        }

        // Get response from the server.
        $data = $this->curl_unserialize('');

        $this->apps = $data['apps'];

        if (empty($this->apps)) {
            $this->error[] = 'Apps were not loaded.';
            return false;
        } else {
            return true;
        }
    }

    /**
     * A Function that will list installed scripts
     *
     * @package		API
     * @author		Jigar Dhulla
     * @return		array $scripts List of Installed Softaculous Scripts
     * @since		4.1.3
     */
    public function list_installed_apps()
    {
        if (!empty($this->installed_apps)) {
            return $this->installed_apps;
        }

        // Get response from the server.
        $resp = $this->curl_unserialize('act=apps_installations');

        $this->installed_apps = $resp['apps_ins'];

        return $resp['apps_ins'];
    }

    ///////////////////////////////////////////////////////////////////////////////
    //							CATEGORY : FEATURES								 //
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Check login error
     *
     * @category	 error
     * @return       array
     */
    public function chk_error()
    {
        if (!empty($this->error)) {
            return $this->r_print($this->error[0]);
        }
    }

    /**
     * List Domains
     *
     * @category	 Domain
     * @return		string $resp Response of Action. Default: Serialize
     */
    public function list_domains()
    {
        $act = 'act=domainmanage';
        $resp = $this->curl_call($act);
        $this->chk_error();
        return trim($resp);
    }

    /**
     * Add Domain
     *
     * @category	 Domain
     * @param        string $domain The domain to add
     * @param		 (Optional) string $domainpath The path for an ADD-ON domain
     * @param		 (Optional) string $ip Different IP Address for domain
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function add_domain($domain, $domainpath = '', $ip = '')
    {

        // The act
        $act = 'act=domainadd';

        $data['domain'] = $domain;
        $data['domainpath'] = $domainpath;
        $data['isaddon'] = !empty($domainpath);
        $data['ip'] = !empty($ip);

        $data['submitdomain'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Delete Domain
     *
     * @category	 Domain
     * @param        string $domain The domain to delete
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function delete_domain($domain)
    {

        // The act
        $act = 'act=domainmanage';

        $data['delete_domain_name'] = $domain;

        $data['delete_domain_id'] = 1;
        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Change ROOT/Endusers's Password
     *
     * @category	 Password
     * @param        string $pass The NEW password for the USER
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function change_password($pass, $user = '')
    {

        // The act
        $act = 'act=changepassword';
        if (!empty($user)) {
            $data['user'] = $user;
        }
        $data['newpass'] = $data['conf'] = $pass;
        $data['changepass'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Change File Manager Password
     *
     * @category	 Password
     * @param        string $pass The NEW password for the File manager
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function change_fileman_pwd($pass)
    {

        // The act
        $act = 'act=changepassword';
        $data['filepass'] = $pass;
        $data['changefilepass'] = 1;
        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Change Apache Tomcat Manager's Password
     *
     * @category	 Password
     * @param        string $pass The NEW password for the Apache Tomcat
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function change_tomcat_pwd($pass)
    {

        // The act
        $act = 'act=changepassword';
        $data['tomcatpass'] = $pass;
        $data['changetomcatpass'] = 1;
        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * List FTP users
     *
     * @category	 FTP
     * @return       array
     */
    public function list_ftpuser()
    {
        $act = 'act=ftp';
        $resp = $this->curl_call($act);
        $this->chk_error();
        return $resp;
    }

    /**
     * Add FTP user
     *
     * @category	 FTP
     * @param        string $user The FTP username
     * @param        string $pass The password for the FTP user
     * @param        string $directory The Directory path for the FTP users relative to /HOME/USER
     * @param        string $quota_limit (Optional) Define a quota for the user
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function add_ftpuser($user, $pass, $directory, $quota_limit = '')
    {
        $act = 'act=ftp_account';

        $data['login'] = $user;
        $data['newpass'] = $data['conf'] = $pass;
        $data['dir'] = $directory;
        if (!empty($quota_limit)) {
            $data['quota'] = 'limited';
            $data['quota_limit'] = $quota_limit;
        } else {
            $data['quota'] = 'unlimited';
        }
        $data['create_acc'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Edit FTP user
     *
     * @category	 FTP
     * @param        string $user FTP user to EDIT data
     * @param        string $quota_limit (Optional) Specify quota limit to the user
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function edit_ftpuser($user, $quota_limit = '')
    {
        $act = 'act=ftp';

        $data['edit_ftp_user'] = $user;
        if (!empty($quota_limit)) {
            $data['quota'] = 'limited';
            $data['quota_limit'] = $quota_limit;
        } else {
            $data['quota'] = 'unlimited';
        }
        $data['edit_record'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Change FTP User's Password
     *
     * @category	 FTP
     * @param        string $user FTP user to change Password
     * @param        string $pass New password for the FTP user
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function change_ftpuser_pass($user, $pass)
    {
        $act = 'act=editftp';

        $data['edit_ftp_user_pass'] = $user;
        $data['newpass'] = $data['conf'] = $pass;
        $data['changepass'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Delete FTP user
     *
     * @category	 FTP
     * @param        string $user FTP user to delete
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function delete_ftpuser($user)
    {
        $act = 'act=ftp';

        $data['delete_ftp_user'] = $user;
        $data['delete_fuser_id'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * List FTP Connections
     *
     * @category	 FTP
     * @return       array
     */
    public function list_ftp_connections()
    {
        $act = 'act=ftp_connections';

        $resp = $this->curl_call($act);

        $this->chk_error();
        return $resp;
    }

    /**
     * Delete FTP Connection
     *
     * @category	 FTP
     * @param        string		$ftp_connection_id	FTP Connection Process ID
     * @return		 string		$resp				Response of Action. Default: Serialize
     */
    public function delete_ftp_connection($ftp_connection_id)
    {
        $act = 'act=ftp_connections';

        $data['ftp_connection_pid'] = $ftp_connection_id;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    ///////////////////////////////////////////////////////////////////////////////
    //							CATEGORY : DATABASE								 //
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * List Database with its size and users
     *
     * @category	 Database
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function list_database()
    {
        $act = 'act=dbmanage';

        $resp = trim($this->curl_call($act));
        $this->chk_error();
        return $resp;
    }

    /**
     * Add Database
     *
     * @category	 database
     * @param        string $db_name Database name to create
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function add_database($db_name)
    {
        $act = 'act=dbmanage';

        $data['db'] = $db_name;
        $data['submitdb'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return trim($resp);
    }

    /**
     * Delete Database
     *
     * @category	 database
     * @param        string $db_name Database name to delete
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function delete_database($db_name)
    {
        $act = 'act=dbmanage';

        $data['delete_db'] = $data['db'] = $db_name;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * List Database Users
     *
     * @category	 Database
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function list_db_user()
    {
        $act = 'act=dbmanage';

        $resp = trim($this->curl_call($act));
        $this->chk_error();
        return $resp;
    }

    /**
     * Add Database User
     *
     * @category	 database
     * @param        string $db_user Database username to ADD
     * @param        string $pass Password for the database user
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function add_db_user($db_user, $pass)
    {
        $act = 'act=dbmanage';

        $data['dbuser'] = $db_user;
        $data['dbpassword'] = $pass;
        $data['submituserdb'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return trim($resp);
    }

    /**
     * Delete Database user
     *
     * @category	 database
     * @param        string $db_user Database user to delete
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function delete_db_user($db_user)
    {
        $act = 'act=dbmanage';

        $data['delete_dbuser'] = $db_user;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return trim($resp);
    }

    /**
     * Set Privileges for a User to a specific database
     *
     * @category	 database
     * @param        string $database Database name to ADD privileges
     * @param        string $db_user Database users name to ADD privileges
     * @param        string $host Database host
     * @param        string $prilist Set of privileges to be given to the User
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function set_privileges($database, $db_user, $host, $prilist)
    {
        $act = 'act=dbmanage';

        $data['dbname'] = $database;
        $data['dbuser'] = $db_user;
        $data['host'] = $host;
        $data['prilist'] = $prilist;
        $data['submitpri'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return trim($resp);
    }

    //////////////////////////////////////////////////////////////////////////////
    //					   CATEGORY : Advance Settings							//
    //////////////////////////////////////////////////////////////////////////////

    /**
     * Edit settings
     *
     * @category	 Advance settings
     * @param        string $email Specify email address to SET
     * @param        int $ins_email (Optional) Set 1 to receive installation emails, otherwise 0
     * @param        int $rem_email (Optional) Set 1 to receive installations removal email,
                      otherwise 0
     * @param        int $edit_email (Optional) Set 1 to receive installations editting email,
                      otherwise 0
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function edit_settings($email, $ins_email = '', $rem_email = '', $edit_email = '')
    {
        $act = 'act=email';

        $data['email'] = $email;
        $data['ins_email'] = empty($ins_email);
        $data['rem_email'] = empty($rem_email);
        $data['editdetail_email'] = empty($edit_email);
        $data['editemailsettings'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Manage Services
     *
     * @category	 Advanced Settings
     * @param        string $service_name Specify the service to restart
                      E.g exim, dovecot, tomcat, httpd, named, pure-ftpd, mysqld
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function manage_service($service_name, $action = 'restart')
    {
        $act = 'act=services';

        $data['service_name'] = $service_name;
        $data['action'] = $action;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Enable/Disable suPHP
     *
     * @category	 Security
     * @param        string $action Specify on/off to START/STOP suPHP respectively
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function manage_suphp($action)
    {
        $act = 'act=apache_settings';

        if ($action != 'off') {
            $data['suphpon'] = 1;
        } else {
            $data['suphpon'] = null;
        }
        $data['editapachesettings'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Enable NGINX Proxy
     *
     * @category	 SystemApps
     * @param        Integer $port Port for Proxy Server
     * @param        Integer $htaccess  - 0 to enable .htaccess
     *									- 1 to disable .htaccess
     * @param        String $proxy_server - Either "httpd" or "httpd2"
     * @return		 array $resp
     */
    public function enable_proxy($port, $htaccess, $proxy_server)
    {
        $act = 'act=apache_settings';

        $data['port'] = $port;
        $data['ht_check'] = $htaccess;
        $data['webserver'] = $proxy_server;
        $data['enable_proxy'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Disable NGINX Proxy
     *
     * @category	 SystemApps
     * @param        String $proxy_server - Default Webserver to be set - "httpd,httpd2,nginx,lighttpd"
     * @return		 array $resp
     */
    public function disable_proxy($set_default_webserver)
    {
        $act = 'act=apache_settings';

        $data['webserver'] = $set_default_webserver;
        $data['disable_proxy'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    //////////////////////////////////////////////////////////////////////////////
    //					   CATEGORY : Server Settings							//
    //////////////////////////////////////////////////////////////////////////////

    /**
     * List DNS Record
     *
     * @category	 Server Settings
     * @param        string $domain Specify domain to list DNS records
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function list_dns_record($domain)
    {
        $act = 'act=advancedns';

        $data['domain'] = $domain;

        $resp =$this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Add DNS Record
     *
     * @category	 Server Settings
     * @param        string $domain Specify domain to ADD DNS record
     * @param        string $name Specify record name
     * @param        string $ttl Specify TTL
     * @param        string $type Specify TYPE of record
     * @param        string $address Specify destination address
     * @return		 string $resp Response of Action. Default: Serialize
     */
    public function add_dns_record($domain, $name, $ttl, $type, $address)
    {
        $act = 'act=advancedns';

        $data['selectdomain'] = $domain;
        $data['name'] = $name;
        $data['ttl'] =  $ttl;
        $data['selecttype'] = $type;
        $data['address'] = $address;
        $data['create_record'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Edit DNS Record
     *
     * @category	 Server Settings
     * @param        string $id Specify ID of record to EDIT
     * @param        string $domain Specify domain to ADD DNS record
     * @param        string $name Specify record name
     * @param        string $ttl Specify TTL
     * @param        string $type Specify TYPE of record
     * @param        string $address Specify destination address
     * @return		 string $resp Response of Action. Default: Serialize
     * @return       array
     */
    public function edit_dns_record($id, $domain, $name, $ttl, $type, $address)
    {
        $act = 'act=advancedns';

        $data['edit_record'] = $id;
        $data['domain_name'] = $domain;
        $data['name'] = $name;
        $data['ttl'] = $ttl;
        $data['type'] = $type;
        $data['record'] = $address;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Delete DNS Record
     *
     * @category	 Server Settings
     * @param        string $id ID of Dns record for delete
     * @param        string $domain Domain for the DNS record for delete
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function delete_dns_record($id, $domain)
    {
        $act = 'act=advancedns';

        $data['delete_record'] = $id;	// Specify record to be DELETED
        $data['domain_name'] = $domain;	// Specify the DOMAIN

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * List CRON
     *
     * @category	 Server Settings
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function list_cron()
    {
        $act = 'act=cronjob';

        $resp = $this->curl_call($act);
        $this->chk_error();
        return $resp;
    }

    /**
     * Add a CRON
     *
     * @category	 Server Settings
     * @param        string $minute Minute of the cron part
     * @param        string $hour Hour of the cron part
     * @param        string $day Day of the cron part
     * @param        string $month Month of the cron part
     * @param        string $weekday Weekend of the cron part
     * @param        string $cmd Command of the cron part
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function add_cron($minute, $hour, $day, $month, $weekday, $cmd)
    {
        $act = 'act=cronjob';

        $data['minute'] = $minute;	// Specify minutes
        $data['hour'] = $hour;		// Specify hour
        $data['day'] = $day;		// Specify day
        $data['month'] = $month;	// Specify month
        $data['weekday'] = $weekday;// Specify weekday
        $data['cmd'] = $cmd;		// Specify command
        $data['create_record'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Edit CRON
     *
     * @category	 Server Settings
     * @param        string $id ID of the cron record. Get from the list of cron
     * @param        string $minute Minute of the cron part
     * @param        string $hour Hour of the cron part
     * @param        string $day Day of the cron part
     * @param        string $month Month of the cron part
     * @param        string $weekday Weekend of the cron part
     * @param        string $cmd Command of the cron part
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function edit_cron($id, $minute, $hour, $day, $month, $weekday, $cmd)
    {
        $act = 'act=cronjob';

        $data['minute'] = $minute;	// Specify minutes
        $data['hour'] = $hour;		// Specify hour
        $data['day'] = $day;		// Specify day
        $data['month'] = $month;	// Specify month
        $data['weekday'] = $weekday;// Specify weekday
        $data['cmd'] = $cmd;		// Specify command
        $data['edit_record'] = 'c'. $id;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return trim($resp);
    }

    /**
     * Delete CRON
     *
     * @category	 Server Settings
     * @param        string $id ID of the cron record. Get from the list of cron
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function delete_cron($id)
    {
        $act = 'act=cronjob';

        $data['delete_record'] = 'c'. $id;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return trim($resp);
    }

    //////////////////////////////////////////////////////////////////////////
    //					   		CATEGORY : Security							//
    //////////////////////////////////////////////////////////////////////////

    /**
     * List SSL Key
     *
     * @category	 Security
      * @return		string $resp Response of Actions. Default: Serialize
     */
    public function list_ssl_key()
    {
        $act = 'act=sslkey';

        $resp = $this->curl_call($act);
        $this->chk_error();
        return $resp;
    }

    /**
     * Create SSL Key
     *
     * @category	 Security
     * @param        string $description Domain name or any name for the SSL Key
     * @param        string $keysize Size of the SSl Key
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function create_ssl_key($description, $keysize = '')
    {
        $act = 'act=sslkey';

        $data['selectdom'] = $description;	// Specify DOMAIN
        $data['keysize'] = (empty($keysize) ? '1024' : $keysize);	// Specify Key size
        $data['create_key'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Upload SSL Key
     *
     * @category	 Security
     * @param        string $description Domain name or any name for the SSL Key
     * @param        string $keypaste Entire SSL Key
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function upload_ssl_key($description, $keypaste)
    {
        $act = 'act=sslkey';

        $data['selectdom'] = $description;	// Specify DOMAIN
        $data['kpaste'] = $keypaste;		// Specify KEY contents
        $data['install_key'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Detail SSL Key
     *
     * @category	 Security
     * @param        string $domain Specify domain name to detail view of SSL Key
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function detail_ssl_key($domain)
    {
        $act = 'act=sslkey';

        $data['detail_record'] = $domain;		// Specify DOMAIN

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Delete SSL Key
     *
     * @category	 Security
     * @param        string $domain Specify domain name to delete SSL Key
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function delete_ssl_key($domain)
    {
        $act = 'act=sslkey';

        $data['delete_record'] = $domain;		// Specify DOMAIN

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * List SSL CSR
     *
     * @category	 Security
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function list_ssl_csr()
    {
        $act = 'act=sslcsr';

        $resp = $this->curl_call($act);
        $this->chk_error();
        return $resp;
    }

    /**
     * Create SSL CSR
     *
     * @category	 Security
     * @param        string $domain Domain name for the CSR
     * @param        string $country_code Two latter Country Code
     * @param        string $state Name of the State
     * @param        string $locality Name of the Location
     * @param        string $org Name of the Organitaion
     * @param        string $org_unit Name of the Organitaion unit
     * @param        string $passphrase Password prase
     * @param        string $email Email address
     * @param        string $key KEY use for creating new csr. if you want to generate new key then pass "newkey" as argument.
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function create_ssl_csr($domain, $country_code, $state, $locality, $org, $org_unit, $passphrase, $email, $key)
    {
        $act = 'act=sslcsr';

        $data['domain'] = $domain;	// Specify DOMAIN - Note : Domain should have a Private KEY
        $data['country'] = $country_code;	// Specify Country Code
        $data['state'] = $state;			// Specify State
        $data['locality'] = $locality;		// Specify Locality
        $data['organisation'] = $org;		// Specify Organization
        $data['orgunit'] = $org_unit;		// Specify Organization Unit
        $data['pass'] = $passphrase;		// Specify PASSPHRASE
        $data['email'] = $email;			// Specify Email
        $data['selectkey'] = $key;		    // Specify key. if you want to generate new key then pass "newkey" as argument.
        $data['createcsr'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Detail SSL CSR
     *
     * @category	 Security
     * @param        string $domain Specify domain name to detail view of SSL CSR
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function detail_ssl_csr($domain)
    {
        $act = 'act=sslcsr';

        $data['detail_record'] = $domain;	// Specify DOMAIN

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Delete SSL CSR
     *
     * @category	 Security
     * @param        string $domain Specify domain name to delete SSL CSR
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function delete_ssl_csr($domain)
    {
        $act = 'act=sslcsr';

        $data['delete_record'] = $domain;	// Specify DOMAIN

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * List SSL Certificate
     *
     * @category	 Security
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function list_ssl_crt()
    {
        $act = 'act=sslcrt';

        $resp = $this->curl_call($act);
        $this->chk_error();
        return $resp;
    }

    /**
     * Create SSL Certificate
     *
     * @category	 Security
     * @param        string $domain Domain for the Certificate
     * @param        string $country_code Two latter Country Code
     * @param        string $state Name of the State
     * @param        string $locality Name of the Location
     * @param        string $org Name of the Organitaion
     * @param        string $org_unit Name of the Organitaion unit
     * @param        string $email Email address
     * @param        string $key KEY use for creating new csr. if you want to generate new key then pass "newkey" as argument.
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function create_ssl_crt($domain, $country_code, $state, $locality, $org, $org_unit, $email, $key)
    {
        $act = 'act=sslcrt';

        $data['domain'] = $domain;			// Specify DOMAIN - Note : Domain should have a KEY
        $data['country'] = $country_code;	// Specify Country Code
        $data['state'] = $state;			// Specify State
        $data['locality'] = $locality;		// Specify Locality
        $data['organisation'] = $org;		// Specify Organization
        $data['orgunit'] = $org_unit;		// Specify Organization Unit
        $data['email'] = $email;			// Specify Email
        $data['selectkey'] = $key;		    // Specify key. if you want to generate new key then pass "newkey" as argument.
        $data['create_crt'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Upload SSL Certificate
     *
     * @category	 Security
     * @param        string $keypaste Entire certificate.
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function upload_ssl_crt($keypaste)
    {
        $act = 'act=sslcrt';

        $data['kpaste'] = $keypaste;			// Specify Certificatae Contents
        $data['install_crt'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Detail SSL Certificate
     *
     * @category	 Security
     * @param        string $domain Specify domain name to detail view of SSL Certificat
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function detail_ssl_crt($domain)
    {
        $act = 'act=sslcrt';

        $data['detail_record'] = $domain;	// Specify DOMAIN

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Delete SSL Certificate
     *
     * @category	 Security
     * @param        string $domain Specify domain name to delete SSL Certificat
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function delete_ssl_crt($domain)
    {
        $act = 'act=sslcrt';

        $data['delete_record'] = $domain;	// Specify DOMAIN

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * List Blocked IP.
     * @return		string $resp Response of Actions. Default: Serialize
     */
    public function list_ipblock()
    {
        $act = 'act=ipblock';

        $resp = $this->curl_call($act);
        $this->chk_error();
        return $resp;
    }

    /**
     * Block IP
     *
     * @category	 Security
     * @param        string $ip IP Address for block
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function add_ipblock($ip)
    {
        $act = 'act=ipblock';

        $data['dip'] = $ip;		// Specify IP to block
        $data['add_ip'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Delete Blocked IP
     *
     * @category	 Security
     * @param        string $ip IP Address for unblock
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function delete_ipblock($ip)
    {
        $act = 'act=ipblock';

        $data['delete_ip'] = $ip;	// Specify IP to unblock
        $data['delete_record'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Enable/Disable SSH Access
     *
     * @category	 Security
     * @param        string $action Action should be on or off
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function ssh_access($action)
    {
        $act = 'act=ssh_access';

        // Specify on/off to enable/disable SSH access respectively.
        if ($action == 'off') {
            $data['sshon'] = null;
        } else {
            $data['sshon'] = 1;
        }
        $data['editsshsettings'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    //////////////////////////////////////////////////////////////////////////////
    //					   		CATEGORY : Email Server							//
    //////////////////////////////////////////////////////////////////////////////

    /**
     * List Email Users
     *
     * @category	 Email
     * @param        string $domain Specify domain name for the Email User Account list
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function list_emailuser($domain)
    {
        $act = 'act=email_account';

        $data['domain'] = $domain;		// Specify Domain

        $resp = trim($this->curl_call($act, $data));
        $this->chk_error();
        return $resp;
    }

    /**
     * Add Email User
     *
     * @category	 Email
     * @param        string $domain Domain for the Email User Account to add
     * @param        string $emailuser Email user name for add
     * @param        string $password Password for user
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function add_emailuser($domain, $emailuser, $password)
    {
        $act = 'act=email_account';

        $data['selectdomain'] = $domain;	// Specify DOMAIN
        $data['login'] = $emailuser;		// Specify email user to create
        $data['newpass'] = $data['conf'] = $password;		// Specfy PASSWORD
        $data['create_acc'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Change Email Users' Password
     *
     * @category	 Email
     * @param        string $domain Domain for the Email User Account for change passsword
     * @param        string $emailuser Email user name for change passsword
     * @param        string $password New password for user
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function change_email_user_pass($domain, $emailuser, $password)
    {
        $act = 'act=email_account';

        $data['domain_name'] = $domain;		// Specify DOMAIN
        $data['edit_record'] = $emailuser;	// Specify record to be EDITTED
        $data['cpass'] = $password;			// Specify PASSWORD

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Delete Email Users
     *
     * @category	 Email
     * @param        string $domain Domain for the Email User Account for delete
     * @param        string $emailuser Email user name for delete
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function delete_email_user($domain, $emailuser)
    {
        $act = 'act=email_account';

        $data['domain_name'] = $domain;		// Specify DOMAIN
        $data['delete_record'] = $emailuser;// Specify record to be DELETED

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * List Email Forwarder
     *
     * @category	 Email
     * @param        string $domain Domain for the Email Forwarder list
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function list_emailforward($domain)
    {
        $act = 'act=email_forward';

        $data['domain'] = $domain;		// Specify DOMAIN

        $resp = trim($this->curl_call($act, $data));
        $this->chk_error();
        return $resp;
    }

    /**
     * Add Email Forwarder
     *
     * @category	 Email
     * @param        string $domain Domain for the Email Forwarder add
     * @param        string $forward_address Forwarder name to add
     * @param        string $forward_to To whome it is forwarded
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function add_emailforward($domain, $forward_address, $forward_to)
    {
        $act = 'act=email_forward';

        $data['selectdomain'] = $domain;		// Specify DOMAIN
        $data['addemail'] = $forward_address;	// Specify Senders Email Address
        $data['sendemail'] = $forward_to;		// Specify Email Address to be Forwarded TO
        $data['create_acc'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Delete Email Forwarder
     *
     * @category	 Email
     * @param        string $domain Domain for the Email Forwarder delete
     * @param        string $forward_address Forwarder name
     * @param        string $forward_to To whome it is forwarded
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function delete_email_forward($domain, $forward_address, $forward_to)
    {
        $act = 'act=email_forward';

        $data['domain_name'] = $domain;		// Specify DOMAIN
        $data['forward_name'] = $forward_address;	// Specify Forwarders Name
        $data['to_name'] = $forward_to;		// Specify Recepients Name
        $data['delete_record'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * List MX record
     *
     * @category	 Email Server
     * @param        string $domain Domain for the MX Record list
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function list_mx_entry($domain)
    {
        $act = 'act=mxentry';

        $data['domain'] = $domain;		// Specify DOMAIN

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Add MX record
     *
     * @category	 Email Server
     * @param        string $domain Domain for the MX Record add
     * @param        string $priority Priority for the MX Record Entry
     * @param        string $destination Destination address
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function add_mx_entry($domain, $priority, $destination)
    {
        $act = 'act=mxentry';

        $data['selectdomain'] = $domain;	// Specify the DOMAIN
        $data['priority'] = $priority;		// Specify the PRIORITY
        $data['destination'] = $destination;// Specify the Destination
        $data['create_record'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Edit MX record
     *
     * @category	 Email Server
     * @param        string $domain Domain for the MX Record edit
     * @param        string $record Record no of the Entry
     * @param        string $priority Priority for the MX Record Entry
     * @param        string $destination Destination address
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function edit_mx_entry($domain, $record, $priority, $destination)
    {
        $act = 'act=mxentry';

        $data['domain_name'] = $domain;		// Specify the DOMAIN
        $data['edit_record'] = $record;		// Specify the record to be EDITTED
        $data['editpriority'] = $priority;	// Specify the PRIORITY
        $data['editdestination'] = $destination;	// Specify DESTINATION

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Delete MX record
     *
     * @category	 Email Server
     * @param        string $domain Domain for the MX Record delete
     * @param        string $record Record no of the Entry
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function delete_mx_entry($domain, $record)
    {
        $act = 'act=mxentry';

        $data['domain_name'] = $domain;
        $data['delete_record'] = $record;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Set Default
     *
     * @category	 Server
     * @param        string $service Set the Default Service - php53/php54/nginx/httpd
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function set_defaults($service)
    {
        $act = 'act=service_manager';

        $php = ['php53', 'php54', 'php55'];
        $server = ['httpd', 'nginx', 'lighttpd'];

        if (in_array($service, $php)) {
            $data['default_php'] = $service;
        }

        if (in_array($service, $server)) {
            $data['webserver'] = $service;
        }

        $data['service_manager'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    //////////////////////////////////////////////////////////////////////////
    //					   CATEGORY : Server Info							//
    //////////////////////////////////////////////////////////////////////////

    /**
     * Show Error Log
     *
     * @category	 Server Info
     * @param        string $domain Domain for the error log (Opional)
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function show_error_log($domain = '')
    {
        $act = 'act=errorlog';

        if (empty($domain)) {
            $data['domain_log'] = 'error_log';
        } else {
            $data['domain_log'] = $domain .'.err';
        }

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Enable / Disable PHP Extensions
     *
     * @category	 Configuration
     * @param        string (Optional) $extensions Extensions to enable
                             (Empty results in list of Extensions and their status)
     * @return		 array	$resp Response of Action. Default: Serialize
     */
    public function handle_php_ext($extensions = '')
    {
        $act = 'act=php_ext';

        if (!empty($extensions)) {
            $data['extensions'] = $extensions;
            $data['save_ext'] = 1;
        }

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return trim($resp);
    }

    /**
     * Networking Tools
     *
     * @category	 DNS
     * @param        string (Optional) $action Lookup by default
                             (Available options are 'lookup' & 'traceroute')
     * @return		 array	$resp Response of Action. Default: Serialize
     */
    public function dns_lookup($domain, $action = 'lookup')
    {
        $act = 'act=network_tools';

        $data['action'] = $action;
        $data['domain_name'] = $domain;
        $data['domain_lookup'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return trim($resp);
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

    /**
     * Set Bandwidth Limit
     *
     * @category	 Bandwidth
     * @param        string $total_bandwidth Set your total available bandwidth in GB(Set 0 for unlimited)
     * @param        string $bandwiwdth_email_alert Email alert limit value in GB
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function set_bandwidth($total_bandwidth = '', $bandwidth_email_alert = '')
    {
        $act = 'act=bandwidth';

        $data['bandwidth_up_limit'] = $total_bandwidth;	    // Specify the total bandwidth in GB
        $data['bandwidth_limit'] = $bandwidth_email_alert;	// Specify the email alert limit in GB
        $data['bandwidth_record'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * Reset Bandwidth Limit
     *
     * @category	 Bandwidth
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function reset_bandwidth()
    {
        $act = 'act=bandwidth';

        $data['bandwidth_reset'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /**
     * List Login Logs
     *
     * @category	 Login
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function list_login_logs()
    {
        $act = 'act=login_logs';

        $resp = $this->curl_call($act);
        $this->chk_error();
        return $resp;
    }

    /**
     * Delete all Login Logs
     *
     * @category	 Login
     * @return		 string $resp Response of Actions. Default: Serialize
     */
    public function delete_login_logs()
    {
        $act = 'act=login_logs&delete_all=1';

        $resp = $this->curl_call($act);
        $this->chk_error();
        return $resp;
    }

    /**
     * List Protected Users
     *
     * @category	 Apache
     * @return		 Array	$resp	Response of Actions. Default: Serialize
     */
    public function list_protected_users()
    {
        $act = 'act=pass_protect_dir';

        $resp = $this->curl_call($act);
        $this->chk_error();
        return $resp;
    }

    /*
     *	----------------------------------
     *	Password Protect Directory
     *	----------------------------------
     *
     *	@category	Apache
     *	@param		String	$path - Path to the directory to be password protected
     *	@param		String	$uname - User to be added for the directory
     *	@param		String	$pass - Password to the user for the directory
     *	@param		String	[OPTIONAL] $name - Alias Name for the directory.
     *	@return     Boolean
     *	@version    2.2.0
     *
     */
    public function add_pass_protect_dir($path, $uname, $pass, $name = '')
    {
        $act = 'act=pass_protect_dir';

        $data['dir_path'] = $path;	// Path to password protect (No leading slashes)
        $data['username'] = $uname;	// Alphanumeric Username
        $data['password'] = $data['re_password'] = $pass;	// Password should not be less than 5 characters

        if (!empty($name)) { // Alias name
            $data['dir_name'] = $name;
        }
        $data['add_pass_protect'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /*
     *	-------------------------------------------
     *	Delete Password Protected Directory User
     *	-------------------------------------------
     *
     *	@category	Apache
     *	@param		String	$uname - User to be deleted
     *	@param		String	$path - Path to the password protected directory
     *	@return		Boolean
     *	@version    2.2.0
     *
     */
    public function delete_pass_protected_user($uname, $path)
    {
        $act = 'act=pass_protect_dir';

        $data['delete_record'] = $uname;
        $data['path'] = $path;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return trim($resp);
    }

    /*
     *	-------------------------------------------
     *	Read extra configuration file path
     *	-------------------------------------------
     *
     *	@category	Configuration
     *	@param		String	$domain Domain for the extra path
     *	@return		array
     *	@version    2.2.6
     *
     */
    public function read_extra_conf($domain)
    {
        $act = 'act=extra_conf';

        $data['domain'] = $domain;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /*
     *	-------------------------------------------
     *	Add extra configuration file path
     *	-------------------------------------------
     *
     *	@category	Configuration
     *	@param		String	$domain Domain for the extra path
     *	@param		String	$path Path of your extra conf
     *	@param		String	$webserver Webserver ID for whic you want to add, you will get it from w_list array key
     *	@return		string $resp Response of Actions. Default: Serialize
     *	@version    2.2.6
     *
     */

    public function add_extra_conf($domain, $path, $webserver)
    {
        $act = 'act=extra_conf';

        $data['selectdomain'] = $domain;
        $data['selectweb'] = $webserver;
        $data['destination'] = $path;
        $data['create_record'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /*
     *	-------------------------------------------
     *	Delete extra configuration file path
     *	-------------------------------------------
     *
     *	@category	Configuration
     *	@param		String	$domain Domain for the extra path
     *	@param		String	$path Path of your extra conf
     *	@param		String	$webserver Webserver ID for whic you want to delete, you will get it from w_list array key
     *	@return		string $resp Response of Actions. Default: Serialize
     *	@version    2.2.6
     *
     */

    public function delete_extra_conf($domain, $path, $webserver)
    {
        $act = 'act=extra_conf';

        $data['domain_name'] = $domain;
        $data['editwebserver'] = $webserver;
        $data['editdestination'] = $path;
        $data['delete_record'] = 1;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }

    /*
     *	-------------------------------------------
     *	Edit extra configuration file path
     *	-------------------------------------------
     *
     *	@category	Configuration
     *	@param		String	$domain Domain for the extra path
     *	@param		String	$path Path of your extra conf
     *	@param		String	$webserver Webserver ID for whic you want to delete, you will get it from w_list array key
     *  @param      String  $id $id ID of the cron record. Get from the list of extra conf
     *	@return		string $resp Response of Actions. Default: Serialize
     *	@version    2.2.6
     *
     */

    public function edit_extra_conf($domain, $path, $webserver, $id)
    {
        $act = 'act=extra_conf';

        $data['domain_name'] = $domain;
        $data['editwebserver'] = $webserver;
        $data['editdestination'] = $path;
        $data['edit_record'] = 'c'.$id;

        $resp = $this->curl_call($act, $data);
        $this->chk_error();
        return $resp;
    }
}

//////////////////////////////////////////////////////////////////////
//							EXAMPLES								//
//////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
//							CATEGORY : FEATURES								 //
///////////////////////////////////////////////////////////////////////////////


////////////////////////////
//		List Domain       //
////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the list of domains
$res = unserialize($test->list_domains());

// Done/Error
if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing domain<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


///////////////////////////
//		Add Domain       //
///////////////////////////
/*$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);
$res = $test->add_domain($domain, $domainpath, $ip);
$res = unserialize($res);

$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Domain added';
}else{
    echo 'Error while adding domain<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


//////////////////////////////
//		Delete Domain       //
//////////////////////////////
/*$test = new Webuzo_API($webuzo_user, $webuzo_password, $host );

$res = $test->delete_domain($domain);
$res = unserialize($res);
$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Domain Deleted';
}else{
    echo 'Error while deleting Domain<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


////////////////////////////////
//		Change Password       //
////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->change_password($webuzo_password, $webuzo_user);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Password changed';
}else{
    echo 'Error while changing Password<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


///////////////////////////////////////
//		Change File Manager's        //
//			 Password				 //
///////////////////////////////////////
/*$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->change_fileman_pwd($pass);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Password changed for File Manager';
}else{
    echo 'Error while changing password for File Manager<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


////////////////////////////////////////////////
//		Change Apache Tomcat Manager's        //
//			 		Password				  //
////////////////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);
$res = $test->change_tomcat_pwd($pass);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Password changed for Apache Tomcat Manager';
}else{
    echo 'Error while changing Password for Apache Tomcat Manager<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


//////////////////////////////
//		List FTP User       //
//////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the list of FTP users
$res = unserialize($test->list_ftpuser());
//$test->r_print($res);

// Done/Error
if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing FTP User<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/

/////////////////////////////
//		Add FTP User       //
/////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->add_ftpuser($user, $pass, $directory, $quota_limit);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'FTP user added';
}else{
    echo 'Error while adding FTP user<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


//////////////////////////////
//		Edit FTP User       //
//////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the list of FTP users
// $res = $test->list_ftpuser();
$res = $test->edit_ftpuser($user, $quota_limit);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'FTP user\' quota edited';
}else{
    echo 'Error while editing FTP user\'s quota<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


////////////////////////////////
//		Delete FTP User       //
////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the list of FTP users
// $res = $test->list_ftpuser();

$res = $test->delete_ftpuser($ftp_user);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'FTP user Deleted';
}else{
    echo 'Error while deleting FTP user<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/

/*
/////////////////////////////////////
//		List FTP Connections       //
/////////////////////////////////////

$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the list of FTP users
$res = unserialize($test->list_ftp_connections());
//$test->r_print($res);

// Done/Error
if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing FTP Connections<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/

///////////////////////////////////////
//		Delete FTP Connections       //
///////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the list of FTP Connections and their Process IDs
// $res = $test->list_ftp_connections();

$res = $test->delete_ftp_connection($ftp_connection_id);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'FTP connection disconnected';
}else{
    echo 'Error while disconnecting FTP connection<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


///////////////////////////////////
//		Change FTP Users'        //
//			 Password			 //
///////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the list of FTP users
// $res = $test->list_ftpuser();
$res = $test->change_ftpuser_pass($ftpuser, $pass);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'FTP user\'s password changed';
}else{
    echo 'Error while changing FTP user\'s password<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


///////////////////////////////////////////////////////////////////////////////
//							CATEGORY : MYSQL								 //
///////////////////////////////////////////////////////////////////////////////

//////////////////////////////
//		List Database       //
//////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the LIST of Databases
$res = unserialize($test->list_database());

// Done/Error
if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing Databases<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


/////////////////////////////
//		Add Database       //
/////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->add_database($db_name);
$res = unserialize($res);
$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Database created';
}else{
    echo 'Error while creating database<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


////////////////////////////////
//		Delete Database       //
////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the LIST of Databases
// $res = $test->list_database();

$res = trim($test->delete_database($db_name));
$res = unserialize($res);
$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Database Deleted';
}else{
    echo 'Error while deleting Database<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


/////////////////////////////////////
//		 List Database Users       //
/////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the LIST of Database Users
$res = $test->list_db_user();
//$test->r_print($res);
$res = unserialize($res);

// Done/Error
if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing Database User<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


////////////////////////////////////
//		 Add Database Users       //
////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Specify Database username
// Note : Database username cannot exceed more than 16 characters inclusive of Webuzo username
// E.g soft_abcdefghijh is allowed

$res = $test->add_db_user($db_user, $pass);
$res = unserialize($res);
//$res = unserialize(trim($res));
$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Database user created';
}else{
    echo 'Error while creating database user<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


///////////////////////////////////////
//		 Delete Database Users       //
///////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the LIST of Database Users
// $res = $test->list_db_user();

// Specify Database user to DELETE in the following format:
// FORMAT : webuzo-username_databaseuser
// E.g : soft_test

$res = $test->delete_db_user($db_user);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Database user Deleted';
}else{
    echo 'Error while deleting Database user<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


/////////////////////////////////////////
//		 Set Database Privileges       //
/////////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Specify Database name in the following format:
// FORMAT : webuzo-username_databasename
// E.g : soft_test
// Specify Database user in the following format:
// FORMAT : webuzo-username_databaseuser
// E.g : soft_test


// Specify the Databas HOST
// Set $data['host'] = 'localhost'; for localhost
// Set $data['host'] = 'any host'; for Remote Host
// Set $data['host'] = 'example.com'; for your HOST(example.com)
// Set the privileges. Leave blank to restrict privileges
// 'SELECT,CREATE,INSERT,UPDATE,ALTER,DELETE,INDEX,CREATE_TEMPORARY_TABLES,EXECUTE,DROP,LOCK_TABLES,REFERENCES,CREATE_ROUTINE,CREATE_VIEW,SHOW_VIEW'


$res = $test->set_privileges($database, $db_user, $host, $prilist);
$res = unserialize($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Privileges set successfully';
}else{
    echo 'Error while setting privileges<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


//////////////////////////////////////////////////////////////////////////////
//					   CATEGORY : Advance Settings							//
//////////////////////////////////////////////////////////////////////////////


///////////////////////////////
//		Email Settings       //
///////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->edit_settings($email, $ins_email, $rem_email, $edit_email);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Email settings editted successfully';
}else{
    echo 'Error while editing Email settings<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


////////////////////////////////
//		Manage Services       //
////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Specify the services to be restarted
$service_name = 'mysqld';
$action = 'restart';

$res = $test->manage_service($service_name, $action);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Service '.$service_name.' '.$action.'ed successfully';
}else{
    echo 'Error while '.$action.'ing '.$service_name.' service<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/

/////////////////////////////////////
//		Enable/Disable suPHP       //
/////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Set on and off to enable and disable suPHP respectively
$res = $test->manage_suphp($status);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Settings saved successfully';
}else{
    echo 'Error while saving settings<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/

/*
///////////////////////////////////
//		Enable NGINX Proxy       //
///////////////////////////////////
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Set on and off to enable and disable suPHP respectively
$res = $test->enable_proxy($port, $htaccess, $proxy_server);
$res = unserialize($res);
$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Settings saved successfully';
}else{
    echo 'Error while saving settings<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}*/

/*
///////////////////////////////////
//		Disable NGINX Proxy       //
///////////////////////////////////
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Set on and off to enable and disable suPHP respectively
$res = $test->disable_proxy($set_default_webserver);
$res = unserialize($res);
$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Settings saved successfully';
}else{
    echo 'Error while saving settings<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/

//////////////////////////////////////////////////////////////////////////////
//					   CATEGORY : Server Settings							//
//////////////////////////////////////////////////////////////////////////////

///////////////////////////////
//		List DNS Record      //
///////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->list_dns_record($domain);
$res = unserialize($res);
if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing DNS Record<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


//////////////////////////////
//		Add DNS Record      //
//////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->add_dns_record($domain, $name, $ttl, $type, $address);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'DNS record added successfully';
}else{
    echo 'Error while adding DNS record<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


////////////////////////////////
//		Edit DNS Record       //
////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the $data['edit_record'] from LIST of DNS Records
//$res = $test->list_dns_record($domain.com);

$res = $test->edit_dns_record($id, $domain, $name, $ttl, $type, $address);
$res = unserialize($res);
$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'DNS Record record editted successfully';
}else{
    echo 'Error while editing DNS Record<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


/////////////////////////////////
//		Delete DNS Record      //
/////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the $data['delete_record'] from LIST of DNS Records
// $res = $test->list_dns_record($data);

$res = $test->delete_dns_record($id, $domain);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'DNS Record deleted';
}else{
    echo 'Error while deleting DNS Record<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


//////////////////////////
//		List CRON       //
//////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the LIST of CRONs Set
$res = $test->list_cron();
$res = unserialize($res);

if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing cron<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


////////////////////////
//		Add CRON      //
////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->add_cron($minute, $hour, $day, $month, $weekday, $cmd);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'CRON added successfully';
}else{
    echo 'Error while adding CRON<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


//////////////////////////
//		Edit CRON       //
//////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get $data['edit_record'] from the LIST of CRON
// $res = $test->list_cron();

$res = $test->edit_cron($id, $minute, $hour, $day, $month, $weekday, $cmd);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'CRON editted successfully';
}else{
    echo 'Error while editing CRON<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


////////////////////////////
//		Delete CRON       //
////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get $data['delete_record'] from the LIST of CRON
// $res = $test->list_cron();

$res = $test->delete_cron($id);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'CRON Deleted';
}else{
    echo 'Error while deleting CRON<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


//////////////////////////////////////////////////////////////////////////
//					   		CATEGORY : Security							//
//////////////////////////////////////////////////////////////////////////

//////////////////////////////
//		 List SSL Key       //
//////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the LIST of SSL keys available
$res = $test->list_ssl_key();
$res = unserialize($res);

if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing SSL key<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


////////////////////////////////
//		 Create SSL Key       //
////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->create_ssl_key($description, $keysize);

// $keysize
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'SSL key created';
}else{
    echo 'Error while creating ssl key<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


////////////////////////////////
//		 Upload SSL Key       //
////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->upload_ssl_key($description, $keypaste);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'SSL key uploaded';
}else{
    echo 'Error while uploading SSL key<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


////////////////////////////////
//		 Detail SSL Key       //
////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->detail_ssl_key($domain);
$res = unserialize($res);

if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while showing details for SSL key<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


////////////////////////////////
//		 Delete SSL Key       //
////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the LIST of SSL KEY
$res = $test->list_ssl_key();

// Pass the key of the KEY to delete
$res = $test->delete_ssl_key($domain_key);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'SSL key deleted';
}else{
    echo 'Error while deleting SSL key<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


///////////////////////////////
//		 List SSL CSR        //
///////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the LIST of SSL CSR
$res = $test->list_ssl_csr();
$res = unserialize($res);

if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing SSL CSR<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


/////////////////////////////////
//		 Create SSL CSR        //
/////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->create_ssl_csr($domain, $country_code, $state, $locality, $org, $org_unit, $passphrase, $email, $key);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'SSL CSR created';
}else{
    echo 'Error while creating SSL CSR<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


/////////////////////////////////
//		 Detail SSL CSR        //
/////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->detail_ssl_csr($domain);
$res = unserialize($res);

if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while showing details for SSL CSR<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


/////////////////////////////////
//		 Delete SSL CSR        //
/////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the LIST of SSL CSR
$res = $test->list_ssl_csr();

// Pass the key of the CSR to delete
$res = $test->delete_ssl_csr($domain_key);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'SSL CSR deleted';
}else{
    echo 'Error while deleting SSL CSR<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


//////////////////////////////////////
//		List SSL Certificate		//
//////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the LIST of SSL Certificates
$res = $test->list_ssl_crt();
$res = unserialize($res);

if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing SSL Certificate<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


//////////////////////////////////////////
//		 Create SSL Certificate			//
//////////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->create_ssl_crt($domain, $country_code, $state, $locality, $org, $org_unit, $email, $key);

$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'SSL Certificate created';
}else{
    echo 'Error while creating SSL Certificate<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


//////////////////////////////////////
//	    Upload SSL Certificate		//
//////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->upload_ssl_crt($keypaste);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'SSL certificate uploaded';
}else{
    echo 'Error while uploading SSL certificate<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


//////////////////////////////////////////
//		 Detail SSL Certificate			//
//////////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->detail_ssl_crt($domain);
$res = unserialize($res);

if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while showing details for SSL Certificate<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


/////////////////////////////////////////
//		 Delete SSL Certificate        //
/////////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the LIST of SSL Certificates
$res = $test->list_ssl_crt();

// Pass the key of the Certificate to delete
$res = $test->delete_ssl_crt($domain_key);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'SSL Certificate deleted';
}else{
    echo 'Error while deleting SSL Certificate<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


////////////////////////////////
//		List Blocked IP       //
////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the LIST of Blocked IPs
$res = $test->list_ipblock();
$res = unserialize($res);

if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing Blocked IPs<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


/////////////////////////
//		Block IP       //
/////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->add_ipblock($ip);
$res = unserialize($res);

// Done/Error
if(!empty($res['done'])){
    echo 'IP Blocked';
}else{
    echo 'Error while Blocking IP<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


///////////////////////////
//		Unblock IP       //
///////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->delete_ipblock($ip);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'IP Unblocked successfully';
}else{
    echo 'Error while Unblocking IP<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


///////////////////////////////////
//		Enable/Disable SSH       //
///////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->ssh_access($action);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Settings saved successfully';
}else{
    echo 'Error while saving settings<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


//////////////////////////////////////////////////////////////////////////////
//					   		CATEGORY : Email Server							//
//////////////////////////////////////////////////////////////////////////////

////////////////////////////////
//		List Email User       //
////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the LIST of email users
$res = $test->list_emailuser($domain);
$res = unserialize($res);

if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing Email User<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


///////////////////////////////
//		Add Email User       //
///////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->add_emailuser($domain, $emailuser, $password);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Email user added';
}else{
    echo 'Error while adding Email user<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


/////////////////////////////////////
//		Change Email Users'        //
//			 Password			   //
/////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the $data['edit_record'] from LIST of Email users
// $res = $test->list_emailuser($domain);
$res = $test->change_email_user_pass($domain, $emailuser, $password);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Email user\'s password changed';
}else{
    echo 'Error while changing Email user\'s password<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


//////////////////////////////////
//		Delete Email User       //
//////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the $data['delete_record'] from LIST of Email users
//$res = $test->list_emailuser($domain);

$res = $test->delete_email_user($domain, $emailuser);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Email user Deleted';
}else{
    echo 'Error while deleting Email user<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


//////////////////////////////////////
//		List Email Forwarders       //
//////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the LIST of Email Forwarders
$res = $test->list_emailforward($domain);
$res = unserialize($res);

if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing Email Forwarders<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


////////////////////////////////////
//		Add Email Forwarder       //
////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->add_emailforward($domain, $forward_address, $forward_to);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Email Forwarder added';
}else{
    echo 'Error while adding Email Forwarder<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


///////////////////////////////////////
//		Delete Email Forwarder       //
///////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the $data['forward_name'] from LIST of Email Forwarders
// $res = $test->list_emailforward($domain);
$res = $test->delete_email_forward($domain, $forward_address, $forward_to);
$res = unserialize($res);
$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Email Forwarder Deleted';
}else{
    echo 'Error while deleting Email Forwarder<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


///////////////////////////////
//		List MX Record       //
///////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the LIST of MXRecords
$res = $test->list_mx_entry($domain);
$res = unserialize($res);

if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing MX Records<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


//////////////////////////////
//		Add MX Record       //
//////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->add_mx_entry($domain, $priority, $destination);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'MX record added successfully';
}else{
    echo 'Error while adding MX record<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


/////////////////////////////
//		Edit MX Entry      //
/////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get $data['edit_record'] from the LIST of MX Records
// $res = $test->list_mx_entry();

$res = $test->edit_mx_entry($domain, $record, $priority, $destination);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'MX Entry record editted successfully';
}else{
    echo 'Error while editing MX Entry<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


///////////////////////////////
//		Delete MX Entry      //
///////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get $data['delete_record'] from the LIST of MX Records
// $res = $test->list_mx_entry();

$res = $test->delete_mx_entry($domain, $record);
$res = unserialize($res);
// $test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'MX Record deleted';
}else{
    echo 'Error while deleting MX Record<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


////////////////////////////
//		Set Defaults      //
////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->set_defaults('php53');
$res = unserialize($res);
//$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Default Set';
}else{
    echo 'Error while setting defaults<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


//////////////////////////////////////////////////////////////////////////
//					   CATEGORY : Server Info							//
//////////////////////////////////////////////////////////////////////////

///////////////////////////////
//		Show Error Log       //
///////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->show_error_log();
$res = unserialize($res);

if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while showing Error Log<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/

///////////////////////////////
//		Configure Webuzo     //
///////////////////////////////
/*
$test = new Webuzo_API();

$res = $test->webuzo_configure($ip, $user, $email, $pass, $host, $ns1 = '', $ns2 ='', $license = '' );
$res = unserialize($res);
$test->r_print($res);
*/

///////////////////////////////////////////
//		Install System Application       //
///////////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$appid = 30; // ID of the Application to install.

$res = $test->install_app($appid);
//$res = unserialize($res);

$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Done';
}else{
    echo 'Failed<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/

///////////////////////////////////////////
//		Remove System Application       //
///////////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$appid = 30; // ID of the Application to install.

$res = $test->remove_app($appid);
//$res = unserialize($res);

$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Done';
}else{
    echo 'Failed<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/

//////////////////////////////////
//		List Services			//
//////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->list_services();
$res = unserialize($res);

$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Done';
}else{
    echo 'Failed<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/

///////////////////////////////////////
//	Enable / Disable PHP Extensions  //
///////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$extensions = 'curl.so,calendar.so'; // Extensions to Enable.

$res = $test->handle_php_ext($extensions);
$res = unserialize($res);
//$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Done';
}else{
    echo 'Failed<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/

/////////////////////
//	Network Tools  //
/////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->dns_lookup($domain_name);
$res = unserialize($res);
$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Done';
}else{
    echo 'Failed<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/

///////////////////////////
//	Set BAndwidth Limit  //
//////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->set_bandwidth($total_bandwidth, $bandwiwdth_email_alert);
$res = unserialize($res);
$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Done';
}else{
    echo 'Failed<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/
/////////////////////////////
//	Reset BAndwidth Limit  //
////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->reset_bandwidth();
$res = unserialize($res);
$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Done';
}else{
    echo 'Failed<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}

*/

/////////////////////////////
//	  List Login Logs     //
////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->list_login_logs();
$res = unserialize($res);
$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Done';
}else{
    echo 'Failed<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/

/////////////////////////////
//	  Delete Login Logs    //
////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

$res = $test->delete_login_logs();
$res = unserialize($res);
$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'Done';
}else{
    echo 'Failed<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/

//////////////////////////////////////
// List Password Protected Dir List //
//////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);

// Get the list of domains
$res = unserialize($test->list_protected_users());

// Done/Error
if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing password protected directories<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/


////////////////////////////////////////////
//		Add Password Protected User       //
////////////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);
$res = $test->add_pass_protect_dir('public_html/test', 'testuser', 'testuser', 'Test Account');

$res = unserialize($res);

$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'User added successfully';
}else{
    echo 'Error while adding user<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/

///////////////////////////////////////////////
//		Delete Password Protected User       //
///////////////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);
$res = $test->delete_pass_protected_user('testuser', '/home/soft/public_html/test');

$res = unserialize($res);

$test->r_print($res);

// Done/Error
if(!empty($res['done'])){
    echo 'User deleted successfully';
}else{
    echo 'Error while deleting user<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/

///////////////////////////////////////////////
//		Read Extra conf file path            //
///////////////////////////////////////////////
/*


$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);
$res = $test->read_extra_conf($host);

$res = unserialize($res);

$test->r_print($res);

// Done/Error
if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing Conf file<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/
///////////////////////////////////////////////
//		 Add Extra conf file path            //
///////////////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);;

//GEt the webserver from w_list key of your webser 3 for apache 18 for nginx 60 for lighttpd
// You will get the w_list from  read_extra_conf(Domain name)
$res = $test->add_extra_conf($domain, $path, $webserver);

$res = unserialize($res);


// Done/Error
if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing Conf file<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/
///////////////////////////////////////////////
//		 Delete Extra conf file path         //
///////////////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);;

//GEt the webserver from w_list key of your webser 3 for apache 18 for nginx 60 for lighttpd
// You will get the w_list from  read_extra_conf(Domain name)
$res = $test->delete_extra_conf($domain, $path, $webserver);

$res = unserialize($res);

// Done/Error
if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing Conf file<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}
*/
///////////////////////////////////////////////
//		 	dit Extra conf file path         //
///////////////////////////////////////////////
/*
$test = new Webuzo_API($webuzo_user, $webuzo_password, $host);;


// Get the $webserver from w_list key of your webser 3 for apache 18 for nginx 60 for lighttpd
// You will get the w_list from  read_extra_conf(Domain name)
// Get the $id from the list of extra conf
$res = $test->edit_extra_conf($domain, $path, $webserver, $id);

$res = unserialize($res);

// Done/Error
if(empty($res['error'])){
    $test->r_print($res);
}else{
    echo 'Error while listing Conf file<br/>';
    if(!empty($res['error'])){
        print_r($res['error']);
    }
}*/


// **************************************************************************************
// 											END OF FILE
// **************************************************************************************
