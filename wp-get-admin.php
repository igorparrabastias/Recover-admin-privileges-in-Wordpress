<?php

/*
 * wp-get-admin.php - A PHP script --not a plugin-- that creates a user with admin privileges in Wordpress
 *
 * Requirements for use:
 *
 *  - Wordpress/MU 2.0+
 *  - ftp or shell access to WP root folder
 *  - PHP 4.2+
 *
 * USAGE
 *
 *  fill config user data
 *  in $config->ip put your current IP from you will run the script (hint: ifconfig or go to http://www.ip-adress.com/)
 *  in $config->is_wp_mu put if the target is WPMU (multi user) or not
 *  upload script to WP root folder
 *  go to http://your-site/wp-get-admin.php
 *
 * KNOWN ISSUES
 *
 *  - This is a rough proof-of-concept hack, not intended for production use.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 * @version         : 0.1
 * @author          : NomikOS
 * #author URI      : http://nomikos.info
 * #author RAC URI  : http://www.rentacoder.com/RentACoder/DotNet/SoftwareCoders/ShowBioInfo.aspx?lngAuthorId=7064234
 *
 * @copyright 2010 Igor Parra B. <root@nomikos.info>
 * @license http://www.fsf.org/licensing/licenses/gpl.txt GNU Public License
 *
 */

require_once('./wp-blog-header.php');

if ( ! defined('DIEONDBERROR'))
    define('DIEONDBERROR', true);

class nomikos_getAdminPrivileges
{
    function nomikos_getAdminPrivileges()
    {
        # -------------------------------------
        # config user
        # -------------------------------------
        $config->use_ssl         = true;
        $config->is_wp_mu        = false;

        $config->ip              = '';      // required
        $config->user_login      = '';      // required
        $config->user_pass       = '';      // required
        $config->user_nicename   = '';      // required
        $config->user_email      = '';      // required

        $config->user_url        = '';
        $config->display_name    = '';
        # -------------------------------------
        # -------------------------------------

        # -------------------------------------
        # let it in my circuits from now on
        # -------------------------------------

        if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1')
        if ($this->getIp() != $config->ip)
        {
            die('access only allowed to the king. die!');
        }

        $this->d('wp-get-admin.php - A PHP script --not a plugin-- that creates a user with admin privileges in Wordpress');

        if ($config->use_ssl)
        if ($_SERVER['HTTPS'] != 'on')
        {
            $msg = "\$config->use_ssl is true.\n";
            $msg .= "please use ";
            $msg .= "https://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}\n";
            $msg .= "if available or turn \$config->use_ssl to false";

            $this->d($msg);
            $this->d($config, 1);

            exit;
        }

        if ( ! $config->ip
        || ! $config->user_login
        || ! $config->user_pass
        || ! $config->user_nicename
        || ! $config->user_email)
        {
            $this->d('some congfig data is missing. fill it please:');
            $this->d($config, 1);

            exit;
        }

        $is_wp_mu                = $config->is_wp_mu ? '_1' : '';
        $prefix                  = $config->is_wp_mu ? 'base_prefix' : 'prefix';

        global $wpdb;
        global $userdata;

        $sql = "SELECT * FROM {$wpdb->$prefix}users
        WHERE
        user_login = '$config->user_login'
        OR
        user_email = '$config->user_email'";
        $record = $wpdb->get_row($sql);

        if ($record)
        {
            $this->d('some data exists. change it please:');
            $this->d($record);

            $sql = "SELECT meta_key, meta_value FROM {$wpdb->$prefix}usermeta
            WHERE
            user_id = '{$record->ID}'";
            $record = $wpdb->get_results($sql);
            $this->d($record, 1);

            exit;
        }

        $wpdb->insert("{$wpdb->$prefix}users",
        array(
        'user_login'         => $config->user_login,
        'user_pass'          => md5($config->user_pass),
        'user_nicename'      => $config->user_nicename,
        'user_email'         => $config->user_email,
        'user_url'           => $config->user_url,
        'user_registered'    => current_time('mysql'),
        'display_name'       => $config->display_name
        ),
        array('%s', '%s', '%s', '%s', '%s', '%s', '%s'));

        $user_id = $wpdb->insert_id;

        $wpdb->insert("{$wpdb->$prefix}usermeta",
        array(
        'user_id'            => $user_id,
        'meta_key'           => "wp{$is_wp_mu}_capabilities",
        'meta_value'         => 'a:1:{s:13:"administrator";b:1;}'
        ),
        array('%d', '%s', '%s'));

        $wpdb->insert("{$wpdb->$prefix}usermeta",
        array(
        'user_id'            => $user_id,
        'meta_key'           => "wp{$is_wp_mu}_user_level",
        'meta_value'         => '10'
        ),
        array('%d', '%s', '%s'));

        $msg = "SUCCESS!\n";
        $msg .= "new user ID: $user_id\n";
        $msg .= "login: $config->user_login\n";
        $msg .= "e-mail: $config->user_email\n";
        $msg .= "paswword: $config->user_pass\n";

        $this->d($msg, 1);
    }

    function getIp()
    {
      $hostip = @gethostbyname($_SERVER['REMOTE_ADDR']);
      return long2ip(ip2long($hostip));
    }

    function d($var, $exit = 0)
    {
        $msg = '';

        if (is_string($var))
        {
            echo '<pre><b>' . $var . '</b></pre>';
        }
        else
        {
            $msg = "dump data:\n";
            echo '<pre>' . $msg . var_export($var, 1) . '</pre>';
        }

        if ($exit)
            exit;
    }
}

$nomikosClass =& new nomikos_getAdminPrivileges();

?>
