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

namespace aiplacement_aipsample\external;

use aiplacement_aipsample\utils;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;

/**
 * External API to call summarise text action for this placement.
 *
 * @package    aiplacement_aipsample
 * @copyright  2024 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class generate_content extends external_api {

    /**
     * Generate content for the given action and the given module.
     *
     * @param int $userid The user ID.
     * @param int $contextid The context ID.
     * @param string $prompttext The prompt text.
     * @return array The generated content.
     * @since Moodle 4.5
     */
    public static function execute(
        int $userid,
        int $contextid,
        string $prompttext,
    ): array {
        // Parameter validation.
        [
            'userid' => $userid,
            'contextid' => $contextid,
            'prompttext' => $prompttext,
        ] = self::validate_parameters(self::execute_parameters(), [
            'userid' => $userid,
            'contextid' => $contextid,
            'prompttext' => $prompttext,
        ]);
        // Context validation and permission check.
        // Get the context from the passed in ID.
        $context = \context::instance_by_id($contextid);

        // Check the user has permission to use the AI service.
        self::validate_context($context);
        if (!utils::is_available($context)) {
            throw new \moodle_exception('noaipsample', 'aiplacement_aipsample');
        }
        if ($context->contextlevel !== CONTEXT_MODULE) {
            throw new \moodle_exception('invalidcontext', 'aiplacement_aipsample');
        }
        // Get the module instance.
        $modinfo = utils::get_course_module_from_context($context);

        if (empty($modinfo)) {
            throw new \moodle_exception('invalidmodule', 'aiplacement_aipsample');
        }
        $manager = new \core_ai\manager();
        // Prepare the action.
        $action = new \core_ai\aiactions\generate_text(
            contextid: $contextid,
            userid: $userid,
            prompttext: $prompttext . ' ' . $modinfo->name,
        );
        $response = $manager->process_action($action);
        // Return the response.
        return [
            'success' => $response->get_success(),
            'generatedcontent' => $response->get_response_data()['generatedcontent'] ?? '',
            'finishreason' => $response->get_response_data()['finishreason'] ?? '',
            'errorcode' => $response->get_errorcode(),
            'error' => $response->get_errormessage(),
            'timecreated' => $response->get_timecreated(),
        ];
    }

    /**
     * Generate content for the given action and the given module.
     *
     * @return external_function_parameters
     * @since Moodle 4.5
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'userid' => new external_value(
                PARAM_INT,
                'The userid ID',
                VALUE_REQUIRED,
            ),
            'contextid' => new external_value(
                PARAM_INT,
                'The context ID',
                VALUE_REQUIRED,
            ),
            'prompttext' => new external_value(
                PARAM_TEXT,
                'Prompt text for the query',
                VALUE_REQUIRED,
            ),
        ]);
    }

    /**
     * Generate content for the given action and the given module return value.
     *
     * @return external_function_parameters
     * @since Moodle 4.5
     */
    public static function execute_returns(): external_function_parameters {
        return new external_function_parameters([
            'success' => new external_value(
                PARAM_BOOL,
                'Was the request successful',
                VALUE_REQUIRED
            ),
            'timecreated' => new external_value(
                PARAM_INT,
                'The time the request was created',
                VALUE_REQUIRED,
            ),
            'generatedcontent' => new external_value(
                PARAM_RAW,
                'The text generated by AI.',
                VALUE_DEFAULT,
            ),
            'finishreason' => new external_value(
                PARAM_ALPHA,
                'The reason generation was stopped',
                VALUE_DEFAULT,
                'stop',
            ),
            'errorcode' => new external_value(
                PARAM_INT,
                'Error code if any',
                VALUE_DEFAULT,
                0,
            ),
            'error' => new external_value(
                PARAM_TEXT,
                'Error message if any',
                VALUE_DEFAULT,
                '',
            ),
        ]);
    }
}
