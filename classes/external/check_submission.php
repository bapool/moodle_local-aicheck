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
 * External service for checking submissions
 *
 * @package    local_aicheck
 * @copyright  2026 Brian A. Pool, National Trail Local Schools
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aicheck\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use context_module;
use assign;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

/**
 * External service for checking student submissions
 */
class check_submission extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
        ]);
    }

    /**
     * Check student's submission and return feedback
     *
     * @param int $cmid Course module ID
     * @return array Result with success status and feedback or error message
     */
    public static function execute($cmid) {
        global $DB, $USER;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
        ]);

        // Get course module and verify it exists.
        $cm = get_coursemodule_from_id('assign', $params['cmid'], 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $context = context_module::instance($cm->id);

        // Validate context.
        self::validate_context($context);

        // Check capability - students can only check their own work.
        require_capability('local/aicheck:use', $context);

        // Create assignment object.
        $assignment = new assign($context, $cm, $course);

        // Perform the check - students can only check their own submissions.
        $checker = new \local_aicheck\checker($assignment, $context, $USER->id);
        $result = $checker->check_submission();

        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the operation was successful'),
            'feedback' => new external_value(PARAM_RAW, 'AI feedback text', VALUE_OPTIONAL),
            'error' => new external_value(PARAM_TEXT, 'Error message if any', VALUE_OPTIONAL),
        ]);
    }
}
