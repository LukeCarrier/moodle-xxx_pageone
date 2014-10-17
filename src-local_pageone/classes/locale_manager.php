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

/**
 * Locale manager.
 */
class locale_manager {
    /**
     * Available locales.
     *
     * @var string[]
     */
    protected static $locales = array(
        'united_kingdom',
    );

    /**
     * Get a locale.
     *
     * @param string $name The internal name of the locale to retrieve.
     *
     * @return string The locale's class name.
     */
    public static function get($name) {
        return '\local_pageone\locales\\' . $name;
    }

    /**
     * Return an array for use within a form.
     *
     * @return string[] The menu options.
     */
    public static function menu() {
        $locales = array();
        foreach (static::$locales as $localetype) {
            $localeclass = static::get($localetype);
            $locales[$localetype] = $localeclass::get_name();
        }

        return $locales;
    }
}
