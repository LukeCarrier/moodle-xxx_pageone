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
 * PageOne MoodleMobile libraries.
 *
 * The core of the PageOne MoodleMoble functionality; used by the block and
 * message output plugins.
 *
 * @author Luke Carrier <luke@tdm.co>
 * @copyright 2014 Luke Carrier, The Development Manager Ltd
 * @license GPL v3
 */

$plugin->component = 'local_pageone';

// Version format:  YYYYMMDDXX
$plugin->version  = 2014101600;
$plugin->requires = 2012120300; // Moodle 2.4

$plugin->release  = '0.1.0';
$plugin->maturity = MATURITY_ALPHA;
