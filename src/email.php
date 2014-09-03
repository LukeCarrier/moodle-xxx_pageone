<?php
/**
 * email.php - Used by pageone for sending emails to users enrolled in a specific course.
 *
 * @author Mark Nielsen
 * @author Charles Fulton
 * @author Tim Williams (tmw@autotrain.org) for PageOne
 * @version 2.00
 * @package pageone
 **/

    require_once('../../config.php');
    require_once($CFG->libdir.'/blocklib.php');
    require_once($CFG->dirroot.'/blocks/pageone/email_form.php');
    require_once($CFG->dirroot.'/blocks/pageone/pageonelib.php');

    $to=optional_param('to', -1, PARAM_INT);
    $id=optional_param('courseid', -1, PARAM_INT);  // course ID
    $instanceid=optional_param('instanceid', -1, PARAM_INT);
    $action=optional_param('action', '', PARAM_ALPHA);
    $sendtype=optional_param('type', 0, PARAM_INT);
    $emailid=optional_param('emailid', -1, PARAM_INT);

    global $DB, $USER, $PAGE;

    if ($id==-1)
        $id=pageone_find_course($to, $USER->id, true);

    if (!$course = $DB->get_record('course', array('id'=>$id)))
        print_error('Course ID was incorrect');

    require_login($course->id);

    $instance=pageone_get_block_instance($instanceid, $course->id);
    if ($instance)
        $instanceid=$instance->id;
    $course_context=get_context_instance(CONTEXT_COURSE, $course->id);
    if ($instanceid==-1)
        $context=$course_context;
    else
        $context=get_context_instance(CONTEXT_BLOCK, $instanceid);

    $blockname = get_string('pluginname', 'block_pageone');
    $header = get_string('compose', 'block_pageone');

    $PAGE->set_url('/blocks/pageone/email.php', array('courseid' => $id, 'instanceid' => $instanceid));
    $PAGE->set_context($context);
    $PAGE->set_course($course);
    $PAGE->navbar->add($blockname);
    $PAGE->navbar->add($header);
    $PAGE->set_title($blockname . ': '. $header);
    $PAGE->set_heading($blockname . ': '.$header);
    $PAGE->blocks->show_only_fake_blocks();

    /*********************get the default format******************/
    if ($usehtmleditor = can_use_html_editor())
        $defaultformat = FORMAT_HTML;
    else
        $defaultformat = FORMAT_MOODLE;

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
        $groupmode     = $pageone->config->groupmode;
        $haspermission = $pageone->check_permission();
    }
    
    if (!$haspermission) {
        print_error(get_string('permission', 'block_pageone'));
    }

    $courseusers=array();
    if ($to>-1)
        $courseusers[$to]=$DB->get_record("user", array("id"=>$to));
    else
    if (!$courseusers = get_enrolled_users($course_context))
    {
        if ($action!="view")
            print_error(get_string('no_course_users', 'block_pageone'));
    }

    if($action == 'view')
    {
        // viewing old email        
        $udb = $DB->get_record('block_pageone_log', array('id' => $emailid));
        //Check that this user owns the email, if not they need sysadmin permission to see it
        if ($USER->id!=$udb->userid)
        {  
            if (!has_capability('moodle/site:readallmessages', get_context_instance(CONTEXT_SYSTEM)))
                print_error(get_string('no_permission_view', 'block_pageone'));
        }
        $ulst = explode(',', $udb->mailto);
        foreach ($ulst as $us)
            $courseusers[$us]=$DB->get_record("user", array("id"=>$us));
    }

    // Groups mode handling
    // Separate groups only makes sense for a student with the cansend capability
    if($groupmode == SEPARATEGROUPS && has_capability('moodle/site:accessallgroups', $context)) {
        $groupmode = VISIBLEGROUPS;
    }

    // Build groups list
    // To make processing easier we do this even if we're not in groups mode
    $nogroup = new stdClass;
    $nogroup->id = 0;
    $nogroup->name = get_string('notingroup', 'block_pageone');
    $userlist = array();
    switch($groupmode) {
        case NOGROUPS:
            $groups = array('0' => $nogroup);
            $userlist[0] = array('' => $groups[0]->name);
            break;
        case VISIBLEGROUPS:
            $groups = groups_get_all_groups($id);
            $groups[0] = $nogroup;
            $userlist[0] = array('' => $groups[0]->name);
            break;
        case SEPARATEGROUPS:
            $groups = array();
            $grouplist = groups_get_user_groups($id, $USER->id);
            foreach($grouplist[0] as $group) {
                $groups[$group] = groups_get_group($group);
            }
            break;
    }

    //Should we use the checkbox display?
    $cbmode=true;
    if (count($courseusers)>21)
        $cbmode=false;

    // Build user lists
    $userlist = array();
    if ($groupmode==NOGROUPS)
        $userlist[0]=array('name' => get_string('to'), 'elements'=> array());
    else
        $userlist[0]=array('name' => get_string('notingroup', 'block_pageone'), 'elements'=> array());

    foreach($courseusers as $user)
    {
        $nonmembership = true;
        foreach($groups as $groupid => $group)
        {
            if(groups_is_member($groupid, $user->id))
            {
                $nonmembership = false;
                if(empty($userlist[$groupid]))
                {
                    $userlist[$groupid] = array('name' => $group->name, 'elements'=> array());
                }
                $r=pageone_test_contacts($user, $course_context, $cbmode);
                $userlist[$groupid]['elements'][$user->id] = fullname($user).' '.$r->mobile;
                break;
            }
        }
        if($nonmembership && ($groupmode != SEPARATEGROUPS))
        {
            $r=pageone_test_contacts($user, $course_context, $cbmode);
            $userlist[0]['elements'][$user->id] = fullname($user).' '.$r->mobile;
        }
    }

    $mform = new pageone_email_form($userlist, $cbmode, $defaultformat, $course->id, $to, $action, $emailid);   
    $showform=" ";     
    if($mform->is_cancelled()) {
        // Form was cancelled; redirect to course
        $showform=false;
        redirect("$CFG->wwwroot/course/view.php?id=$course->id");        
    } elseif ($fromform = $mform->get_data()) {
            $showform=pageone_send_all_messages($fromform, $mform, $courseusers, $course, $context);
    }

    if ($showform)
    {
        // Data didn't validate OR first load of form
        $data = new stdClass;

        /******************************************************/
        /************Do I Need to do something about this?*****/
        //$data->format = $pageone->config->defaultformat;
        if($action == 'view') {
            // viewing old email
            $data = $DB->get_record('block_pageone_log', array('id' => $emailid));
            $data->mailto = explode(',', $data->mailto);
            if ($cbmode)
            {
                foreach($data->mailto as $id)
                    $data->{"mailto-".$id}=true;
            }
            else
            {
                // $data->mailto isn't very useful because it needs to be broken down by group
                foreach($userlist as $groupid => $list)
                    $data->{"mailto[$groupid]"} = array_intersect($data->mailto,array_keys($list['elements']));
            }            
        }
        $data->id = $id;
        $data->instanceid = $instanceid;
        $data->maxbytes = $course->maxbytes;

        $mform->set_data($data);
    }
    
    // set up some strings
    $strpageone   = get_string('blockname', 'block_pageone');

