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

namespace local_pageone;

use lang_string;

/**
 * Utility methods for lazy developers.
 */
class util {
    /**
     * Moodle frankenstyle component name.
     *
     * @var string
     */
    const MOODLE_MODULE = 'local_pageone';

    /**
     * Get a lazy-loading language string.
     *
     * @param string  $string The key of the string to retrieve.
     * @param mixed   $a      Substitions for the string (optional).
     * @param string  $module If sourcing strings from another module, the name
     *                        of the module (optional).
     * @param boolean $now    Should we source the string immediately?
     *
     * @return \lang_string The language string.
     */
    public static function string($string, $a=null, $module=null, $now=false) {
        if ($module === null) {
            $module = static::MOODLE_MODULE;
        }

        return $now ? get_string($string, $module, $a)
                : new lang_string($string, $module, $a);
    }

    /**
     * Get a language string.
     *
     * @param string $string The key of the string to retrieve.
     * @param mixed  $a      Substitions for the string (optional).
     * @param string $module If sourcing strings from another module, the name
     *                       of the module (optional).
     *
     * @return string The language string.
     */
    public static function string_now($string, $a=null, $module=null) {
        return static::string($string, $a, $module, true);
    }
}
