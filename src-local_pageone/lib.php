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

use local_pageone\util;

/**
 * Autoload a class.
 *
 * This is a hack necessary to support Moodle <2.6, which doesn't introduce
 * support for autoloading classes under plugin namespaces.
 *
 * @param string $classname The fully-qualified name of the class to autoload.
 *
 * @return void
 */
function local_pageone_class_autoload($classname) {
    $namespace       = 'local_pageone\\';
    $namespacelength = strlen($namespace);

    if (substr($classname, 0, $namespacelength) === $namespace) {
        $filename = __DIR__ . '/classes/' . str_replace('\\', '/', substr($classname, $namespacelength)) . '.php';

        extract($GLOBALS);
        include $filename;
    }
}

spl_autoload_register('local_pageone_class_autoload');
