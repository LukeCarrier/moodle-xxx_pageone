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

use block_pageone\util;

require_once dirname(__DIR__) . '/moodleblock.class.php';
require_once __DIR__ . '/lib.php';

/**
 * PageOne block class.
 */
class block_pageone extends block_list {
    /**
     * @override \block_base
     */
    function init() {
        $this->title = get_string('pluginname', 'block_pageone');
    }

    /**
     * @override \block_base
     */
    function applicable_formats() {
        return array('all' => true);
    }
    
    /**
     * Get the (possibly cached) block content.
     *
     * @return stdClass An object with an array of items, an array of icons, and
     *                  a string for the footer.
     */
    function get_content() {
        global $CFG, $USER;

        if ($this->content === null) {
            $this->content = (object) array(
                'footer' => '',
                'icons'  => array(),
                'items'  => array(
                    html_writer::link(util::compose_url($this->context->instanceid),
                                      util::string('compose')),
                    html_writer::link(util::outbox_url($this->context->instanceid),
                                      util::string('outbox')),
                    html_writer::link(util::inbox_url($this->context->instanceid),
                                      util::string('inbox')),
                ),
            );
        }

        return $this->content;
    }

    /**
     * @override \block_base
     */
    function instance_allow_multiple() {
        return false;
    }
}
