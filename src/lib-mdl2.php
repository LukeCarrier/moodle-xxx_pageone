<?php

if (!function_exists("mdl21_getconfigparam"))
 require_once("mdl2_generic.php");

/**
* Contains the Moodle 2.x specific methods for MoodleMobile
* @author Tim Williams (tmw@autotrain.org) for PageOne
* @package pageone
**/

function pageone_get_email_spacer()
{
 return "<div class=\"fitemtitle\">&nbsp;</div>";
}

function pageone_subject_params()
{
 return array('size' => '83');
}

function pageone_get_block_instance($instanceid, $courseid)
{
 global $DB;

 if ($instanceid>-1)
 {
  return $DB->get_record('block_instances', array('id' => $instanceid));
 }
 else
 {
  //The instance ID is missing, try to find a suitable instance based on the course
  if ($pageoneblock = $DB->get_record('block', array('name' => 'pageone')))
  {
   $context = get_context_instance(CONTEXT_COURSE, $courseid);
   return $DB->get_record('block_instances', array('id' => $pageoneblock->id, 'parentcontextid' => $context->id));
  }
 }

 return false;
}

function pageone_get_all_instances()
{
 global $DB;
 return $DB->get_records('block_instances', array('blockname'=>'pageone'));
}

function pageone_add_file_upload(&$mform, $obj)
{
 global $CFG;
 //$maxbytes = get_max_upload_file_size($CFG->maxbytes, $obj->_customdata['maxbytes']);
 $maxbytes = $CFG->maxbytes;
 $mform->addElement('filepicker', 'attachment', get_string('attachmentoptional', 'block_pageone'),
  null, array('maxbytes' => $maxbytes, 'accepted_types' => '*'));
}


function pageone_print_header($course, $strpageone)
{
 global $CFG;
 $navigation = ($course->category) ? "<a href=\"$CFG->wwwroot/course/view.php?id=$course->id\">$course->shortname</a> ->" : '';
 print_header($course->fullname.': '.$strpageone, $course->fullname, "$navigation $strpageone", '', '', true);
}

function pageone_get_attachment(&$mform, $context)
{
    global $CFG;
    $attachment=new stdclass;
    $attachment->file=$mform->save_temp_file('attachment');
    if($attachment)
    {
        // email_to_user() supplies the dataroot, so we remove it
        $attachment->file= str_replace($CFG->dataroot,'',$attachment->file);
        $attachment->name = $mform->get_new_filename('attachment');
    }
    else
    {
        $attachment->file = null;
        $attachment->name = '';
    }
    return $attachment;
}

function pageone_get_receive_users($number)
{
 global $DB;
 return $DB->get_records("block_pageone_alphatags", array("alphatag"=>$number, "receive"=>true));
}

function pageone_get_js_field()
{
 return 'var aField=document.getElementById("id_subject").value.trim()+"\n"+'.
  'tinyMCE.editors["id_message"].getContent().replace(/(<([^>]+)>)/gi, "").trim();';
}

?>
