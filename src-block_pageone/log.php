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

use block_pageone\exceptions\invalid_log_direction_exception;
use block_pageone\forms\log_filter_form;
use block_pageone\util;

require_once dirname(dirname(__DIR__)) . '/config.php';
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/renderer.php';

$instanceid = required_param('instanceid', PARAM_INT);
$direction  = required_param('direction',  PARAM_ALPHA);

switch ($direction) {
    case 'in':
        $navigationtab = block_pageone_renderer::TAB_INBOX;
        break;

    case 'out':
        $navigationtab = block_pageone_renderer::TAB_OUTBOX;
        break;

    default:
        throw new invalid_log_direction_exception($direction);
}

$context = context_block::instance($instanceid);
$heading = util::string($navigationtab);

$PAGE->set_context($context);
$PAGE->set_heading($heading);
$PAGE->set_title($heading);
$PAGE->set_url(util::log_url($instanceid, $direction));

$renderer = $PAGE->get_renderer('block_pageone');

$filterform = new log_filter_form();

echo $OUTPUT->header(),
     $OUTPUT->heading($heading),
     $renderer->navigation_tabs($instanceid, $navigationtab);
$filterform->display();
echo $OUTPUT->footer();
