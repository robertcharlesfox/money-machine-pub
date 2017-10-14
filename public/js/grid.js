(function($) {
    $(document).ready(function() {

        /**
         * Configuration
         * @returns {{questionEmpty: string, questionCorrect: string, questionWrong: string, questionSkipped: string}}
         */
        function gridValues() {
            return {
                questionEmpty: '',
                questionCorrect: '1',
                questionWrong: '0',
                questionSkipped: '-1'
            }
        }

        /***********************************************************************
         * Bindings.
         **********************************************************************/

        /**
         * Gateway to our JS.
         * Called when user clicks on a result to update it.
         */
        $('.question-result-display').click(function() {
            var inputField = $(this).prev();
            updateResult(inputField);
            updateStatistics(inputField);
        }).each(function () {
            var inputField = $(this).prev();
            initResult(inputField);
            updateStatistics(inputField);
        });

        /**
         * Click handler for Save button.
         */
        $('#save-grid').click(function(e) {
            var $saveButton = $(this);

            e.preventDefault();

            var results = prepareResults();

            var onSuccess = function () {
                window.location = $saveButton.data('classroom-uri');
            };

            var onFailure = function () {
                alert("Changes have not been saved; are you sure you are online?");
            };

            saveResults(
                $saveButton.data('save-uri'),
                $saveButton.data('csrf-token'),
                results,
                onSuccess,
                onFailure
            );
        });

        /***********************************************************************
         * Helpers.
         **********************************************************************/

        /**
         * Ajax call to save prepared results.
         * @param uri
         * @param results
         * @param onSuccess
         * @param onFailure
         */
        function saveResults(uri, csrfToken, results, onSuccess, onFailure) {
            $.ajax({
                url: uri,
                type: "POST",
                data: JSON.stringify(results),
                contentType: "application/json",
                headers: {
                    "X-CSRF-TOKEN": csrfToken
                },
                success: onSuccess,
                error: onFailure
            });
        }

        /**
         * Gathers the statistics for a user and displays them.
         * @param inputField jQuery object.
         */
        function updateStatistics(inputField) {
            var studentID = getStudentID(inputField);
            var studentAverage = getStudentAverage(studentID);
            setStudentAverage(studentID, studentAverage);
        }

        /**
         * Determines the current state and
         * updates the icon displayed.
         * @param inputField jQuery object.
         */
        function initResult(inputField) {
            // Magic numbers (stored in result fields).
            var values = gridValues();

            // Switch on the value when initialized.
            switch (inputField.val()) {
                case values.questionEmpty:
                    inputField.next().addClass('result-fsg-empty');
                    break;
                case values.questionCorrect:
                    inputField.next().addClass('result-fsg-correct');
                    break;
                case values.questionWrong:
                    inputField.next().addClass('result-fsg-wrong');
                    break;
                case values.questionSkipped:
                    inputField.next().addClass('result-fsg-skipped');
                    break;
            }
        }

        /**
         * Determines the next state and
         * updates the value in the input field and
         * updates the icon displayed.
         * @param inputField jQuery object.
         */
        function updateResult(inputField) {

            // Magic numbers (stored in result fields).
            var values = gridValues();

            // Switch on the value when clicked.
            switch (inputField.val()) {
                // Empty ==> Correct.
                case values.questionEmpty:
                    inputField.val(values.questionCorrect)
                        .next().removeClass('result-fsg-empty').addClass('result-fsg-correct');
                    break;
                // Correct ==> Wrong.
                case values.questionCorrect:
                    inputField.val(values.questionWrong)
                        .next().removeClass('result-fsg-correct').addClass('result-fsg-wrong');
                    break;
                // Wrong ==> Skipped.
                case values.questionWrong:
                    inputField.val(values.questionSkipped)
                        .next().removeClass('result-fsg-wrong').addClass('result-fsg-skipped');
                    break;
                // Skipped ==> Empty.
                case values.questionSkipped:
                    inputField.val(values.questionEmpty)
                        .next().removeClass('result-fsg-skipped').addClass('result-fsg-empty');
                    break;
            }
        }

        /**
         * Gets the student's ID from the name of an input field.
         * @param inputField a jQuery object representing the result input field.
         * @returns int, the ID of the student stored in the DB.
         */
        function getStudentID(inputField) {
            return getInputIDs(inputField)[0];
        }

        /**
         * Gets the question's ID from the name of an input field.
         * @param inputField a jQuery object representing the result input field.
         * @returns int, the ID of the question stored in the DB.
         */
        function getQuestionID(inputField) {
            return getInputIDs(inputField)[1];
        }

        /**
         * Grabs the student and question ID from an input field's name.
         * Assumes a name of "student-{id}-question-{id}".
         * @param inputField
         * @returns {Array}
         */
        function getInputIDs(inputField) {
            return inputField.attr('name') // student-{id}-question-{id}
                .replace('student-', '').replace('question-', '') // {student_id}-{question_id}
                .split('-'); // [student_id, question_id]
        }

        /**
         * Returns the average score for this student.
         * @param studentID - The ID for the student, stored in the database.
         * @returns Either the percentage for this student, an int between 0 and
         * 100, or an empty string if there are no results.
         */
        function getStudentAverage(studentID) {

            // Magic numbers (stored in result fields).
            var values = gridValues();

            // Loop through results for this student, counting correct/wrong.
            // Only count if correct (1) or wrong (2); ignore if skipped or empty.
            var correct = 0;
            var wrong = 0;
            $('.student-' + studentID).each(function (index) {
                switch($(this).val()) {
                    case values.questionCorrect:
                        correct++;
                        break;
                    case values.questionWrong:
                        wrong++;
                        break;
                }
            });

            // If we don't have any results, we can't get an average.
            var resultsCounted = correct + wrong;
            if (resultsCounted > 0) {
                return Math.round((correct / resultsCounted) * 100);
            }
            return '';
        }

        /**
         * Walks through the result inputs and builds an array of arrays.
         * @returns {Array} [[student-id, question-id, result], [student-id, question-id, result], ...]
         */
        function prepareResults() {
            var results = [];
            $('.question-result').each(function (index) {
                var studentID = getStudentID($(this));
                var questionID = getQuestionID($(this));
                var result = $(this).val();
                results.push([studentID, questionID, result]);
            })

            return results;
        }

        /**
         * Updates the HTML with the new student average.
         * @param studentID An int, stored in students::id in db.
         * @param studentAverage -- Either empty string, or int 0-100.
         */
        function setStudentAverage(studentID, studentAverage) {
            // studentAverage is an empty string if no questions were answered.
            // We don't want to display 0% if they didn't answer anything.
            // We do want 0% if the student got all the answers wrong.
            if (studentAverage !== '') {
                studentAverage = studentAverage + '%';
            }
            $('#student-avg-' + studentID).html(studentAverage);
            $('#student-avg-' + studentID).addClass("grid-changing").delay(1000).queue(function(next){
                $('#student-avg-' + studentID).removeClass("grid-changing");
                next();
            });
        }

    });
})(jQuery);
