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

/**
 * Track an individual successful upgrade.
 *
 * In the event of one of our upgrades failing, this will allow Moodle to skip
 * the upgrade in the future.
 *
 * @param integer $version The new version the site is at.
 *
 * @return void
 */
function xmldb_local_pageone_savepoint($version) {
    upgrade_plugin_savepoint(true, $version, 'local', 'pageone');
}

/**
 * Upgrade handler.
 *
 * @param integer $oldversion The pre-upgrade version of the site.
 *
 * @return boolean Always true, as we're using upgrade savepoints to track
 *                 successful progression.
 */
function xmldb_local_pageone_upgrade($oldversion=0) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2008050803) {
        $table = new xmldb_table('block_pageone_log');
        $field = new xmldb_field('includefrom', XMLDB_TYPE_INTEGER, 1,
                                 XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0,
                                 'failednumbers');

        $dbman->add_field($table, $field);

        xmldb_local_pageone_savepoint(2008050803);
    }

    if ($oldversion < 2008082601) {
        $table = new xmldb_table('block_pageone_alphatags');
        $field = new xmldb_field('receive', XMLDB_TYPE_INTEGER, 1,
                                 XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0,
                                 'alphatag');

        $dbman->add_field($table, $field);

        xmldb_local_pageone_savepoint(2008082601);
    }

    if ($oldversion < 2008082901) {
        $table = new xmldb_table('block_pageone_log');
        $field = new xmldb_field('ovid', XMLDB_TYPE_INTEGER, 15,
                                 XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0,
                                 'includefrom');

        $dbman->add_field($table, $field);

        xmldb_local_pageone_savepoint(2008082901);
    }

    if ($oldversion < 2009011309) {
        $table = new xmldb_table('block_pageone_inlog');

        $table->add_field(new xmldb_field('id', MLDB_TYPE_INTEGER, 10,
                                          XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, 0,
                                          null));
        $table->add_field(new xmldb_field('courseid', XMLDB_TYPE_INTEGER, 10,
                                          XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0,
                                          'id'));
        $table->add_field(new xmldb_field('userid', XMLDB_TYPE_INTEGER, 10,
                                          XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0,
                                          'courseid'));
        $table->add_field(new xmldb_field('mailfrom', XMLDB_TYPE_INTEGER, 16,
                                          XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0,
                                          'userid'));
        $table->add_field(new xmldb_field('timesent', XMLDB_TYPE_INTEGER, 10,
                                          XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0,
                                          'mailfrom'));
        $table->add_field(new xmldb_field('message', XMLDB_TYPE_TEXT, 'small',
                                          null, XMLDB_NOTNULL, null, '',
                                          'timesent'));

        $table->add_key(new xmldb_key('id',       XMLDB_KEY_PRIMARY, array('id')));
        $table->add_key(new xmldb_key('courseid', XMLDB_KEY_FOREIGN, array('courseid')));
        $table->add_key(new xmldb_key('userid',   XMLDB_KEY_FOREIGN, array('userid')));

        $dbman->create_table($table);

        xmldb_local_pageone_savepoint(2009011309);
    }

    return true;
}
