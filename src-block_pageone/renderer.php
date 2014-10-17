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

use block_pageone\util;

/**
 * Renderer.
 */
class block_pageone_renderer extends plugin_renderer_base {
    /**
     * Tab: compose.
     *
     * @var string
     */
    const TAB_COMPOSE = 'compose';

    /**
     * Tab: inbox.
     *
     * @var string
     */
    const TAB_INBOX = 'inbox';

    /**
     * Tab: outbox.
     *
     * @var string
     */
    const TAB_OUTBOX = 'outbox';

    /**
     * Render the navigation tabs.
     *
     * @param integer $instanceid The ID of the block instance.
     * @param string  $selected   The currently selected tab; one of the TAB_*
     *                            constants.
     *
     * @return string The rendered HTML for the navigation tabs.
     */
    public function navigation_tabs($instanceid, $selected) {
        return print_tabs(array(
            array(
                new tabobject(static::TAB_COMPOSE,
                              util::compose_url($instanceid),
                              util::string('compose')),
                new tabobject(static::TAB_INBOX,
                              util::inbox_url($instanceid),
                              util::string('inbox')),
                new tabobject(static::TAB_OUTBOX,
                              util::outbox_url($instanceid),
                              util::string('outbox')),
            ),
        ), $selected, null, null, true);
    }
}
