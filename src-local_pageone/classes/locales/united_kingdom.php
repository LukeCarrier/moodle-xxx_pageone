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

namespace local_pageone\locales;

use local_pageone\locale_interface;
use local_pageone\util;

/**
 * United Kingdom locale.
 *
 */
class united_kingdom implements locale_interface {
    /**
     * @override \local_pageone\locale_interface
     */
    public static function get_name() {
        return util::string('locale:unitedkingdom');
    }

    /**
     * @override \local_pageone\locale_interface
     */
    public static function get_type() {
        return 'united_kingdom';
    }
}
