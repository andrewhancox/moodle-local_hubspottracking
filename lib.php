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
use local_hubspottracking\local\identitytracker;

/**
 * @package local_hubspottracking
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @author Open Source Learning <enquiries@opensourcelearning.co.uk>
 * @link https://opensourcelearning.co.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2024, Andrew Hancox
 */

function local_hubspottracking_post_signup_requests($data) {
    $trackingdata = new stdClass();
    $trackingdata->profile = [];

    foreach ((array)$data as $key => $value) {
        $customprofileprefix = 'profile_field_';

        if (core_text::strpos($key, $customprofileprefix) === 0) {
            $key = core_text::substr($key, core_text::strlen($customprofileprefix));
            $trackingdata->profile[$key] = $value;
        } else {
            $trackingdata->$key = $value;
        }
    }

    identitytracker::setidentitydata($trackingdata);
}

function local_hubspottracking_before_standard_html_head() {
    global $PAGE, $SESSION;

    $trackingcodeurl = get_config('local_hubspottracking', 'trackingcodeurl');

    if (empty($trackingcodeurl)) {
        return '';
    }

    $data = [];

    $identitydata = identitytracker::getidentitydata();
    if ($identitydata) {
        $identitydata['identify'] = true;
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
