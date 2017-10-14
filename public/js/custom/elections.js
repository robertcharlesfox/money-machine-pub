/**
 * This namespace contains functions that implement the page
 * functionality. Once the initialize() method is called the page is fully
 * functional and can serve the process.
 *
 * @namespace Elections
 */
var Elections = {

    /**
     * This method initializes the page.
     *
     * @param {bool} bindEventHandlers (OPTIONAL) Determines whether the default
     * event handlers will be binded to the dom elements.
     */
    initialize: function(bindEventHandlers) {
        if (bindEventHandlers === undefined) {
            bindEventHandlers = true; // Default Value
        }
        if (bindEventHandlers) {
            Elections.bindEventHandlers();
        }
    },

    /**
     * This method binds the necessary event handlers for the book
     * appointments page.
     */
    bindEventHandlers: function() {
        $('#show-panels').click(function () {
          $('#states-panels').show();
          $('#states-table').hide();
        });
        $('#show-table').click(function () {
          $('#states-table').show();
          $('#states-panels').hide();
        });
        $('.race-update').click(function() {
            var raceId = $(this).attr('data-race-id');
            var dem_chance = $(this).attr('data-dem-chance');
            Elections.saveRaceData(raceId, dem_chance);
        });

        $('#pi_contest_id').change(function () {
          $('#old-polls-hide-' + raceId).show();
          $('#pi_question_id').val('');
          $(".option-question[data-question-contest-id!='" + $('#pi_contest_id').val() + "']").hide();
        });
    },

    saveRaceData: function(raceId, dem_chance) {
        var postUrl = '/elections/races/update';
        var postData = {
            '_token': GlobalVariables.csrfToken,
            'dem_chance': dem_chance,
            'race_id': raceId
        };

        $.post(postUrl, postData, function(response) {
            ///////////////////////////////////////////////////////////////
            console.log('Save Race Data JSON Response:', response);
            ///////////////////////////////////////////////////////////////

            $('#race-dem-chance-' + raceId).html(response.dem_chance);
            
            $('#all-D').html(response.data.totals.all_D);
            $('#all-D-plus').html(response.data.totals.all_D_plus);
            $('#all-R').html(response.data.totals.all_R);

            $('#safe-D').html(response.data.totals.safe_D);
            $('#likely-D').html(response.data.totals.likely_D);
            $('#lean-D').html(response.data.totals.lean_D);
            $('#safe-R').html(response.data.totals.safe_R);
            $('#likely-R').html(response.data.totals.likely_R);
            $('#lean-R').html(response.data.totals.lean_R);
            $('#tossup').html(response.data.totals.tossup);

            var divisor = response.divisor;
            document.getElementById('safe-D').setAttribute("style","width: " + response.data.totals.safe_D/divisor + "%");
            document.getElementById('likely-D').setAttribute("style","width: " + response.data.totals.likely_D/divisor + "%");
            document.getElementById('lean-D').setAttribute("style","width: " + response.data.totals.lean_D/divisor + "%");
            document.getElementById('safe-R').setAttribute("style","width: " + response.data.totals.safe_R/divisor + "%");
            document.getElementById('likely-R').setAttribute("style","width: " + response.data.totals.likely_R/divisor + "%");
            document.getElementById('lean-R').setAttribute("style","width: " + response.data.totals.lean_R/divisor + "%");
            document.getElementById('tossup').setAttribute("style","width: " + response.data.totals.tossup/divisor + "%");

        }, 'json').fail(GeneralFunctions.ajaxFailureHandler);
    },

};
