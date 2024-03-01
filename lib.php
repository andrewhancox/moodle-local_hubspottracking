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
 * @copyright 2024, Andrew Hancox
 */

function local_hubspottracking_before_standard_html_head() {
    global $PAGE, $SESSION, $USER;

    $trackingcodeurl = get_config('local_hubspottracking', 'trackingcodeurl');

    if (empty($trackingcodeurl)) {
        return '';
    }

    $data = [];
    $USER->lastname = 'changed';
    if (isloggedin() && !isguestuser()) {
        $identitydata = [];
        $identitydata['identify'] = true;
        $identitydata['email'] = $USER->email;
        $identitydata['firstname'] = $USER->firstname;
        $identitydata['lastname'] = $USER->lastname;
        $identitydata['city'] = $USER->city;
        $identitydata['company'] = $USER->institution;
        $identitydata['country'] = $USER->country;
        $identitydata['jobtitle'] = $USER->department;
        $identitydata['phone'] = $USER->phone1;
        $identitydata['mobilephone'] = $USER->phone2;
        $identitydata['address'] = $USER->address;

        global $CFG;
        require_once("$CFG->dirroot/user/profile/lib.php");
        profile_load_data($USER);

        if (!empty($USER->profile['organisation'])) {
            $identitydata['company'] = $USER->profile['organisation'];
        }
        if (!empty($USER->profile['school'])) {
            $identitydata['company'] = $USER->profile['school'];
        }
        if (!empty($USER->profile['jobtitle'])) {
            $identitydata['jobtitle'] = $USER->profile['orgrole'];
        }
        if (!empty($USER->profile['phone'])) {
            $identitydata['phone'] = $USER->profile['phone'];
        }

        $identitydatachecksum = md5(json_encode($identitydata));

        if (
            empty($SESSION->local_hubspottracking_identified)
            ||
            $SESSION->local_hubspottracking_identified !== $identitydatachecksum
        ) {
            $data = $identitydata;
            $SESSION->local_hubspottracking_identified = $identitydatachecksum;
        }
    }

    $data['trackingcodeurl'] = $trackingcodeurl;
    $data['pageurl'] = $PAGE->url->out_as_local_url();

    return $PAGE->get_renderer('core')->render_from_template('local_hubspottracking/trackingcode', $data);
}
