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
require_once($CFG->dirroot.'/enrol/apply/renderer.php');

$id = optional_param('id', null, PARAM_INT);
$userenrolments = optional_param_array('userenrolments', null, PARAM_INT);

require_login();

$manageurlparams = array();
if ($id == null) {
    $context = context_system::instance();
    require_capability('enrol/apply:manageapplications', $context);
    $pageheading = get_string('confirmusers', 'enrol_apply');
} else {
    $instance = $DB->get_record('enrol', array('id'=>$id, 'enrol'=>'apply'), '*', MUST_EXIST);
    require_course_login($instance->courseid);
    $course = get_course($instance->courseid);
    $context = context_course::instance($course->id, MUST_EXIST);
    require_capability('enrol/apply:manageapplications', $context);
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
    $enrolapply = enrol_get_plugin('apply');
    if (optional_param('confirm', false, PARAM_BOOL)) {
        $enrolapply->confirmEnrolment($userenrolments);
    } else if (optional_param('wait', false, PARAM_BOOL)) {
        $enrolapply->waitEnrolment($userenrolments);
    } else if (optional_param('cancel', false, PARAM_BOOL)) {
        $enrolapply->cancelEnrolment($userenrolments);
    }
    redirect($manageurl);
}

$table = new enrol_apply_manage_table($id);
$table->define_baseurl($manageurl);

$renderer = $PAGE->get_renderer('enrol_apply');
$renderer->manage_page($table, $manageurl);
