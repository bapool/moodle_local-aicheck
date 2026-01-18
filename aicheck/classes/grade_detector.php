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
 * Grade level detector for AI Check
 *
 * @package    local_aicheck
 * @copyright  2026 Brian A. Pool, National Trail Local Schools
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aicheck;

defined('MOODLE_INTERNAL') || die();

/**
 * Detects student grade level from username
 */
class grade_detector {
    
    /**
     * Detect grade level from username
     *
     * @param string $username The student's username
     * @return int Grade level (3-12), defaults to 9 if not detected
     */
    public static function detect_grade_level($username) {
        // Look for 2 or 4 digit year in username
        // Examples: jsmith25, student2025, john27
        
        // Try 4-digit year first (e.g., "student2025")
        if (preg_match('/(\d{4})/', $username, $matches)) {
            $year = (int)$matches[1];
            return self::graduation_year_to_grade($year);
        }
        
        // Try 2-digit year (e.g., "jsmith25" or "jsmith27")
        if (preg_match('/(\d{2})/', $username, $matches)) {
            $year = (int)$matches[1];
            // Convert 2-digit to 4-digit (assume 20xx)
            $fullyear = 2000 + $year;
            return self::graduation_year_to_grade($fullyear);
        }
        
        // Default to grade 9 if no year found
        return 9;
    }
    
    /**
     * Convert graduation year to current grade level
     *
     * @param int $gradyear Graduation year (4-digit)
     * @return int Grade level (3-12)
     */
    private static function graduation_year_to_grade($gradyear) {
        // Get current year and month
        $currentyear = (int)date('Y');
        $currentmonth = (int)date('n');
        
        // If we're past June (month 6), consider it the next school year
        if ($currentmonth > 6) {
            $currentyear++;
        }
        
        // Calculate years until graduation
        $yearsuntil = $gradyear - $currentyear;
        
        // Grade 12 graduates, so work backwards
        // If graduating this year (0 years) = grade 12
        // If graduating next year (1 year) = grade 11
        // etc.
        $grade = 12 - $yearsuntil;
        
        // Clamp to valid range (3-12)
        if ($grade < 3) {
            $grade = 3;
        }
        if ($grade > 12) {
            $grade = 12;
        }
        
        return $grade;
    }
    
    /**
     * Get grade level name (for display)
     *
     * @param int $grade Grade level (3-12)
     * @return string Grade level name
     */
    public static function get_grade_name($grade) {
        $names = [
            3 => 'Grade 3',
            4 => 'Grade 4',
            5 => 'Grade 5',
            6 => 'Grade 6',
            7 => 'Grade 7',
            8 => 'Grade 8',
            9 => 'Grade 9 (Freshman)',
            10 => 'Grade 10 (Sophomore)',
            11 => 'Grade 11 (Junior)',
            12 => 'Grade 12 (Senior)',
        ];
        
        return $names[$grade] ?? 'Grade 9';
    }
}
