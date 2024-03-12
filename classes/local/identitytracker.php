<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package local_hubspottracking
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @author Open Source Learning <enquiries@opensourcelearning.co.uk>
 * @link https://opensourcelearning.co.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2021, Andrew Hancox
 */

namespace local_hubspottracking\local;

class identitytracker {
    private static $identitydata;

    public static function setidentitydata(\stdClass $trackingdata) {
        self::$identitydata = $trackingdata;
    }

    public static function getidentitydata() {
        global $USER, $CFG;
        require_once("$CFG->dirroot/user/profile/lib.php");

        if (isset(self::$identitydata)) {
            $user = self::$identitydata;
        } else if (isloggedin() && !isguestuser()) {
            $user = $USER;
            profile_load_data($user);
        } else {
            return false;
        }

        $identitydata = [];

        foreach ([
                     'email' => 'email',
                     'firstname' => 'firstname',
                     'lastname' => 'lastname',
                     'city' => 'city',
                     'institution' => 'company',
                     'country' => 'country',
                     'department' => 'jobtitle',
                     'phone1' => 'phone',
                     'phone2' => 'mobilephone',
                     'address' => 'address',
                 ] as $local => $remote) {
            if (!empty($user->$local)) {
                $identitydata[$remote] = $user->$local;
            }
        }

        foreach ([
                     'organisation' => 'company',
                     'school' => 'company',
                     'orgrole' => 'jobtitle',
                     'phone' => 'phone',
                 ] as $local => $remote) {
            if (!empty($user->profile[$local])) {
                $identitydata[$remote] = $user->profile[$local];
            }
        }

        return $identitydata;
    }
}