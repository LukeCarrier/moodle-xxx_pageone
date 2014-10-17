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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * PageOne MoodleMobile block.
 *
 * @author Luke Carrier <luke@tdm.co>
 * @copyright 2014 Luke Carrier, The Development Manager Ltd
 * @license GPL v3
 */

use local_pageone\locale_manager;
use local_pageone\util;

require_once __DIR__ . '/lib.php';

if ($hassiteconfig) {
    $page = new admin_settingpage('localplugins_pageone',
                                  util::string('pageone'));
    $page->add(new admin_setting_configtext('local_pageone/accountnumber',
            util::string('config:accountnumber'),
            util::string('config:accountnumberdesc'), ''));
    $page->add(new admin_setting_configpasswordunmask('local_pageone/accountpassword',
            util::string('config:accountpassword'),
            util::string('config:accountpassworddesc'), ''));

    $page->add(new admin_setting_configselect('local_pageone/locale',
            util::string('config:locale'),
            util::string('config:localedesc'), null, locale_manager::menu()));

    $ADMIN->add('localplugins', $page);
}
