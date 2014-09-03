<?php
/**
 * @author Charles Fulton, modified by Tim Williams (tmw@autotrain.org) for PageOne
 * @version 2.00
 * @package pageone
 */
    require_once("$CFG->libdir/formslib.php");
    
    class pageone_email_form extends moodleform
    {
        
        var $userlist;		// special user table

        function __construct($userlist, $cbmode, $defaultformat, $courseid, $to, $action, $emailid)
        {
            $this->userlist = $userlist;
            $this->cbmode=$cbmode;
            $this->defaultformat=$defaultformat;
            $this->courseid=$courseid;
            $this->to=$to;
            $this->action=$action;
            $this->emailid=$emailid;
            moodleform::moodleform();
        }
        
        function definition()
        {            
            $mform =& $this->_form;

            // Recipients                
            // Display lists
            if ($this->cbmode || $this->to>-1)
            {
                $row=0;
                $to=get_string('to', 'block_pageone');
                foreach($this->userlist as $groupid =>$list)
                {
                    $linecount=0;
                    $line=array();
                    $line[]= &$mform->createElement('static', '', '',
                          pageone_get_email_spacer()."\n<table class=\"flexible generaltable generalbox mailtable\">\n");

                    if ($list['name']!=$to)
                        $line[]= &$mform->createElement('static', '', '', "<tr><th class=\"header c1 mailheader\" colspan=\"3\">".
                            $list['name']."</th></tr>");

                    foreach ($list['elements'] as $id=>$name)
                    {
                        if ($linecount==0)
                            $line[]= &$mform->createElement('static', '', '', "<tr>\n");

                        $line[]= &$mform->createElement('static', '', '', "<td class=\"cell mailtocell\">\n");
                        $line[]= &$mform->createElement('advcheckbox', 'mailto-'.$id, '', $name, array('group' => $groupid));
                        if ($id==$this->to)
                            $mform->setDefault('mailto-'.$id, true);

                        $line[]= &$mform->createElement('static', '', '', "\n</td>\n");
                        if ($linecount<2)
                            $linecount++;
                        else
                        {
                            $row++;
                            $line[]= &$mform->createElement('static', '', '', "</tr>\n");
                            $linecount=0;
                        }
                    }

                    if ($row>0 && $linecount>0)
                        for ($loop=$linecount; $loop<2; $loop++)
                            $line[]= &$mform->createElement('static', '', '', "<td>&nbsp;</td>\n");

                    if ($linecount>0)
                        $line[]= &$mform->createElement('static', '', '', "</tr>\n");

                    $line[]=&$mform->createElement('static', '', '', "</table>\n");

                    $mform->addGroup($line, 'mailto[]', $to , array(' '), false);
                    $mform->addElement('html', '<div style="width:35%;">');
                    $this->add_checkbox_controller($groupid);
                    $mform->addElement('html', '</div><br />');
                    //$to="&nbsp;";
                }
                $mform->addRule('mailto[]', null, 'required', null);
            }
            else
            {
                $select_lists = array();
                $count=0;
                foreach($this->userlist as $groupid => $list)
                {
                    /***Need to use static elements here, html ones do not display inline***/
                    $select_lists[]=&$mform->createElement('static', '', '', '<table class="mailtotable"><tr><td style="text-align:center;">'.$list['name'].'<br />');
                    $select =&$mform->createElement('select',$groupid,$list['name'], $list['elements'], array('size'=>'7', 'class'=>'mailtoitem'));
                    $select->setMultiple(true);
                    $select_lists[]=$select;
                    $select_lists[]=&$mform->createElement('static', '', '', '</td></tr></table>');
                }

                $select_lists[]=&$mform->createElement('static', '', '', '<br /><div><p style="font-size:small;">'.
                    get_string('mailto_key', 'block_pageone').'</p><p style="font-size:small;">'.get_string('select_help', 'block_pageone').'</p></div>');
                $mform->addGroup($select_lists, 'mailto', get_string('to', 'block_pageone'), false);
                $mform->addRule('mailto', null, 'required', null);
            }

            // Message Type
            $t_options = array(
                TYPE_TEXT_EMAIL   => get_string('messagetype_'.TYPE_TEXT_EMAIL, 'block_pageone'),
            	TYPE_TEXT => get_string('messagetype_'.TYPE_TEXT, 'block_pageone'),
                TYPE_EMAIL => get_string('messagetype_'.TYPE_EMAIL, 'block_pageone')
            );
            $mform->addElement('select','messagetype',get_string('messagetype', 'block_pageone'), $t_options);
            //$mform->setHelpButton('messagetype', array('messageopts', get_string('messagetype_help_button', 'block_pageone'), 'block_pageone'));
            
            $mform->addElement('checkbox', 'includefrom', get_string('includefrom', 'block_pageone'));

            // Subject
            $mform->addElement('text', 'subject', get_string('subject', 'forum'), pageone_subject_params());
            $mform->addRule('subject', null, 'required');
            
            // Message
            $mform->addElement('htmleditor', 'message', get_string('message', 'forum'), array('cols' => '80'));
            $mform->setType('message', PARAM_RAW);
            //$mform->addRule('message', null, 'required', null, 'client');

            $mform->addElement('html', '<div class="fitem">'.
                '<div class="fitemtitle">'.get_string('credit_usage', 'block_pageone').'</div>'.
                '<div class="felement ftext"><div id="creditusage"></div><br /><div id="warningCell"></div></div>'.
                '.</div>');
            
            // Formatting
            $options = array(
                FORMAT_HTML   => get_string('formathtml'),
            	FORMAT_PLAIN => get_string('formatplain')
            );
            $mform->addElement('select','format',get_string('emailformat'), $options, $this->defaultformat);
            
            // Attachment
            pageone_add_file_upload($mform, $this);
            // Hidden stuff
            $mform->addElement('hidden', 'courseid', $this->courseid);
            $mform->addElement('hidden', 'instanceid');
            $mform->addElement('hidden', 'groupmode');
            $mform->addElement('hidden', 'cbmode', $this->cbmode);
            $mform->addElement('hidden', 'to', $this->to);
            $mform->addElement('hidden', 'action', $this->action);
            $mform->addElement('hidden', 'emailid', $this->emailid);

            // Submit
            $this->add_action_buttons(true, get_string('sendemail', 'block_pageone'));
        }
    }
?>
