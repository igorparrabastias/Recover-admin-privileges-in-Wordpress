<?php

/*
 * wp-get-admin.php - A PHP script that create a user with admin privileges in Wordpress
 *
 * Requirements for use:
 *
 *  - Wordpress/MU 2.0+
 *  - ftp or shell access to WP root foolder
 *
 * USAGE
 *
 *  upload script to WP root foolder
 *  go to http://wp-website/wp-get-admin.php
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
        $ip              = '';      // required
        $user_login      = '';      // required
        $user_pass       = '';      // required
        $user_nicename   = '';      // required
        $user_email      = '';      // required
        $user_url        = '';
        $display_name    = '';
        $mu              = 0;
        # -------------------------------------
        # -------------------------------------

        # -------------------------------------
        # let it in my circuits from now on
        # -------------------------------------

        $user_pass       = md5($user_pass);
        $user_registered = current_time('mysql');
        $mu              = $mu ? '_1' : '';

        global $wpdb;
        global $userdata;

        if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1')
        if ($this->getIp() != $ip)
        {
            die('access only allowed to the king. die!');
        }

        $sql = "SELECT * FROM {$wpdb->base_prefix}users
        WHERE
        user_login = '$user_login'
        OR
        user_email = '$user_email'";
        $record = $wpdb->get_row($sql);

        if ($record)
        {
            $this->d('some data exists. change it please:');
            
            $this->d($record);

            $sql = "SELECT * FROM {$wpdb->base_prefix}usermeta
            WHERE
            user_id = '{$record->ID}'";
            $record = $wpdb->get_results($sql);

            $this->d($record, 1);
        }

        $wpdb->insert("{$wpdb->base_prefix}users",
        array(
        'user_login'         => $user_login,
        'user_pass'          => $user_pass,
        'user_nicename'      => $user_nicename,
        'user_email'         => $user_email,
        'user_url'           => $user_url,
        'user_registered'    => $user_registered,
        'display_name'       => $display_name
        ),
        array('%s', '%s', '%s', '%s', '%s', '%s', '%s'));

        $user_id = $wpdb->insert_id;

        $wpdb->insert("{$wpdb->base_prefix}usermeta",
        array(
        'user_id'            => $user_id,
        'meta_key'           => "wp{$mu}_capabilities",
        'meta_value'         => 'a:1:{s:13:"administrator";b:1;}'
        ),
        array('%d', '%s', '%s'));

        $wpdb->insert("{$wpdb->base_prefix}usermeta",
        array(
        'user_id'            => $user_id,
        'meta_key'           => "wp{$mu}_user_level",
        'meta_value'         => '10'
        ),
        array('%d', '%s', '%s'));

        $this->d('success! new user ID: ' . $user_id, 1);
    }

    function getIp()
    {
      $hostip = @gethostbyname($_SERVER['REMOTE_ADDR']);
      return long2ip(ip2long($hostip));
    }

    function d($var, $exit = 0)
    {
        if (is_string($var))
        $var = "<b>$var</b>";
        
        echo '<pre>' . var_export($var, 1) . '</pre>';
        if ($exit)
            exit;
    }
}

$nomikosClass =& new nomikos_getAdminPrivileges();

?>
