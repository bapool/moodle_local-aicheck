<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library functions for local_aicheck
 *
 * @package    local_aicheck
 * @copyright  2026 Brian A. Pool, National Trail Local Schools
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Hook to inject AI Check button into assignment submission pages
 *
 * @return string Empty string (JavaScript loaded via AMD)
 */
function local_aicheck_before_footer() {
    global $PAGE, $USER, $DB;
    
    // Only on assignment view pages
    if ($PAGE->pagetype !== 'mod-assign-view') {
        return '';
    }
    
    // Get course module
    $cm = $PAGE->cm;
    if (!$cm || $cm->modname !== 'assign') {
        return '';
    }
    
    // Check capability - only students with the use capability
    $context = context_module::instance($cm->id);
    if (!has_capability('local/aicheck:use', $context)) {
        return '';
    }
    
    // Make sure user is a student (not a teacher viewing as student)
    if (has_capability('mod/assign:grade', $context)) {
        return '';
    }
    
    // Check if assignment is already graded
    require_once($PAGE->theme->dir . '/../../mod/assign/locallib.php');
    $assignment = new assign($context, $cm, $PAGE->course);
    
    // Get user's grade
    $grade = $assignment->get_user_grade($USER->id, false);
    
    // If already graded, don't show button
    if ($grade && $grade->grade !== null && $grade->grade >= 0) {
        return '';
    }
    
    // Get user's submission (draft or submitted)
    $submission = $assignment->get_user_submission($USER->id, true); // true = create if doesn't exist
    
    // Only show if there's actually some content
    // We'll let the checker handle the "no content" error if they click with empty draft
    
    // Get custom AI name
    $ai_name = get_config('local_aicheck', 'ai_name');
    if (empty($ai_name)) {
        $ai_name = 'AI';
    }
    
    $button_text = get_string('button_check', 'local_aicheck', $ai_name);
    
    // Load the AMD module
    $PAGE->requires->js_call_amd('local_aicheck/check_button', 'init', [
        $cm->id,
        $button_text
    ]);
    
    return '';
}
