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

use block_pageone\forms\compose_form;
use block_pageone\util;

require_once dirname(dirname(__DIR__)) . '/config.php';
require_once __DIR__ . '/lib.php';

$instanceid = required_param('instanceid', PARAM_INT);

$context  = context_block::instance($instanceid);
$course   = $DB->get_record('course', array(
    'id' => $context->get_parent_context()->instanceid,
));
$heading  = util::string('compose');

$PAGE->set_context($context);
$PAGE->set_heading($heading);
$PAGE->set_title($heading);
$PAGE->set_url(util::compose_url($instanceid));

$renderer = $PAGE->get_renderer('block_pageone');

$composeform = new compose_form(null, array(
    'course'         => $course,
    'enrolled_users' => util::enrolled_users($context),
));

echo $OUTPUT->header(),
     $OUTPUT->heading($heading),
     $renderer->navigation_tabs($instanceid, block_pageone_renderer::TAB_COMPOSE);
$composeform->display();
echo $OUTPUT->footer();
