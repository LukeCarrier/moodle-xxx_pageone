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

namespace block_pageone;

use local_pageone\util as local_util;
use moodle_url;

class util extends local_util {
    /**
     * Moodle frankenstyle component name.
     *
     * @var string
     */
    const MOODLE_MODULE = 'block_pageone';

    /**
     * Get compose URL.
     *
     * @param integer $instanceid The ID of the block instance.
     *
     * @return \moodle_url The compose URL.
     */
    public static function compose_url($instanceid) {
        return new moodle_url('/blocks/pageone/compose.php', array(
            'instanceid' => $instanceid,
        ));
    }

    /**
     * Get inbox URL.
     *
     * @param integer $instanceid The ID of the block instance.
     *
     * @return \moodle_url The inbox URL.
     */
    public static function inbox_url($instanceid) {
        return static::log_url($instanceid, 'in');
    }

    /**
     * Get log URL.
     *
     * @param integer $instanceid The ID of the block instance.
     * @param string  $direction  The direction of the messages; in for inbox,
     *                            out for outbox.
     *
     * @return \moodle_url The log URL.
     */
    public static function log_url($instanceid, $direction) {
        return new moodle_url('/blocks/pageone/log.php', array(
            'instanceid' => $instanceid,
            'direction'  => $direction,
        ));
    }

    /**
     * Get outbox URL.
     *
     * @param integer $instanceid The ID of the block instance.
     *
     * @return \moodle_url The outbox URL.
     */
    public static function outbox_url($instanceid) {
        return static::log_url($instanceid, 'out');
    }
}
