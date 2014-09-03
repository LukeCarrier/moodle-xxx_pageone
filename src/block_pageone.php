<?php
/**
 * pageone - Allows teachers and students to email and text one another
 *      at a course level.  Also supports group mode so students
 *      can only email their group members if desired.  Both group
 *      mode and student access to pageone are configurable by
 *      editing a pageone instance.
 *
 * @author Mark Nielsen, Tim Williams (tmw@autotrain.org) for PageOne
 * @package pageone
 **/ 

require_once("pageonelib.php");

/***This should be included, but Moodle 2 is ignoring it for some unknown reason***/
global $CFG;
require_once($CFG->libdir."/../blocks/moodleblock.class.php");

/**
 * This is the pageone block class.  Contains the necessary
 * functions for a Moodle block.  Has some extra functions as well
 * to increase its flexibility and useability
 *
 * @package pageone
 * @todo Make a global config so that admins can set the defaults (default for student (yes/no) default for groupmode (select a groupmode or use the courses groupmode)) NOTE: make sure email.php and emaillog.php use the global config settings
 **/

class block_pageone extends block_list {
    
    /**
     * Sets the block name and version number
     *
     * @return void
     **/
    function init() {
        $this->title = get_string('blockname', 'block_pageone');
        $this->version = 2012042901;
        /***Remember to update version.php as well***/
        $this->requires = 2007101591;
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function instance_allow_multiple() {
        return false;
    }
    
    /**
     * Gets the contents of the block (course view)
     *
     * @return object An object with an array of items, an array of icons, and a string for the footer
     **/
    function get_content() {
        global $USER, $CFG;

        if($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->items = array();
        $this->content->icons = array();
        
        if (empty($this->instance) or !$this->check_permission()) {
            return $this->content;
        }


        if (pageone_is_configured())
        {
            /// link to composing an email/text
            $this->content->items[] = "&nbsp;<a href=\"$CFG->wwwroot/blocks/pageone/email.php?courseid={$this->course->id}&amp;instanceid={$this->instance->id}\">".
                                    get_string('compose', 'block_pageone').'</a>';

            $this->content->icons[] = mdl21_get_output()->pix_icon('i/email', get_string('email_address', 'block_pageone'));

            /// link to history log
            $this->content->items[] = "&nbsp;<a href=\"$CFG->wwwroot/blocks/pageone/emaillog.php?courseid={$this->course->id}&amp;instanceid={$this->instance->id}\">".
                                    get_string('history', 'block_pageone').'</a>';

            $this->content->icons[] = mdl21_get_output()->pix_icon('t/log', get_string('message_log', 'block_pageone'));

            $this->content->items[] = "&nbsp;<a href=\"$CFG->wwwroot/blocks/pageone/emaillog.php?in=1&amp;courseid={$this->course->id}&amp;instanceid={$this->instance->id}\">".
                                    get_string('inhistory', 'block_pageone').'</a>';

            $this->content->icons[] =  mdl21_get_output()->pix_icon('t/log', get_string('message_log', 'block_pageone'));
        }
        else
            $this->content->items[] = get_string("not_configured", "block_pageone");

        return $this->content;
    }

    /**
     * Loads the course
     *
     * @return void
     **/
    function specialization() {
        global $COURSE;

        $this->course = $COURSE;
    }

    /**
     * Cleanup the history
     *
     * @return boolean
     **/
    function instance_delete() {
        global $COURSE;
        mdl21_get_DB()->delete_records('block_pageone_inlog', array('courseid'=>$COURSE->id));
        return mdl21_get_DB()->delete_records('block_pageone_log', array('courseid'=>$COURSE->id));
    }

    /**
     * Set defaults for new instances
     *
     * @return boolean
     **/
    function instance_create() {
        $this->config = new stdClass;
        $this->config->groupmode = $this->course->groupmode;
        $pinned = (!isset($this->instance->pageid));
        return $this->instance_config_commit($pinned);
    }

    /**
     * Allows the block to be configurable at an instance level.
     *
     * @return boolean
     **/
    function instance_allow_config() {
        return true;
    }

    /**
     * Allows the block to be configurable at the global level.
     *
     * @return boolean
     **/

    function has_config() {
      return true;
    }

    /**
     * Check to make sure that the current user is allowed to use pageone.
     *
     * @return boolean True for access / False for denied
     **/
    function check_permission() {
        return has_capability('block/pageone:cansend', get_context_instance(CONTEXT_BLOCK, $this->instance->id));
    }

    /**
     * Get the groupmode of pageone.  This function pays
     * attention to the course group mode force.
     *
     * @return int The group mode of the block
     **/
    function groupmode() {
        return groupmode($this->course, $this->config);
    }


}
?>
