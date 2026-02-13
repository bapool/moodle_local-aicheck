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
 * Checker class for AI Check
 *
 * @package    local_aicheck
 * @copyright  2026 Brian A. Pool, National Trail Local Schools
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aicheck;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/filelib.php');

/**
 * Main checker class that processes student submissions
 */
class checker {
    
    /** @var \assign The assignment object */
    private $assignment;
    
    /** @var \context_module The module context */
    private $context;
    
    /** @var int The user ID */
    private $userid;
    
    /**
     * Constructor
     *
     * @param \assign $assignment Assignment object
     * @param \context_module $context Module context
     * @param int $userid User ID
     */
    public function __construct($assignment, $context, $userid) {
        $this->assignment = $assignment;
        $this->context = $context;
        $this->userid = $userid;
    }
    
    /**
     * Check a student's submission and return feedback
     *
     * @return array Result array with success and feedback or error
     */
    public function check_submission() {
        global $USER, $DB;
        
        try {
            // Get the student's submission
            $submission = $this->assignment->get_user_submission($this->userid, false);
            
            if (!$submission) {
                return [
                    'success' => false,
                    'error' => get_string('error_no_submission', 'local_aicheck'),
                ];
            }
            
            // Get submission text
            $submissiontext = $this->get_submission_text($submission);
            
            if (empty($submissiontext)) {
                return [
                    'success' => false,
                    'error' => get_string('error_no_content', 'local_aicheck'),
                ];
            }
            
            // Detect grade level from username
            $username = $DB->get_field('user', 'username', ['id' => $this->userid]);
            $gradelevel = grade_detector::detect_grade_level($username);
            
            // Get assignment details
            $assignmentname = $this->assignment->get_instance()->name;
            $assignmentdesc = $this->assignment->get_instance()->intro;
            
            // Get rubric files if any
            $rubrictext = $this->get_rubric_text();
            
            // Build AI prompt
            $prompt = $this->build_prompt($assignmentname, $assignmentdesc, $rubrictext, $submissiontext, $gradelevel);
            
            // Call AI service
            $feedback = $this->call_ai_service($prompt);
            
            return [
                'success' => true,
                'feedback' => $feedback,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => get_string('error_processing', 'local_aicheck', $e->getMessage()),
            ];
        }
    }
    
    /**
     * Get submission text from various submission types
     *
     * @param object $submission Submission object
     * @return string Submission text
     */
    private function get_submission_text($submission) {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');
        
        $text = '';
        
        // Get online text submission
        $plugin = $this->assignment->get_submission_plugin_by_type('onlinetext');
        if ($plugin && $plugin->is_enabled()) {
            $submissiondata = $plugin->get_editor_text('onlinetext', $submission->id);
            if ($submissiondata) {
                $text .= strip_tags($submissiondata) . "\n\n";
            }
        }
        
        // Get file submissions
        $plugin = $this->assignment->get_submission_plugin_by_type('file');
        if ($plugin && $plugin->is_enabled()) {
            $fs = get_file_storage();
            $files = $fs->get_area_files(
                $this->context->id,
                'assignsubmission_file',
                'submission_files',
                $submission->id,
                'filename',
                false
            );
            
            foreach ($files as $file) {
                if ($file->get_filename() != '.') {
                    $filetext = $this->extract_text_from_file($file);
                    if ($filetext) {
                        $text .= "=== " . $file->get_filename() . " ===\n" . $filetext . "\n\n";
                    }
                }
            }
        }
        
        // TODO: Add Google Docs link support here if needed
        // This would require parsing the submission for Google Docs URLs
        // and using Google's export API
        
        return trim($text);
    }
    
    /**
     * Extract text from uploaded file
     *
     * @param \stored_file $file File object
     * @return string Extracted text
     */
    private function extract_text_from_file($file) {
        $filename = $file->get_filename();
        $mimetype = $file->get_mimetype();
        
        // Plain text files
        if ($mimetype == 'text/plain') {
            return $file->get_content();
        }
        
        // PDF files
        if ($mimetype == 'application/pdf') {
            return $this->extract_pdf_text($file);
        }
        
        // DOCX files
        if ($mimetype == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            return $this->extract_docx_text($file);
        }
        
        // DOC files (older Word format)
        if ($mimetype == 'application/msword') {
            // DOC extraction is complex, return a note for now
            return "[Microsoft Word document - please convert to DOCX or PDF for AI Check]";
        }
        
        // For other file types, return a note
        return "[File type not supported: {$filename}]";
    }
    
