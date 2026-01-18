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
 * Settings for local_aicheck
 *
 * @package    local_aicheck
 * @copyright  2026 Brian A. Pool, National Trail Local Schools
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_aicheck', get_string('pluginname', 'local_aicheck'));
    
    // AI Assistant Name
    $settings->add(new admin_setting_configtext(
        'local_aicheck/ai_name',
        get_string('ai_name', 'local_aicheck'),
        get_string('ai_name_desc', 'local_aicheck'),
        'AI',
        PARAM_TEXT
    ));
    
    // AI Instructions
    $settings->add(new admin_setting_configtextarea(
        'local_aicheck/ai_instructions',
        get_string('ai_instructions', 'local_aicheck'),
        get_string('ai_instructions_desc', 'local_aicheck'),
        get_string('default_instructions', 'local_aicheck'),
        PARAM_TEXT
    ));
    
    $ADMIN->add('localplugins', $settings);
}
