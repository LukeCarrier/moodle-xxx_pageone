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

namespace block_pageone\exceptions;

use moodle_exception;

/**
 * Invalidd log direction.
 *
 * Thrown when the supplied direction is neither in nor out.
 */
class invalid_log_direction_exception extends moodle_exception {
    /**
     * @override \moodle_exception
     *
     * @param string $direction The supplied direction.
     */
    public function __construct($direction) {
        parent::__construct('invalidlogdirection', 'block_pageone', null,
                            $direction);
    }
}
