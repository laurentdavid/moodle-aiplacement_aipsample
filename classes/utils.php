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

namespace aiplacement_aipsample;

use context;
use core_ai\aiactions\generate_text;
use core_ai\manager;

/**
 * AI Placement course assist utils.
 *
 * @package    aiplacement_aipsample
 * @copyright  2024 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {
    /**
     * Check if AI Placement course assist is available for the module.
     *
     * @param \context $context The context.
     * @return bool True if AI Placement course assist is available, false otherwise.
     */
    public static function is_available(\context $context): bool {
        [$plugintype, $pluginname] = explode(
            '_',
            \core_component::normalize_componentname('aiplacement_aipsample'),
            2
        );
        $manager = \core_plugin_manager::resolve_plugininfo_class($plugintype);
        if (!$manager::is_plugin_enabled($pluginname)) {
            return false;
        }

        $providers = manager::get_providers_for_actions([generate_text::class], true);
        if (!has_capability('aiplacement/aipsample:generate_text', $context)
            || !manager::is_action_available(generate_text::class)
            || !manager::is_action_enabled('aiplacement_aipsample', generate_text::class)
            || empty($providers[generate_text::class])
        ) {
            return false;
        }

        return true;
    }

    /**
     * Get course module for context
     *
     * @param context $context
     * @return \stdClass|null
     * @throws \coding_exception
     */
    public static function get_course_module_from_context(\context $context) {
        $cm = get_coursemodule_from_id('', $context->instanceid, 0, false, MUST_EXIST);
        if (empty($cm)) {
            return null;
        }
        return $cm;
    }
}
