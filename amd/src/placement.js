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
import Templates from 'core/templates';
import Ajax from 'core/ajax';
import AIHelper from 'core_ai/helper';
import {getString} from 'core/str';
import Notification from 'core/notification';
/**
 * Module to load and render the tools for the placement.
 *
 * Note that we completely ignore user agreement and privacy policy here
 * for simplicity but it should be a consideration in a real implementation.
 *
 * @module     aiplacement_aipsample/placement
 * @copyright  2024 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


const Selectors = {
    ELEMENTS: {
        AIRESPONSE_AREA: '.aipsample-placement-ui [data-region="response"]',
    },
    ACTIONS: {
        RUN: '.aipsample-placement-ui [data-action="run"]',
    }
};

/**
 * Placement class.
 */
const Placement = class {
    /**
     * Constructor.
     * @param {Integer} userId The user ID.
     * @param {Integer} contextId The context ID.
     */
    constructor(userId, contextId) {
        this.userId = userId;
        this.contextId = contextId;
        this.aiResponseArea = document.querySelector(Selectors.ELEMENTS.AIRESPONSE_AREA);
        this.registerEventListeners();
    }
    /**
     * Register event listeners.
     */
    registerEventListeners() {
        document.querySelectorAll(Selectors.ACTIONS.RUN).forEach(
            (el) => el.addEventListener('click', async(e) => {
                const runAction = e.target.closest(Selectors.ACTIONS.RUN);
                if (runAction) {
                    e.preventDefault();
                    this.displayLoading();
                    // Clear the drawer content to prevent sending some unnecessary content.
                    this.aiResponseArea.innerHTML = '';
                    const request = {
                        methodname: 'aiplacement_aipsample_generate_content',
                        args: {
                            userid: this.userId,
                            contextid: this.contextId,
                            prompttext: await getString('prompttext', 'aiplacement_aipsample'),
                        }
                    };
                    try {
                        const responseObj = await Ajax.call([request])[0];
                        if (responseObj.error) {
                            this.displayError();
                            return;
                        } else {
                            const generatedContent = AIHelper.replaceLineBreaks(responseObj.generatedcontent);
                            this.displayResponse(generatedContent);
                        }
                    } catch (error) {
                        window.console.log(error);
                        this.displayError();
                    }
                }
            }));
    }
    /**
     * Display the loading spinner.
     */
    displayLoading() {
        Templates.render('aiplacement_aipsample/loading', {}).then((html) => {
            this.aiResponseArea.innerHTML = html;
            return;
        }).catch(Notification.exception);
    }

    /**
     * Display the response.
     * @param {String} content The content to display.
     */
    displayResponse(content) {
        Templates.render('aiplacement_aipsample/response', {content: content}).then((html) => {
            this.aiResponseArea.innerHTML = html;
            return;
        }).catch(Notification.exception);
    }

    /**
     * Display the error.
     */
    displayError() {
        Templates.render('aiplacement_aipsample/error', {}).then((html) => {
            this.aiResponseArea.innerHTML = html;
            return;
        }).catch(Notification.exception);
    }

};
export default Placement;