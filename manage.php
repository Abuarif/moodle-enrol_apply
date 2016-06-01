<?php
/**
 * *************************************************************************
 * *                  Apply Enrol                                         **
 * *************************************************************************
 * @copyright   emeneo.com                                                **
 * @link        emeneo.com                                                **
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later  **
 * *************************************************************************
 * ************************************************************************
*/

require_once ('../../config.php');
require_once($CFG->dirroot.'/enrol/apply/lib.php');
require_once($CFG->dirroot.'/enrol/apply/manage_table.php');

$id = optional_param('id', null, PARAM_INT);
$userenrolments = optional_param_array('userenrolments', null, PARAM_INT);

require_login();

$manageurlparams = array();
if ($id == null) {
    $context = context_system::instance();
    require_capability('enrol/apply:manage', $context);
    $pageheading = get_string('confirmusers', 'enrol_apply');
} else {
    $instance = $DB->get_record('enrol', array('id'=>$id, 'enrol'=>'apply'), '*', MUST_EXIST);
    require_course_login($instance->courseid);
    $course = get_course($instance->courseid);
    $context = context_course::instance($course->id, MUST_EXIST);
    require_capability('moodle/course:enrolreview', $context);
    $manageurlparams['id'] = $instance->id;
    $pageheading = $course->fullname;
}

$manageurl = new moodle_url('/enrol/apply/manage.php', $manageurlparams);

$PAGE->set_context($context);
$PAGE->set_url($manageurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($pageheading);
$PAGE->navbar->add(get_string('confirmusers', 'enrol_apply'));
$PAGE->set_title(get_string('confirmusers', 'enrol_apply'));
$PAGE->requires->css('/enrol/apply/style.css');

if ($userenrolments != null) {
    $action = required_param('type', PARAM_TEXT);
    switch ($action) {
        case 'confirm':
            confirmEnrolment($userenrolments);
            break;
        case 'wait':
            waitEnrolment ($userenrolments);
            break;
        case 'cancel':
            cancelEnrolment($userenrolments);
            break;
        default:
            # code...
            break;
    }
    redirect($manageurl);
}

echo $OUTPUT->header ();
echo $OUTPUT->heading ( get_string ( 'confirmusers', 'enrol_apply' ) );
echo get_string('confirmusers_desc', 'enrol_apply');

$table = new enrol_apply_manage_table($id);
$table->define_baseurl($manageurl);
$columns = array(
    'checkboxcolumn',
    'course',
    'fullname', // Magic happens here: The column heading will automatically be set.
    'email',
    'applydate',
    'applycomment');
$headers = array(
    '',
    get_string('course'),
    'fullname', // Magic happens here: The column heading will automatically be set due to column name 'fullname'.
    get_string('email'),
    get_string('applydate', 'enrol_apply'),
    get_string('comment', 'enrol_apply'));
$table->define_columns($columns);
$table->define_headers($headers);

$table->sortable(true, 'id');


echo html_writer::start_tag('form', array('id' => 'enrol_apply_manage_form', 'method' => 'post', 'action' => $manageurl->out()));
echo html_writer::empty_tag('input', array('type' => 'hidden', 'id' => 'type', 'name' => 'type', 'value' => 'confirm'));

$table->out(50, true);

echo html_writer::start_tag('p', array('align' => 'center'));
echo html_writer::empty_tag('input', array(
    'type' => 'button',
    'onclick' => 'doSubmit("confirm");',
    'value' => get_string('btnconfirm', 'enrol_apply')));
echo html_writer::empty_tag('input', array(
    'type' => 'button',
    'onclick' => 'doSubmit("wait");',
    'value' => get_string('btnwait', 'enrol_apply')));
echo html_writer::empty_tag('input', array(
    'type' => 'button',
    'onclick' => 'doSubmit("cancel");',
    'value' => get_string('btncancel', 'enrol_apply')));
echo html_writer::end_tag('p');
echo html_writer::end_tag('form');

$js = "
    function doSubmit(type){
        document.getElementById('type').value=type;
        document.getElementById('enrol_apply_manage_form').submit();
    }";
echo html_writer::tag('script', $js, array('type' => 'text/javascript'));

echo $OUTPUT->footer ();
