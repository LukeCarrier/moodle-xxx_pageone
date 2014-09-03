<?

/**
 * Form for editing Pageone block instances.
 *
 * @package   block_pageone
 * @author    Charles Fulton, Tim Williams (tmw@autotrain.org) for PageOne
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */

class block_pageone_edit_form extends block_edit_form
{
 protected function specific_definition($mform)
 {
  $mform->addElement('header','configheader',get_string('blocksettings', 'block'));
        
  $options = array(
   NOGROUPS=>get_string('groupsnone'),
   SEPARATEGROUPS=>get_string('groupsseparate'),
   VISIBLEGROUPS=>get_string('groupsvisible')
  );
                
  if($this->page->course->groupmodeforce)
  {
   $mform->addElement('select','config_groupmode',get_string('groupmode'), $options, array('disabled' => 'disabled'));
   $mform->setDefault('config_groupmode', $this->page->course->groupmode);
  }
  else
  {
   $mform->addElement('select','config_groupmode',get_string('groupmode'), $options);
   $mform->setDefault('config_groupmode', NOGROUPS);
  }
 }
}
?>