/// Header setup
    pageone_print_header($course, $strpageone);

    // print the email form START
    echo $OUTPUT->heading($strpageone);

    $currenttab = 'compose';
    include($CFG->dirroot.'/blocks/pageone/tabs.php');

    // error printing
    if (isset($data->error)) {
        notify($data->error);
        if (isset($data->usersfail)) {
            $errorstring = '';

            if (isset($data->usersfail['emailfail'])) {
                $errorstring .= get_string('emailfail', 'block_pageone').'<br />';
                foreach($data->usersfail['emailfail'] as $user) {
                    $errorstring .= $user.'<br />';
                }               
            }

            if (isset($data->usersfail['textfail'])) {
                $errorstring .= get_string('textfail', 'block_pageone').'<br />';
                foreach($data->usersfail['textfail'] as $user) {
                    $errorstring .= $user.'<br />';
                }               
            }

            if (isset($data->usersfail['emailstop'])) {
                $errorstring .= get_string('emailstop', 'block_pageone').'<br />';
                foreach($data->usersfail['emailstop'] as $user) {
                    $errorstring .= $user.'<br />';
                }               
            }

            notice($errorstring, "$CFG->wwwroot/course/view.php?courseid=$course->id", $course);
        }
    }

    /***Show errors***/

    if (isset($data->status))
    {
        if ($data->status==PAGEONE_ERRORS || $data->status==PAGEONE_CONFIRMED_ERRORS)
        {
            notify('<p>'.get_string("errors", "block_pageone").'</p>');
            if (isset($data->failednumbers))
                echo pageone_get_failed_numbers($data->failednumbers);
        }
    }

    if ($showform)
        echo $showform;

    /***Display the form***/
    $mform->display();
    include($CFG->dirroot.'/blocks/pageone/email_javascript.php');
    echo $OUTPUT->footer();

    function pageone_send_all_messages(&$fromform, &$mform, $courseusers, $course, $context)
    {
        global $USER, $CFG;

        if (!isset($fromform->includefrom))
            $fromform->includefrom=false;

        // Form was submitted and validated
        $fromform->subject = clean_param(strip_tags($fromform->subject, '<lang><span>'), PARAM_RAW);
        $fromform->message = clean_param($fromform->message, PARAM_CLEANHTML);
        $fromform->messagetype=intval($fromform->messagetype);

        $fromform->plaintxt = trim(format_text_email($fromform->message, FORMAT_HTML));
        // If we're doing plaintext then we don't want to send along an HTML formatted message
        $fromform->html = ($fromform->format == FORMAT_HTML) ? format_text($fromform->message, FORMAT_HTML) : '';

        /***Check if the character limit has been exceeded***/
        if (pageone_char_limit_exceeded($USER, $fromform->subject, $fromform->plaintxt, $fromform->includefrom))
        {
            return '<div class="generalbox errorbox" style="width:60%;margin-left:auto;margin-right:auto;">'.
                get_string('char_limit_error', 'block_pageone').'</div>';
        }

        $temp = array();
        if ($fromform->cbmode)
        {
            //Find all the mailtos and create an array
            foreach($fromform as $param=>$value)
            {
                $pos=strpos($param, "mailto-");

                // !== is used here to determin the difference between boolean false and position 0
                if ($pos!==false && $pos==0)
                {
                    if ($value)
                        $temp[]=intval(substr($param, 7));
                }
            }
        }
        else
        {
            // $fromform->mailto will have arrays of arrays; we need to merge these down
            foreach($fromform->mailto as $group)
                $temp = array_merge($temp, $group);
        }
        $fromform->mailto = $temp;

        /***Check there is somebody to send a message too***/
        if (count($fromform->mailto)==0)
        {
            return '<div class="generalbox errorbox" style="width:60%;margin-left:auto;margin-right:auto;">'.
                get_string('no_user_select', 'block_pageone').'</div>';
        }
        
        // Get the attachment
        $attachment=pageone_get_attachment($mform, $context, $course);

        // Store the successful emails
        $mailedto = array();
        $users_to_text=array();
        
        foreach($fromform->mailto as $userid)
        {
            if(empty($userid))
            {
                continue;
            }
            set_time_limit(300);
            if ($fromform->messagetype==TYPE_TEXT_EMAIL || $fromform->messagetype==TYPE_EMAIL)
            {
                $mailresult = email_to_user($courseusers[$userid], $USER, $fromform->subject, $fromform->plaintxt, $fromform->html, $attachment->file, $attachment->name);
                if(!$mailresult)
                {
                    $fromform->error = get_string('emailfailerror', 'block_pageone');
                    $fromform->usersfail['emailfail'][] = $courseusers[$userid]->lastname . ', '. $courseusers[$userid]->firstname;
                }
                else
                {
                    $mailedto[] = $userid;
                }
            }
 
            if ($fromform->messagetype==TYPE_TEXT_MM || $fromform->messagetype==TYPE_MM)
            {
                message_post_message($USER, $courseusers[$userid], $fromform->html, FORMAT_HTML, 'direct');
                $mailedto[] = $userid;
            }

            array_push($users_to_text, $courseusers[$userid]);
        }

        /***************Send the text messages*****************/
        $ovid=0;
        if (count($users_to_text)>0 && ($fromform->messagetype==TYPE_TEXT_EMAIL || $fromform->messagetype==TYPE_TEXT || $fromform->messagetype==TYPE_TEXT_MM))
        {
             $textresult=pageone_send_text($users_to_text, $USER, $fromform->subject, $fromform->plaintxt, $fromform->includefrom);
             if ($textresult->ok==false)
             {
                $fromform->texterror = get_string("textfail", "block_pageone");
                if (isset($textresult->faultstring))
                    $fromform->texterror.=' '.$textresult->faultstring;
                if (isset($textresult->failednumbers))
                    $fromform->failednumbers = $textresult->failednumbers;
             }
             else
                 $ovid=$textresult->id;

             if ($fromform->messagetype==TYPE_TEXT)
                foreach ($textresult->valid_users as $userid)
                    $mailedto[]=$userid;
        }
        
        // if it exists, delete the attached file
        if(!empty($attachment->file))
        {
            if(!is_writable($CFG->dataroot . $attachment->file))
            {
                print_error("No write access to ".$CFG->dataroot.$attachment->file);
            }
            else
            {
                if(!unlink($CFG->dataroot . $attachment->file))
                {
                    print_error("Failed to delete ".$CFG->dataroot.$attachment->file);
                }
            }
        }
        
        // log email to {block_pageone_log} table
        $log = new stdClass;
        $log->ovid       = $ovid;
        $log->courseid   = $course->id;
        $log->userid     = $USER->id;
        $log->mailto     = implode(',', $mailedto);
        $log->subject    = $fromform->subject;
        $log->message    = $fromform->message;
        $log->attachment = $attachment->name;
        $log->format     = $fromform->format;
        $log->timesent   = time();
        $log->messagetype = $fromform->messagetype;

        $log->includefrom=$fromform->includefrom;

        if (isset($fromform->error) || isset($form->texterror))
            $log->status=PAGEONE_ERRORS;
        else
            $log->status=PAGEONE_NO_ERRORS;
        if (isset($fromform->failednumbers))
            $log->failednumbers=$form->failednumbers;
        else
            $log->failednumbers="";

        global $DB;
        if (!$DB->insert_record('block_pageone_log', $log))
        {
            print_error('Email not logged.');
        }
        
        if(!isset($fromform->error))
        {  // if no emailing errors, we are done
            // inform of success and continue
            redirect("$CFG->wwwroot/course/view.php?id=$course->id", 
             "<h1>".get_string('blockname', 'block_pageone')."</h1>".
             "<div class='generalbox'><p>".get_string('successfulemail', 'block_pageone')."</p></div>");
        }

        return false;
    }

    /**
    * Tests a users contact details for validity
    * @param $user The user to test
    * @param $context The authorisation context of the user requesting this operation
    * @param $cbmode true if we are using checkboxes to display the users instead of a list
    * @return Associative array ->mobile HTML showning the status icons ->disable true if the user has no valid contact details
    **/

    function pageone_test_contacts($user, $context, $cbmode)
    {
            global $CFG, $OUTPUT;
            $r=new stdClass();
            $r->emailok=true;
            $email="";
            if (has_capability('moodle/user:viewhiddendetails', $context))
                $email=$user->email;
            else
                $email=get_string('email_found', 'block_pageone');

            if (strlen($user->email)>0 && $user->email!="root@localhost" && !$user->emailstop)
            {
                if ($cbmode)
                    $r->mobile='<img src="'.$OUTPUT->pix_url("/t/email").'" height="14" width="14" title="'.$email.'" alt="'.$email.'"  class="mailtopic" />';
            }
            else
            {
                $r->emailok=false;
                if ($cbmode)
                    $r->mobile='<img src="'.$OUTPUT->pix_url("/t/emailno").'" height="14" width="14" title="'.get_string('email_not_found', 'block_pageone').
                        '" alt="'.get_string('email_not_found', 'block_pageone').'" class="mailtopic" />';
                else
                    $r->mobile=" #";
            }

            $r->mobileok=true;
            if (!pageone_has_valid_mobile_number($user))
            {
                if ($cbmode)
                    $r->mobile.='<img src="nophone.gif" width="14" height="14" alt="'.get_string("no_mobile", "block_pageone").'"'.
                        ' title="'.get_string("no_mobile", "block_pageone").'" class="mailtopic" />';
                else
                    $r->mobile.=" *";
                $r->mobileok=false;
            }
            else
            {
                $number="";
                if (has_capability('moodle/user:viewhiddendetails', $context))
                    $number=pageone_find_mobile_number($user);
                else
                    $number=get_string("mobile_found", "block_pageone");
                if ($cbmode)
                    $r->mobile.='<img src="phone.gif" width="14" height="14" alt="'.$number.'" title="'.$number.'" class="mailtopic" />';
                  
            }

            $r->disabled='';
            if ($r->emailok==false && $r->mobileok==false)
                $r->disabled='disabled="disabled"';
            return $r;
    }

    function pageone_get_failed_numbers($failednumbers)
    {
        global $DB, $CFG;
        $errorstring="";

        $tok=explode(",", $failednumbers);

        if (count($tok)>0)
        {	

            $errorstring.='<div style="text-align:center"><table style="margin-left:auto;margin-right:auto;" class="flexible generaltable generalbox">'.
                '<tr><th class="header c1">'.get_string("user", "block_pageone").'</th>'.
                '<th class="header c1">'.get_string("error").'</th></tr>';

            $topup=false;
            for ($loop=0; $loop<count($tok); $loop=$loop+2)
            {
                if (strlen($tok[$loop])>0)
                {
                    $user=$DB->get_record("user", array("id"=>$tok[$loop]));
                    if (isset($user->id))
                        $errorstring.=' <tr><td class="cell mailtocell c1"><a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'" '.
                                    'title="'.$user->username.'">'.$user->firstname.' '.$user->lastname.'</a></td>';
                    else
                        $errorstring.=' <tr><td class="cell mailtocell c1">'.$tok[$loop].'</td>';
	
                    if ($tok[$loop+1]=="552" || $tok[$loop+1]=="553" || $tok[$loop+1]=="553" || $tok[$loop+1]=="559")
                        $topup=true;
                    $errorstring.='<td class="cell mailtocell c1">'.get_string("error_code_".$tok[$loop+1], "block_pageone").'</td></tr>';
                }
            }

            $errorstring.='</table></div><br /><br />';

            if ($topup)
                notify(get_string("credit_message".$tok, "block_pageone"));
        }

        return $errorstring;
    }
?>
