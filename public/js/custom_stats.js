(function($) {
    $(document).ready(function() {

        var customStat = {};

        /**
         * Example of a custom statistic. Simply sums the results.
         * @param results Array of answers, for example: [10, 9, 10, 8]
         * @returns {number}
         */
        customStat.sum = function (results) {
            results = results || [10, 10, 9, 10, 9, 10, 22];
            var stat = 0;
            for (var i = 0; i < results.length; i++) {
                 stat += results[i];
            }
            return stat;
        };

        /**
         * Example of a custom statistic. Average of correct answers.
         * @param results Array of answers, for example [10, 10, 9]
         * @returns {*}
         */
        customStat.average = function (results) {
            results = results || [10, 10, 9, 10, 9, 10, 22];
            // Need to know what a correct/wrong answer would be.
            var correctAnswer = 10;
            var wrongAnswer = 9;

            // Our variables for counting the number correct/wrong.
            var correct =  0;
            var wrong = 0;
            for (var i = 0; i < results.length; i++) {
                if (results[i] === correctAnswer) {
                    correct++;
                }
                else if (results[i] === wrongAnswer) {
                    wrong++;
                }
            }

            // If we don't have any results, we can't get an average.
            var resultsCounted = correct + wrong;
            if (resultsCounted > 0) {
                return parseFloat((correct / resultsCounted).toFixed(2));
            }
            return '';
        };

        /**
         * Example of a custom statistic. Highest score in results.
         * @param results
         * @returns {number}
         */
        customStat.highest = function (results) {
            results = results || [10, 10, 9, 10, 9, 10, 22];
            return Math.max.apply(Math, results);
        };

        console.log(customStat.sum());
        console.log(customStat.average());
        console.log(customStat.highest());

    });
})(jQuery);
