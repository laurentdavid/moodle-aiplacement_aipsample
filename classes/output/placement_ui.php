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

namespace aiplacement_aipsample\output;

use aiplacement_aipsample\utils;
use core\hook\output\after_http_headers;

/**
 * Output handler for the course assist AI Placement.
 *
 * @package    aiplacement_aipsample
 * @copyright  2024 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class placement_ui {
    /**
     * Bootstrap the UI.
     *
     * @return void
     */
    public static function set_placement_ui(after_http_headers $hook): void {
        global $OUTPUT, $USER;
        $context = $hook->renderer->get_page()->context;
        // Preflight checks.
        if (!self::preflight_checks()) {
            return;
        }
        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }
        $params = [
            'userid' => $USER->id,
            'contextid' => $context->id,
            'title' => get_string('pluginname', 'aiplacement_aipsample'),
        ];

        $html = $OUTPUT->render_from_template('aiplacement_aipsample/placement_ui', $params);
        $hook->add_html($html);
    }

    /**
     * Preflight checks to determine if the assist UI should be loaded.
     *
     * @return bool
     */
    private static function preflight_checks(): bool {
        global $PAGE;
        if (during_initial_install()) {
            return false;
        }
        if (!get_config('aiplacement_aipsample', 'version')) {
            return false;
        }
        if (in_array($PAGE->pagelayout, ['maintenance', 'print', 'redirect', 'embedded'])) {
            // Do not try to show assist UI inside iframe, in maintenance mode,
            // when printing, or during redirects.
            return false;
        }
        // Check we are in the right context, exit if not activity.
        if ($PAGE->context->contextlevel != CONTEXT_MODULE) {
            return false;
        }

        // Check if the user has permission to use the AI service.
        return utils::is_available($PAGE->context);
    }
}
