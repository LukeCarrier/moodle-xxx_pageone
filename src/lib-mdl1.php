<?php

/**
* Contains the Moodle 1.x specific methods for MoodleMobile
* @author Tim Williams (tmw@autotrain.org) for PageOne
* @package pageone
**/

/**
* Include the generic abstractor classes
**/
if (!function_exists("mdl21_getconfigparam"))
 require_once("mdl21_abstract.php");

/**
* PageOne specific methods
**/

function pageone_get_email_spacer()
{
 return "";
}

function pageone_subject_params()
{
 return array('size' => '73');
}

function pageone_get_block_instance($instanceid, $courseid)
{
 if ($instanceid>-1)
 {
  return get_record('block_instance', 'id', $instanceid);
 }
 else
 {
  //The instance ID is missing, try to find a suitable instance based on the course
  if ($pageoneblock = get_record('block', 'name', 'pageone'))
  {
   $r=get_record('block_instance', 'blockid', $pageoneblock->id, 'pagetype', 'course-view', 'pageid', $courseid);
   return $r;
  }
 }
 return false;
}

function pageone_get_all_instances()
{
 if ($pageoneblock = get_record('block', 'name', 'pageone'))
 {
  return get_records('block_instance', 'blockid', $pageoneblock->id);
 }
 return array();
}

function get_enrolled_users($context)
{
 $possibleroles = get_roles_with_capability('moodle/course:view', CAP_ALLOW, $context);
 $courseusers=array();
 foreach ($possibleroles as $p)
 {
  $nc=get_role_users($p->id, $context,false, 'u.*', 'u.lastname, u.firstname', false, '', '');
  if (!empty($nc))
  {
   foreach ($nc as $u)
    $courseusers[$u->id]=$u;
  }   
 }
 return $courseusers;
}

function pageone_add_file_upload(&$mform, $obj)
{
 global $CFG;
 //$maxbytes = get_max_upload_file_size($CFG->maxbytes, $obj->_customdata['maxbytes']);
 $maxbytes = $CFG->maxbytes;
 $mform->addElement('file', 'attachment', get_string('attachmentoptional', 'block_pageone'), null, array('maxbytes' => $maxbytes, 'accepted_types' => '*'));
}

function pageone_print_header($course, $strpageone)
{
 $navigation = build_navigation($strpageone);
 print_header($course->fullname.': '.$strpageone, $course->fullname, $navigation, '', '', true);
}

function pageone_get_attachment(&$form, $context, $courseid)
{
    global $CFG;

    $attachment = '';
    $attachname = '';

    require_once($CFG->libdir.'/uploadlib.php');
        
    $um = new upload_manager('attachment', false, true, $courseid, false, 0, true);
    /****************process the posted attachment if it exists****************/
    if ($um->process_file_uploads('temp/block_pageone'))
    {
                
        // original name gets saved in the database
        $form->attachment = $um->get_original_filename();

        // check if file is there
        if (file_exists($um->get_new_filepath()))
        {
            // get path to the file without $CFG->dataroot
            $attachment = '/temp/block_pageone/'.$um->get_new_filename();
                
            // get the new name (name may change due to filename collisions)
            $attachname = $um->get_new_filename();
        }
        else
            $form->error = get_string("attachmenterror", "block_pageone", $form->attachment);
    }
    else
        $form->attachment = ''; // no attachment

    $attach=new stdclass;
    $attach->file=$attachment;
    $attach->name=$attachname;

    return $attach;
}

function pageone_get_js_field()
{
 return 'var aField=document.getElementById("id_subject").value+"\n"+'.
  'editor_78e731027d8fd50ed642340b7c9a63b3.getHTML().replace(/(<([^>]+)>)/gi, "").trim();';
}

function pageone_get_receive_users($number)
{
 $recs=get_records("block_pageone_alphatags", "alphatag", $number);
 $ret=array();
 foreach($recs as $r)
 {
  if ($r->receive)
   $ret[]=$r;
 }
 return $ret;
}
?>