    /**
     * Extract text from PDF file
     *
     * @param \stored_file $file File object
     * @return string Extracted text
     */
    private function extract_pdf_text($file) {
        // Try using pdftotext if available
        $content = $file->get_content();
        $tempfile = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tempfile, $content);
        
        $output = '';
        $command = "pdftotext " . escapeshellarg($tempfile) . " -";
        exec($command, $outputlines, $returnvar);
        
        if ($returnvar === 0) {
            $output = implode("\n", $outputlines);
        }
        
        unlink($tempfile);
        
        if (!empty($output)) {
            return $output;
        }
        
        return "[PDF file - text extraction not available on this server]";
    }
    
    /**
     * Extract text from DOCX file
     *
     * @param \stored_file $file File object
     * @return string Extracted text
     */
    private function extract_docx_text($file) {
        $content = $file->get_content();
        $tempfile = tempnam(sys_get_temp_dir(), 'docx');
        file_put_contents($tempfile, $content);
        
        $text = '';
        
        try {
            $zip = new \ZipArchive();
            if ($zip->open($tempfile) === true) {
                $xml = $zip->getFromName('word/document.xml');
                if ($xml) {
                    // Remove XML tags to get plain text
                    $text = strip_tags($xml);
                    // Clean up extra whitespace
                    $text = preg_replace('/\s+/', ' ', $text);
                }
                $zip->close();
            }
        } catch (\Exception $e) {
            $text = "[Error extracting DOCX content]";
        }
        
        unlink($tempfile);
        
        return trim($text);
    }
    
    /**
     * Get rubric text from assignment files
     *
     * @return string Rubric text or empty string
     */
    private function get_rubric_text() {
        $fs = get_file_storage();
        $files = $fs->get_area_files(
            $this->context->id,
            'mod_assign',
            'intro',
            0,
            'filename',
            false
        );
        
        $rubrictext = '';
        foreach ($files as $file) {
            if ($file->get_filename() != '.') {
                $rubrictext .= $this->extract_text_from_file($file) . "\n\n";
            }
        }
        
        return trim($rubrictext);
    }
    
    /**
     * Build AI prompt
     *
     * @param string $assignmentname Assignment name
     * @param string $assignmentdesc Assignment description
     * @param string $rubrictext Rubric text
     * @param string $submissiontext Student submission
     * @param int $gradelevel Grade level
     * @return string Complete prompt
     */
    private function build_prompt($assignmentname, $assignmentdesc, $rubrictext, $submissiontext, $gradelevel) {
        $instructions = get_config('local_aicheck', 'ai_instructions');
        if (empty($instructions)) {
            $instructions = get_string('default_instructions', 'local_aicheck');
        }
        
        $gradename = grade_detector::get_grade_name($gradelevel);
        
        $prompt = $instructions . "\n\n";
        $prompt .= "STUDENT GRADE LEVEL: {$gradename}\n\n";
        $prompt .= "ASSIGNMENT: {$assignmentname}\n\n";
        $prompt .= "ASSIGNMENT DESCRIPTION:\n{$assignmentdesc}\n\n";
        
        if (!empty($rubrictext)) {
            $prompt .= "RUBRIC/GUIDELINES:\n{$rubrictext}\n\n";
        }
        
        $prompt .= "STUDENT SUBMISSION:\n{$submissiontext}\n\n";
        $prompt .= "Please provide feedback for this student:";
        
        return $prompt;
    }
    
    /**
     * Call AI service to get feedback
     *
     * @param string $prompt The prompt to send
     * @return string AI feedback
     */
    private function call_ai_service($prompt) {
        global $USER;
        
        try {
            // Create AI action using Moodle's core AI system
            $action = new \core_ai\aiactions\generate_text(
                contextid: $this->context->id,
                userid: $USER->id,
                prompttext: $prompt
            );
            
            // Get AI manager and process the action
            $manager = \core\di::get(\core_ai\manager::class);
            $response = $manager->process_action($action);
            
            if ($response->get_success()) {
                return $response->get_response_data()['generatedcontent'] ?? '';
            } else {
                throw new \Exception($response->get_errormessage() ?: 'AI generation failed');
            }
            
        } catch (\Exception $e) {
            throw new \moodle_exception('error_ai_service', 'local_aicheck', '', $e->getMessage());
        }
    }
}
