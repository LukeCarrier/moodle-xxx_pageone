<?php
global $CFG;
?>
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/blocks/pageone/script/prototype.js"></script>
<script type="text/javascript" src="<?php echo $CFG->wwwroot;?>/blocks/pageone/script/fieldCounter.js"></script>

<script type="text/javascript">
var smsCounter = new PageoneCounter.PageoneSMSCounter(2000);
var fieldMonitor = new PageoneCounter.PageoneCharMonitor();
var fromString = "<?php echo get_string("from", "block_pageone").":".$USER->firstname." ".$USER->lastname."\\n"; ?>";
var pageOneForm=document.getElementById('mform1');
var charLimit=<?php echo mdl21_getconfigparam("block_pageone", "char_limit"); ?>;

var incFrom=document.getElementById("id_includefrom");
if (incFrom==null)
    incFrom=document.getElementsByName("includefrom")[0];

var sendWithMM=document.getElementsByName("send_with_mm")[0];

var sub=document.getElementById("id_subject");
if (sub==null)
    sub=document.getElementsByName("subject")[0];

var mes=document.getElementById("id_message");
if (mes==null)
    mes=document.getElementsByName("message")[0];

function checkField()
{
    <?php

    if (isset($usehtmleditor) && $usehtmleditor)
        echo pageone_get_js_field();
    else
    {
    ?>
        var aField="";

        if (sub!=null)
            aField=sub.value+"\n"+mes.value;
        else
            aField=mes.value;
    <?php   
    }
    ?>

    if (typeof(aField.trim)!="undefined")
     aField=aField.trim();

    var extChars = fieldMonitor.getExtendedChars(aField);

    var overLimit=smsCounter.displayCounter(aField, $('creditusage'), incFrom);

    if (sendWithMM!=null)
        sendWithMM.disabled=overLimit;
}

var monitor=setInterval('checkField()', 1000);


<?php
 //This bit ought to go in javascript.php, but the php tags in this file don't get parsed.
 echo "var TYPE_TEXT_EMAIL=".TYPE_TEXT_EMAIL.";\n";
 echo "var TYPE_TEXT=".TYPE_TEXT.";\n";
 echo "var TYPE_EMAIL=".TYPE_EMAIL.";\n";
 echo "var TYPE_MM=".TYPE_MM.";\n";
 echo "var TYPE_TEXT_MM=".TYPE_TEXT_MM.";\n";
?>

function block_pageone_check_form()
{
    var opt=pageOneForm.messagetype.options[pageOneForm.messagetype.selectedIndex].value;

    if(pageOneForm.attachment.value.length>0 &&
      (opt==TYPE_TEXT_EMAIL || opt==TYPE_TEXT || opt==TYPE_MM || opt==TYPE_TEXT_MM))
        return confirm("<?php echo get_string('attachment_warn', 'block_pageone'); ?>");

    return true;
}

/**
 * JavaScript for checking or unchecking 
 * all the students or all students in a group.
 *
 * @param toggle Check All/None
 * @param start the first checkbox to be changed
 * @param end the last checkbox to be changed
 * return boolean
 **/

function block_pageone_toggle(toggle, start, end) {
    // Element ID
    var id = 'mailto'+start;

    // iterate through all of the appropriate checkboxes and change their state
    while(document.getElementById(id) && start != end) {
        document.getElementById(id).checked = toggle;
        start++;
        id = 'mailto'+start;
    }

    return false;
}
</script>
