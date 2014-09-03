<?php 
/**
 * Tabs for pageone
 *
 * @author Mark Nielsen, Tim Williams (tmw@autotrain.org) for PageOne
 * @package pageone
 **/

    if (empty($course)) {
        error('Programmer error: cannot call this script without $course set');
    }
    if (!isset($instanceid)) {
        $instanceid = 0;
    }
    if (empty($currenttab)) {
        $currenttab = 'compose';
    }

    $rows = array();
    $row = array();

    $row[] = new tabobject('compose', "$CFG->wwwroot/blocks/pageone/email.php?courseid=$course->id&amp;instanceid=$instanceid", get_string('compose', 'block_pageone'));
    $row[] = new tabobject('history', "$CFG->wwwroot/blocks/pageone/emaillog.php?courseid=$course->id&amp;instanceid=$instanceid", get_string('history', 'block_pageone'));
    $row[] = new tabobject('inhistory', "$CFG->wwwroot/blocks/pageone/emaillog.php?in=1&amp;courseid=$course->id&amp;instanceid=$instanceid", get_string('inhistory', 'block_pageone'));
    if ($currenttab=='viewin')
        $row[] = new tabobject('viewin', "", get_string('view_message', 'block_pageone'));
    $rows[] = $row;

    print_tabs($rows, $currenttab);
?>
