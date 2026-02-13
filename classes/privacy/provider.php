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
 * Privacy provider for local_aicheck
 *
 * @package    local_aicheck
 * @copyright  2026 Brian A. Pool, National Trail Local Schools
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aicheck\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider implementation for AI Check
 */
class provider implements
    \core_privacy\local\metadata\provider {

    /**
     * Returns metadata about this plugin's privacy policy
     *
     * @param collection $collection The collection to add metadata to
     * @return collection The updated collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link(
            'ai_provider',
            [
                'submissiontext' => 'privacy:metadata:ai_provider:submissiontext',
                'assignmentname' => 'privacy:metadata:ai_provider:assignmentname',
                'assignmentinstructions' => 'privacy:metadata:ai_provider:assignmentinstructions',
                'rubric' => 'privacy:metadata:ai_provider:rubric',
                'gradelevel' => 'privacy:metadata:ai_provider:gradelevel',
            ],
            'privacy:metadata:ai_provider'
        );

        return $collection;
    }
}
