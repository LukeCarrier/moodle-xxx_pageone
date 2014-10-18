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
 * Compose form.
 */
class compose_form extends moodleform {
    /**
     * @override \moodleform
     */
    public function definition() {
        $mform = $this->_form;

        $select = $mform->addElement('select', 'recipients',
                                     util::string('recipients'),
                                     $this->_customdata['enrolled_users']);
        $select->setMultiple(true);

        $mform->addElement('checkbox', 'sendemail', util::string('sendemail'));

        $mform->addElement('checkbox', 'sendsms', util::string('sendsms'));

        $mform->addElement('checkbox', 'includesendernameinsms',
                           util::string('includesendernameinsms'));

        $mform->addElement('text', 'subject', util::string('subject'));

        $mform->addElement('editor', 'message', util::string('message'));

        $mform->addElement('static', 'smscharacterlimit',
                           util::string('smscharacterlimit'));

        $mform->addElement('select', 'emailformat', util::string('emailformat'), array(
            'html' => util::string('emailformat:html'),
            'text' => util::string('emailformat:text'),
        ));

        $mform->addElement('filemanager', 'emailattachments',
                          util::string('emailattachments'), null,
                          util::attachments_options($this->_customdata['course']));

        $this->add_action_buttons(true, util::string('send'));
    }
}
