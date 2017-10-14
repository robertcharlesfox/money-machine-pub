/**
 * This namespace contains functions that implement the page
 * functionality. Once the initialize() method is called the page is fully
 * functional and can serve the process.
 *
 * @namespace TvCProjections
 */
var TvCProjections = {
    /**
     * Determines the functionality of the page.
     *
     * @type {bool}
     */
    manageMode: false,

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

        // Bind the event handlers (might not be necessary every time
        // we use this class).
        if (bindEventHandlers) {
            TvCProjections.bindEventHandlers();
        }
    },

    /**
     * This method binds the necessary event handlers for the book
     * appointments page.
     */
    bindEventHandlers: function() {
        $('.glyphicon-plus').click(function() {
            var pollsterId = $(this).attr('data-pollster_id');
            $('.old-polls-' + pollsterId).show();
            $('#old-polls-show-' + pollsterId).hide();
            $('#old-polls-hide-' + pollsterId).show();
        });
        $('.glyphicon-minus').click(function() {
            var pollsterId = $(this).attr('data-pollster_id');
            $('.old-polls-' + pollsterId).hide();
            $('#old-polls-show-' + pollsterId).show();
            $('#old-polls-hide-' + pollsterId).hide();
        });

        $('#pollster-results').click(function() {
            $('.analysis-table-results').show('fade');
            $('.analysis-table-selectors').hide('fade');
            $('.analysis-table-early-results').hide('fade');
            $('.analysis-table-release-notes').hide('fade');
            $('.analysis-table-drop-analysis').hide('fade');
            $('.analysis-table-historic-results').hide('fade');
        });
        $('#pollster-selectors').click(function() {
            $('.analysis-table-results').hide('fade');
            $('.analysis-table-selectors').show('fade');
            $('.analysis-table-early-results').hide('fade');
            $('.analysis-table-release-notes').hide('fade');
            $('.analysis-table-drop-analysis').hide('fade');
            $('.analysis-table-historic-results').hide('fade');
        });
        $('#pollster-early-results').click(function() {
            $('.analysis-table-results').hide('fade');
            $('.analysis-table-selectors').hide('fade');
            $('.analysis-table-early-results').show('fade');
            $('.analysis-table-release-notes').hide('fade');
            $('.analysis-table-drop-analysis').hide('fade');
            $('.analysis-table-historic-results').hide('fade');
        });
        $('#pollster-release-notes').click(function() {
            $('.analysis-table-results').hide('fade');
            $('.analysis-table-selectors').hide('fade');
            $('.analysis-table-early-results').hide('fade');
            $('.analysis-table-release-notes').show('fade');
            $('.analysis-table-drop-analysis').hide('fade');
            $('.analysis-table-historic-results').hide('fade');
        });
        $('#pollster-drop-analysis').click(function() {
            $('.analysis-table-results').hide('fade');
            $('.analysis-table-selectors').hide('fade');
            $('.analysis-table-early-results').hide('fade');
            $('.analysis-table-release-notes').hide('fade');
            $('.analysis-table-drop-analysis').show('fade');
            $('.analysis-table-historic-results').hide('fade');
        });
        $('#pollster-historic-results').click(function() {
            $('.analysis-table-results').hide('fade');
            $('.analysis-table-selectors').hide('fade');
            $('.analysis-table-early-results').hide('fade');
            $('.analysis-table-release-notes').hide('fade');
            $('.analysis-table-drop-analysis').hide('fade');
            $('.analysis-table-historic-results').show('fade');
        });

        $('.btn-save-pollster').click(function() {
            var pollsterId = $(this).attr('data-pollster_id');
            TvCProjections.savePollsterData(pollsterId);
        });

        if (TvCProjections.manageMode) {
        }
    },

    /**
     * This function makes an ajax call and returns the available
     * hours for the selected service, provider and date.
     *
     * @param {string} selDate The selected date of which the available
     * hours we need to receive.
     */
    savePollsterData: function(pollsterId) {
        // Make ajax post request and get the available hours.
        var postUrl = '/rcp/ajax/pollster/save';

        var postData = {
            '_token': GlobalVariables.csrfToken,
            'probability_added': $('#select-add-' + pollsterId).val(),
            'probability_dropped': $('#select-drop-' + pollsterId).val(),
            'probability_updated': $('#select-update-' + pollsterId).val(),
            'update_frequency': $('#select-frequency-' + pollsterId).val(),
            'release_notes': $('#text-release-notes-' + pollsterId).val(),
            'early_Clinton': $('#early_Clinton-' + pollsterId).val(),
            'early_Trump': $('#early_Trump-' + pollsterId).val(),
            'early_spread': $('#early_spread-' + pollsterId).val(),
            'early_Johnson': $('#early_Johnson-' + pollsterId).val(),
            'early_Stein': $('#early_Stein-' + pollsterId).val(),
            'projected_result': $('#projected_result-' + pollsterId).val(),
            'pollster_id': pollsterId
        };

        $.post(postUrl, postData, function(response) {
            ///////////////////////////////////////////////////////////////
            console.log('Save Pollster Data JSON Response:', response);
            ///////////////////////////////////////////////////////////////

            $('#pollster-value-' + pollsterId).html(response.new_values);
            $('#rcp-all-inclusive').html(response.rcp_all_inclusive);
            $('#rcp-un-adjusted').html(response.rcp_un_adjusted);

            var pi_values = response.pi_contract_new_values;
            for (i = 0; i < pi_values.length; i++) { 
                $('#polling-contract-' + pi_values[i].id).html(pi_values[i].value + '%');
            }

        }, 'json').fail(GeneralFunctions.ajaxFailureHandler);
    },

};
