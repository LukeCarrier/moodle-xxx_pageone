<?php
/**
 * email.php - Used by pageone for sending emails to users enrolled in a specific course.
 *      Calls email.hmtl at the end.
 *
 * @author Mark Nielsen. Modified for PageOne by Tim Williams (tmw@autotrain.org)
 * @package pageone
 **/
    
    require_once('../../config.php');
    require_once($CFG->libdir.'/blocklib.php');
    require_once($CFG->dirroot.'/message/lib.php');
    require_once('pageonelib.php');

    $id = required_param('id', PARAM_INT);  // course ID
    $instanceid = optional_param('instanceid', 0, PARAM_INT);
    $inid = required_param('inid', PARAM_INT);
    $show = optional_param('show', "own", PARAM_ALPHA);
    $instance = new stdClass;

    global $DB, $CFG, $USER;

    if (!$course = $DB->get_record('course', array('id'=>$id))) {
        error('Course ID was incorrect');
    }

    require_login($course->id);

    $instance=pageone_get_block_instance($instanceid, $course->id);
    if ($instance)
        $instanceid=$instance->id;

    $course_context=get_context_instance(CONTEXT_COURSE, $course->id);
    if ($instanceid==-1)
        $context=$course_context;
    else
        $context=get_context_instance(CONTEXT_BLOCK, $instanceid);

    // set up some strings
    $strpageone   = get_string('blockname', 'block_pageone');
    $header=get_string('view_message', 'block_pageone');
    $PAGE->set_url('/blocks/pageone/email.php', array('courseid' => $id, 'instanceid' => $instanceid));
    $PAGE->set_context($context);
    $PAGE->set_course($course);
    $PAGE->navbar->add(get_string('pluginname', 'block_pageone'));
    $PAGE->navbar->add($header);
    $PAGE->set_title($strpageone . ': '. $header);
    $PAGE->set_heading($strpageone . ': '.$header);
    $PAGE->blocks->show_only_fake_blocks();


/// This block of code ensures that pageone will run 
///     when the instance is not available
    if (empty($instance)) {
        $groupmode = groupmode($course);
        if (has_capability('block/pageone:cansend', $course_context)) {
            $haspermission = true;
        } else {
            $haspermission = false;
        }
    } else {
        // create a pageone block instance
        $pageone = block_instance('pageone', $instance);
        $haspermission = $pageone->check_permission();
    }
    
    if (!$haspermission) {
        print_error(get_string('permission', 'block_pageone'));
    }

    $message=$DB->get_record("block_pageone_inlog", array("id"=>$inid));

    if ($USER->id!=$message->userid)
    {  
        if (!has_capability('moodle/site:readallmessages', get_context_instance(CONTEXT_SYSTEM)))
            print_error(get_string('no_permission_view', 'block_pageone'));
    }

    /// Header setup
    pageone_print_header($course, $strpageone);

    // print the email form START
    echo $OUTPUT->heading($strpageone);

    $currenttab = 'viewin';
    include($CFG->dirroot.'/blocks/pageone/tabs.php');
    $userfrom=pageone_get_user($message->mailfrom);

    ?>
    <br /><br />
    <table>
     <tr>
      <td><b><?php print_string("from", "block_pageone"); ?></b></td>
      <td><b>:</b></td>
      <td><?php echo "<a href='".$CFG->wwwroot."/user/view.php?id=".$message->mailfrom."' ".
                               "title='".$userfrom->username."'>".$userfrom->firstname." ".$userfrom->lastname."</a>";?></td>
     </tr>
     <tr>
      <td><b><?php print_string("date", "block_pageone"); ?></b></td>
      <td><b>:</b></td>
      <td><?php echo userdate($message->timesent, "%H:%M, %d %b %y");?></td>
     </tr>
    </table>

    <p class="generalbox"><?php echo $message->message;?></p>
    <?php

    echo $OUTPUT->footer();

?>
