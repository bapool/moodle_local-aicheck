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
 * English language strings for local_aicheck
 *
 * @package    local_aicheck
 * @copyright  2026 Brian A. Pool, National Trail Local Schools
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'AI Check';
$string['aicheck'] = 'AI Check';
$string['aicheck:use'] = 'Use AI Check to get feedback on submissions';

// Button text
$string['button_check'] = '{$a} Check';
$string['checking'] = 'Checking your work...';

// Feedback popup
$string['feedback_title'] = 'AI Feedback';
$string['feedback_close'] = 'Close';

// Settings
$string['ai_name'] = 'AI assistant name';
$string['ai_name_desc'] = 'Customize the name of the AI assistant (e.g., "Boone", "Helper", "Study Buddy"). This name will appear on the check button. Default is "AI".';
$string['ai_instructions'] = 'AI instructions';
$string['ai_instructions_desc'] = 'Default instructions for the AI when checking student submissions. The AI will provide feedback but not assign grades.';

// Error messages
$string['error_no_submission'] = 'You must submit your work before you can check it.';
$string['error_no_content'] = 'Your submission appears to be empty. Please add content before checking.';
$string['error_processing'] = 'Error processing your submission: {$a}';
$string['error_ai_service'] = 'AI service error: {$a}';

// Default instructions
$string['default_instructions'] = 'You are helping a student check their assignment before final submission. Provide honest, constructive feedback. Do not exaggerate or over-praise - be truthful about the quality of their work.

IMPORTANT:
- Be honest and constructive, not falsely encouraging
- If they did something well, mention it briefly
- If they missed requirements or did poor work, say so clearly but kindly
- Give 2-3 specific, actionable suggestions for improvement
- Use age-appropriate language for their grade level
- Keep feedback under 150 words
- Address the student directly using "you"
- Do NOT provide a grade or score

FORMAT YOUR RESPONSE:
[If applicable: 1-2 sentences about what worked well]
[If needed: 1-2 sentences about major problems or missing requirements]

SUGGESTIONS FOR IMPROVEMENT:
1. [First specific, actionable suggestion]
2. [Second specific, actionable suggestion]
3. [Third suggestion if needed]

CRITICAL: If the student failed to follow specific assignment directions, you MUST point this out clearly. Be supportive but truthful - students need honest feedback to improve.';

// Privacy
$string['privacy:metadata:ai_provider'] = 'Student submission content is sent to the configured AI provider for feedback analysis. This data is processed by the external AI service but not permanently stored by this plugin.';
$string['privacy:metadata:ai_provider:submissiontext'] = 'The text content of the student\'s submission that is sent to the AI provider for feedback.';
$string['privacy:metadata:ai_provider:assignmentname'] = 'The name of the assignment, sent to provide context to the AI system.';
$string['privacy:metadata:ai_provider:assignmentinstructions'] = 'The assignment instructions, sent to help the AI understand what to check.';
$string['privacy:metadata:ai_provider:rubric'] = 'The grading rubric (if uploaded), sent to guide the AI\'s feedback.';
$string['privacy:metadata:ai_provider:gradelevel'] = 'The student grade level (detected from username), sent to help the AI adjust feedback appropriateness.';
