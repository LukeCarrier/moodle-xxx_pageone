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

namespace block_pageone\forms;

use block_pageone\util;
use moodleform;

require_once "{$CFG->libdir}/formslib.php";

/**
 * Filter form.
 *
 * From which sources should we obtain messaging data?
 */
class log_filter_form extends moodleform {
    /**
     * @override moodleform
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('select', 'user', util::string('user'), array(
            'current' => util::string('user:current'),
            'all'     => util::string('user:all'),
        ));

        $mform->addElement('select', 'course', util::string('course'), array(
            'current' => util::string('course:current'),
            'all'     => util::string('course:all'),
        ));

        $this->add_action_buttons(false, util::string('applyfilters'));
    }
}
