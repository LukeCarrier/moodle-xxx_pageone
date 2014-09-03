<?php
/**
 * emaillog.php - displays a log (or history) of all emails sent by
 *      a specific in a specific course.  Each email log can be viewed
 *      or deleted.
 *
 * @todo Add a print option?
 * @author Mark Nielsen. Modified for PageOne by Tim Williams (tmw@autotrain.org)
 * @package pageone
 **/
    
    require_once('../../config.php');
    require_once($CFG->libdir.'/blocklib.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once('pageonelib.php');
    
    $id = required_param('courseid', PARAM_INT);    // course id
    $action = optional_param('action', '', PARAM_ALPHA);
    $instanceid = optional_param('instanceid', -1, PARAM_INT);
    $show = optional_param('show', "own", PARAM_ALPHA);
    $in=optional_param('in', '0', PARAM_INT);

    $dbtbl="block_pageone_log";
    if ($in)
        $dbtbl="block_pageone_inlog";

    $instance = new stdClass;

    global $DB;

    if (!$course = $DB->get_record('course', array('id'=>$id))) {
        print_error('Course ID was incorrect');
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

    $blockname= get_string('pluginname', 'block_pageone');
    if ($in)
        $header = get_string('inhistory', 'block_pageone');
    else
        $header = get_string('history', 'block_pageone');

    $PAGE->set_context($context);
    $PAGE->set_course($course);
    $PAGE->navbar->add($blockname);
    $PAGE->navbar->add($header);
    $PAGE->set_title($blockname . ': '. $header);
    $PAGE->set_heading($blockname . ': '.$header);
    $PAGE->set_url('/blocks/pageone/emaillog.php', array('courseid' => $id, 'instanceid' => $instanceid));
    $PAGE->blocks->show_only_fake_blocks();

/// This block of code ensures that pageone will run 
///     whether it is in the course or not

    if (empty($instance)) {
        $context=get_context_instance(CONTEXT_COURSE, $course->id);
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

    $admin=has_capability('moodle/site:readallmessages', get_context_instance(CONTEXT_SYSTEM));
    
    if (!$haspermission) {
        print_error("permission", "block_pageone"); 
    }

    $PAGE->set_url('/blocks/pageone/emaillog.php', array('id' => $id, 'instanceid' => $instanceid, 'in'=>$in));
    
    // log deleting happens here (NOTE: reporting is handled below)
    $dumpresult = false;
    if ($action == 'dump') {
        confirm_sesskey();
        
        // delete a single log or all of them
        if ($emailid = optional_param('emailid', 0, PARAM_INT)) {
            $dumpresult = $DB->delete_records($dbtbl, array('id'=>$emailid));
        } else {
            if ($admin)
                $dumpresult = $DB->delete_records($dbtbl, array());
        }
    }
    
/// Start printing everyting
    $strpageone = get_string('blockname', 'block_pageone');
    if (empty($pastemails)) {
        $disabled = 'disabled="disabled" ';
    } else {
        $disabled = '';
    }
    $button= '';
    if ($admin)
    {
        $button = "<form method=\"post\" action=\"$CFG->wwwroot/blocks/pageone/emaillog.php\"><div>
               <input type=\"hidden\" name=\"courseid\" value=\"$course->id\" />
               <input type=\"hidden\" name=\"instanceid\" value=\"$instanceid\" />
               <input type=\"hidden\" name=\"sesskey\" value=\"".sesskey().'" />
               <input type="hidden" name="action" value="confirm" />
               <input type="submit" name="submit" value="'.get_string('clearhistory', 'block_pageone')."\" $disabled/></div>
               </form>";
    }
    
/// Header setup
    pageone_print_header($course, $strpageone);

    // print the email form START
    echo $OUTPUT->heading($strpageone);

    if ($in)
        $currenttab = 'inhistory';
    else
        $currenttab = 'history';
    include($CFG->dirroot.'/blocks/pageone/tabs.php');
    
/// delete reporting happens here
    if ($action == 'dump') {
        if ($dumpresult) {
            notify(get_string('deletesuccess', 'block_pageone'), 'notifysuccess');
        } else {
            notify(get_string('deletefail', 'block_pageone'));
        }
    }

    if ($action == 'confirm') {
        notice_yesno(get_string('areyousure', 'block_pageone'), 
                     "$CFG->wwwroot/blocks/pageone/emaillog.php?courseid=$course->id&amp;instanceid=$instanceid&amp;sesskey=".sesskey()."&amp;action=dump",
                     "$CFG->wwwroot/blocks/pageone/emaillog.php?courseid=$course->id&amp;instanceid=$instanceid");
    } else {

        echo '<form action="emaillog.php" method="get">'.
             '<table style="margin-left:auto;margin-right:auto"><tr><td>'.get_string("show").'</td><td>'.
             '    <select name="show">';

        print_message_option($show, "own", "own_messages");
        print_message_option($show, "allown", "all_own_messages");
        if ($admin)
        {
            print_message_option($show, "course", "all_course_messages");
            print_message_option($show, "all", "all_messages");
        }

        echo '    </select>'.
             '    <input type="hidden" name="courseid" value="'.$course->id.'" />'.
             '    <input type="hidden" name="instanceid" value="'.$instanceid.'" />'.
             '    <input type="hidden" name="in" value="'.$in.'" />'.
             '    <input type="submit" value="'.get_string("go").'" />'.
             '</td></tr></table></form><br />';

        echo '<div id="tablecontainer">';
        $table=pageone_get_log_table($admin, $show, $course, $in, $instanceid);
        $table->print_html();
        echo '</div><p style="margin-left:auto;margin-right:0px;width:150px;">';

        if ($in==false)
        {
            echo $OUTPUT->help_icon("status", "block_pageone", get_string("status_info_header", "block_pageone"));
            //helpbutton("messageopts", get_string("messagetype", "block_pageone"), "block_pageone", true, true);
        }
        echo '</p>';
    }

    echo $OUTPUT->footer();

   function pageone_get_log_table($admin, $show, $course, $in, $instanceid)
   {
    global $DB, $CFG, $USER;
    $table = new flexible_table('blocks-pageone-emaillog');

    /// define table columns, headers, and base url
    $table->define_columns(pageone_get_log_columns($in, $admin, $show));
    $table->define_headers(pageone_get_log_headers($in, $admin, $show));
    $table->define_baseurl($CFG->wwwroot.'/blocks/pageone/emaillog.php?in='.$in.'&amp;courseid='.$course->id.'&amp;instanceid='.$instanceid.'&amp;show='.$show);

    /// table settings
    $table->sortable(true, 'timesent', SORT_DESC);
    $table->collapsible(true);
    $table->initialbars(false);
    $table->pageable(true);

    /// column styles (make sure date does not wrap) NOTE: More table styles in styles.php
    //$table->column_style('timesent', 'width', '40%');
    $table->column_style('timesent', 'white-space', 'nowrap');

    /// set attributes in the table tag
    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('id', 'emaillog');
    $table->set_attribute('class', 'generaltable generalbox');
    //$table->set_attribute('width', '80%');
    $table->set_attribute('style', 'margin-left:auto; margin-right:auto;');

    $table->setup();  

    /// SQL
    $dbjoin="userid";
    if ($in)
        $dbjoin="mailfrom";

    $dbtbl="block_pageone_log";
    if ($in)
        $dbtbl="block_pageone_inlog";

    $sql = "SELECT * FROM {$CFG->prefix}user RIGHT JOIN {$CFG->prefix}{$dbtbl} ON {$CFG->prefix}user.id = {$CFG->prefix}{$dbtbl}.{$dbjoin}";
    $total=0;

    if ($admin && $show=="course")
    {
        $sql .= " WHERE {$CFG->prefix}{$dbtbl}.courseid = $course->id ";
        $total = $DB->count_records($dbtbl, array('courseid'=>$course->id));
    }
    else
    if ($show=="own")
    {
        $sql.=  " WHERE {$CFG->prefix}{$dbtbl}.courseid = $course->id AND {$CFG->prefix}{$dbtbl}.userid = $USER->id ";
        $total = $DB->count_records($dbtbl, array('courseid'=>$course->id, 'userid'=>$USER->id));
    }
    else
    if ($show=="allown")
    {
        $sql.=  " WHERE {$CFG->prefix}{$dbtbl}.userid = $USER->id ";
        $total = $DB->count_records($dbtbl, array('userid'=>$USER->id));
    }
    else
        $total = $DB->count_records($dbtbl);

    //if ($table->get_sql_where())
    //{
    //    $sql .= ' AND '.$table->get_sql_where();
    //}

    $sql .= ' ORDER BY '. $table->get_sql_sort();

    /// set page size
    $table->pagesize(20, $total);

    if ($pastemails = $DB->get_records_sql($sql, null, $table->get_page_start(), $table->get_page_size()))
    {
            foreach ($pastemails as $pastemail)
            {
                $table->add_data(pageone_get_log_table_row($in, $admin, $show, $pastemail, $instanceid, $USER, $course));
            }
    }

    return $table;
   }

    /**
    * Gets the table header for the log display
    * @param $in true if we are showing incoming messages
    * @param $admin true if this person is has administrator authorisation
    * @param $show The set of messages being shown, should be "all" "course" or empty.
    * @return An array object containging the correctly formatted table header data
    **/

    function pageone_get_log_headers($in, $admin, $show)
    {
        if ($in)
        {
            if ($admin && ($show=="all" || $show=="course"))
                return array(get_string('date', 'block_pageone'),get_string('to', 'block_pageone'),
                    get_string('from', 'block_pageone'), get_string('message', 'block_pageone'));

            return array(get_string('date', 'block_pageone'), get_string('from', 'block_pageone'), get_string('message', 'block_pageone'));
        }
        else
        {
            if ($admin && ($show=="all" || $show=="course"))
            {
                return array(get_string('date', 'block_pageone'), get_string('sender', 'block_pageone'), get_string('to', 'block_pageone'), get_string('subject', 'forum'),
                        get_string('attachment', 'block_pageone'), get_string('messagetype', 'block_pageone'),
                        get_string('status', 'block_pageone'), get_string('action', 'block_pageone'));
            }

            return array(get_string('date', 'block_pageone'), get_string('to', 'block_pageone'), get_string('subject', 'forum'),
                         get_string('attachment', 'block_pageone'), get_string('messagetype', 'block_pageone'), 
                         get_string('status', 'block_pageone'), get_string('action', 'block_pageone'));
        }
    }

    /**
    * Gets the table header for the log display
    * @param $in true if we are showing incoming messages
    * @param $admin true if this person is has administrator authorisation
    * @param $show The set of messages being shown, should be "all" "course" or empty.
    * @return An array object containging the correctly formatted table column data
    **/

    function pageone_get_log_columns($in, $admin, $show)
    {
        if ($in)
        {
            if ($admin && ($show=="all" || $show=="course"))
                return array('timesent','username', 'mailfrom', 'message', '');

            return array('timesent', 'mailfrom', 'message', '');
        }
        else
        {
            if ($admin && ($show=="all" || $show=="course"))
                return array('timesent', 'username', 'mailto', 'subject', 'attachment', 'messagetype', 'status', '');

            return array('timesent', 'mailto', 'subject', 'attachment', 'messagetype', 'status', '');
        }
    }

    /**
    * Gets a lo9g table row
    * @param $in true if we are showing incoming messages
    * @param $admin true if this person is has administrator authorisation
    * @param $show The set of messages being shown, should be "all" "course" or empty.
    * @param $pasteemail The email/text message to display in this row
    * @param $instanceid The instsance ID of the PageOne block in which this operation is taking place
    * @param $USER The user requesting this operation
    * @param $course The course object representing the course containing this PageOne block
    * @return An array object containging the correctly formatted table row data
    **/

    function pageone_get_log_table_row($in, $admin, $show, $pastemail, $instanceid, $USER, $course)
    {
        global $CFG, $OUTPUT, $DB;
        $row=array();
        array_push($row, userdate($pastemail->timesent, "%H:%M, %d %b %y"));

        if ($in)
        {
            if ($admin && ($show=="all" || $show=="course"))
            {
                $touser=pageone_get_user($pastemail->userid);
                array_push($row, "<a href='".$CFG->wwwroot."/user/view.php?id=".$pastemail->userid."' ".
                                 "title='".$touser->firstname." ".$touser->lastname."'>".$touser->firstname." ".$touser->lastname."</a>");
            }

            $from=get_string("user_not_found", "block_pageone")." (".$pastemail->mailfrom.")";
            if (!empty($pastemail->username))
                $from="<a href='".$CFG->wwwroot."/user/view.php?id=".$pastemail->mailfrom."' ".
                      "title='".$pastemail->username."'>".$pastemail->firstname." ".$pastemail->lastname."</a>";
            array_push($row, $from, substr($pastemail->message, 0, 20));

            $viewlink="viewin.php?inid=".$pastemail->id."&amp;id=$course->id&amp;instanceid=$instanceid&amp;show=$show";
        }
        else
        {
            if ($admin && ($show=="all" || $show=="course") )
                array_push($row, "<a href='".$CFG->wwwroot."/user/view.php?id=".$pastemail->userid."' ".
                                 "title='".$pastemail->username."'>".$pastemail->firstname." ".$pastemail->lastname."</a>");

            $attach="";
            if ($pastemail->attachment)
                $attach=format_string($pastemail->attachment, true);

            array_push($row, pageone_get_mailto_user_list($pastemail->mailto),
                       s($pastemail->subject),
                       $attach,
                       get_string("messagetype_".$pastemail->messagetype, "block_pageone"),
                       pageone_getstatus($pastemail));

            $viewlink="email.php?in=$in&amp;courseid=$course->id&amp;instanceid=$instanceid&amp;emailid=$pastemail->id&amp;action=view&amp;editable=0";

        }
        array_push($row, "<a href=\"".$viewlink."\">".
                      $OUTPUT->pix_icon("i/search", get_string('view'))."</a> ".
                      "<a href=\"emaillog.php?show=$show&amp;in=$in&amp;courseid=$course->id&amp;instanceid=$instanceid&amp;sesskey=$USER->sesskey&amp;action=dump&amp;emailid=$pastemail->id\">".
                      $OUTPUT->pix_icon("t/delete", get_string('delete'))."</a>");
        return $row;
    }

    /**
    * Get a status string for the specified message
    * @param $email The message to check
    * @return A status string
    **/

    function pageone_getstatus($email)
    {
        if ($email->status==PAGEONE_ERRORS)
            return get_string("error");
        if ($email->status==PAGEONE_CONFIRMED)
            return get_string("confirmed", "block_pageone");
        if ($email->status==PAGEONE_CONFIRMED_ERRORS)
            return get_string("confirmed_errors", "block_pageone");
        return get_string("sent", "block_pageone");
    }

    /**
    * Processess the users a message was sent to into an HTML formatted list
    * @param array of the user ID's the message was sent to
    * @return The HTML formatted list
    **/

    function pageone_get_mailto_user_list($users)
    {
        global $CFG, $DB;
        $tok=strtok($users, ",");
        $data="";
        $first=true;
        $count=0;

        while ($tok !== false)
        {
            if ($first)
                $first=false;
            else
                $data.=", ";

            $user=pageone_get_user($tok);
            $data.="<a href='".$CFG->wwwroot."/user/view.php?id=".$user->id."' ".
                                    "title='".$user->username."'>".$user->firstname." ".$user->lastname."</a>";
            $tok = strtok(" \n\t");
            $count++;
            if ($count>3 && $tok!=false)
            {
                $tok=false;
                $data.=" ...";
            }
        }

        return $data;
    }

    /**
    * Prints out a message log viewing option
    * @param $show The option currently selected
    * @param $key The form key used for this option
    * @param $message The name of the language string to display for this option
    **/

    function print_message_option($show, $key, $message)
    {
            if ($show==$key)
                echo '        <option value="'.$key.'" selected="selected">'.get_string($message, "block_pageone").'</option>';
            else
                echo '        <option value="'.$key.'">'.get_string($message, "block_pageone").'</option>';
    }
?>
