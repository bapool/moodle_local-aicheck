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
 * AMD module for AI Check button
 *
 * @module     local_aicheck/check_button
 * @copyright  2026 Brian A. Pool, National Trail Local Schools
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/str', 'core/notification'], function($, Ajax, Str, Notification) {

    return {
        /**
         * Initialize the check button
         * @param {int} cmid The course module ID
         * @param {string} buttonText The text to display on the button
         */
        init: function(cmid, buttonText) {

            var insertButton = function() {
                // Don't insert if button already exists
                if ($('.aicheck-button-injected').length > 0) {
                    return true;
                }

                // Create the button
                var button = $('<button>')
                    .attr('type', 'button')
                    .addClass('btn btn-secondary aicheck-button-injected')
                    .css('margin-left', '10px')
                    .text(buttonText)
                    .on('click', function() {
                        var btn = $(this);
                        var originalText = btn.text();

                        Str.get_strings([
                            {key: 'checking', component: 'local_aicheck'},
                            {key: 'feedback_title', component: 'local_aicheck'},
                            {key: 'feedback_close', component: 'local_aicheck'},
                            {key: 'error_processing', component: 'local_aicheck'}
                        ]).done(function(strings) {
                            btn.prop('disabled', true).text(strings[0]);

                            Ajax.call([{
                                methodname: 'local_aicheck_check_submission',
                                args: {
                                    cmid: cmid
                                }
                            }])[0].done(function(response) {
                                if (response.success) {
                                    // Show feedback in modal
                                    showFeedbackModal(strings[1], response.feedback, strings[2]);
                                } else {
                                    var errorMsg = response.error || 'Unknown error';
                                    Notification.alert(strings[1], errorMsg, strings[2]);
                                }
                                btn.prop('disabled', false).text(originalText);
                            }).fail(function(error) {
                                var errorMsg = error.message || 'Unknown error';
                                Notification.alert(strings[1], strings[3].replace('{$a}', errorMsg), strings[2]);
                                btn.prop('disabled', false).text(originalText);
                            });
                        }).fail(function() {
                            // Fallback if string fetch fails
                            btn.prop('disabled', true).text('Checking...');

                            Ajax.call([{
                                methodname: 'local_aicheck_check_submission',
                                args: {
                                    cmid: cmid
                                }
                            }])[0].done(function(response) {
                                if (response.success) {
                                    showFeedbackModal('AI Feedback', response.feedback, 'Close');
                                } else {
                                    alert('Error: ' + (response.error || 'Unknown error'));
                                }
                                btn.prop('disabled', false).text(originalText);
                            }).fail(function(error) {
                                alert('Error: ' + (error.message || 'Unknown error'));
                                btn.prop('disabled', false).text(originalText);
                            });
                        });
                    });

                // Try to insert button after the submit button
                var inserted = false;

                // Option 1: After submit button in submission form
                var submitButton = $('input[type="submit"][name="submitbutton"]');
                if (submitButton.length) {
                    submitButton.after(button);
                    inserted = true;
                }

                // Option 2: In the submission status area
                if (!inserted) {
                    var statusTable = $('.submissionstatustable');
                    if (statusTable.length) {
                        statusTable.after($('<div>').addClass('aicheck-button-container').css({
                            'margin-top': '10px',
                            'text-align': 'center'
                        }).append(button));
                        inserted = true;
                    }
                }

                // Option 3: At the top of the page content
                if (!inserted) {
                    var pageContent = $('#region-main').first();
                    if (pageContent.length) {
                        pageContent.prepend($('<div>').addClass('alert alert-info').css('margin', '15px').append(button));
                        inserted = true;
                    }
                }

                return inserted;
            };

            /**
             * Show feedback in a modal dialog
             * @param {string} title Modal title
             * @param {string} feedback Feedback text
             * @param {string} closeText Close button text
             */
            var showFeedbackModal = function(title, feedback, closeText) {
                // Create modal HTML
                var modal = $('<div>')
                    .addClass('modal fade')
                    .attr('tabindex', '-1')
                    .attr('role', 'dialog');

                var modalDialog = $('<div>')
                    .addClass('modal-dialog modal-lg')
                    .attr('role', 'document');

                var modalContent = $('<div>').addClass('modal-content');

                var modalHeader = $('<div>').addClass('modal-header');
                modalHeader.append($('<h5>').addClass('modal-title').text(title));
                modalHeader.append($('<button>')
                    .addClass('close')
                    .attr('type', 'button')
                    .attr('data-dismiss', 'modal')
                    .attr('aria-label', closeText)
                    .html('&times;'));

                var modalBody = $('<div>')
                    .addClass('modal-body')
                    .css({
                        'white-space': 'pre-wrap',
                        'font-size': '14px',
                        'line-height': '1.6'
                    })
                    .text(feedback);

                var modalFooter = $('<div>').addClass('modal-footer');
                modalFooter.append($('<button>')
                    .addClass('btn btn-primary')
                    .attr('type', 'button')
                    .attr('data-dismiss', 'modal')
                    .text(closeText));

                modalContent.append(modalHeader, modalBody, modalFooter);
                modalDialog.append(modalContent);
                modal.append(modalDialog);

                $('body').append(modal);
                modal.modal('show');

                // Remove modal from DOM after it's hidden
                modal.on('hidden.bs.modal', function() {
                    modal.remove();
                });
            };

            // Wait for DOM to be ready, then try to insert
            $(document).ready(function() {
                // Try immediately
                var inserted = insertButton();

                // If not inserted, set up interval checking
                if (!inserted) {
                    var checkCount = 0;
                    var maxChecks = 10;

                    var insertInterval = setInterval(function() {
                        checkCount++;
                        if (insertButton() || checkCount >= maxChecks) {
                            clearInterval(insertInterval);
                        }
                    }, 500);
                }
            });

            // Also try on window load as backup
            $(window).on('load', function() {
                insertButton();
            });
        }
    };
});
