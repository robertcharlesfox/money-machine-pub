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

        $('#select-service').trigger('change'); // Load the available hours.
    },

    /**
     * This method binds the necessary event handlers for the book
     * appointments page.
     */
    bindEventHandlers: function() {
        $('.btn-save-pollster').click(function() {
            var pollsterId = $(this).attr('data-pollster_id');
            TvCProjections.savePollsterData(pollsterId);
        });

        if (TvCProjections.manageMode) {
        }

        /**
         * Event: Selected Provider "Changed"
         *
         * Whenever the provider changes the available appointment
         * date - time periods must be updated.
         */
        $('#select-provider').change(function() {
            TvCProjections.getAvailableHours(Date.today().toString('dd-MM-yyyy'));
            TvCProjections.updateConfirmFrame();
        });

        /**
         * Event: Selected Service "Changed"
         *
         * When the user clicks on a service, its available providers should
         * become visible.
         */
        $('#select-service').change(function() {
            var currServiceId = $('#select-service').val();
            $('#select-provider').empty();

            $.each(GlobalVariables.availableProviders, function(indexProvider, provider) {
                $.each(provider['services'], function(indexService, serviceId) {
                    // If the current provider is able to provide the selected service,
                    // add him to the listbox.
                    if (serviceId == currServiceId) {
                        var optionHtml = '<option value="' + provider['id'] + '">'
                                + provider['first_name']  + ' ' + provider['last_name']
                                + '</option>';
                        $('#select-provider').append(optionHtml);
                    }
                });
            });

            // Add the "Any Provider" entry.
            // if ($('#select-provider option').length >= 1) {
            //     $('#select-provider').append(new Option('- ' + 'Any Provider' + ' -', 'any-provider'));
            // }


            TvCProjections.getAvailableHours($('#select-date').val());
            TvCProjections.updateConfirmFrame();
            TvCProjections.updateServiceDescription($('#select-service').val(), $('#service-description'));
        });

        /**
         * Event: Selected Service Duration "Changed"
         *
         * When the user clicks on a duration, it triggers a fresh lookup
         * of available hours. Also updates the lesson descriptions on the page.
         */
        $('#select-duration').change(function() {
            TvCProjections.getAvailableHours($('#select-date').val());
            TvCProjections.updateConfirmFrame();
            TvCProjections.updateServiceDescription($('#select-service').val(), $('#service-description'));
        });

        /**
         * Event: Next Step Button "Clicked"
         *
         * This handler is triggered every time the user pressed the
         * "next" button on the book wizard. Some special tasks might
         * be perfomed, depending the current wizard step.
         */
        $('.button-next').click(function() {
            // If we are on the first step and there is not provider selected do not continue
            // with the next step.
            if ($(this).attr('data-step_index') === '1' && $('#select-provider').val() == null) {
                return;
            }

            // If we are on the 2nd tab then the user should have an appointment hour
            // selected.
            if ($(this).attr('data-step_index') === '2') {
                if ($('.selected-hour').length == 0) {
                    if ($('#select-hour-prompt').length == 0) {
                        $('#available-hours').append('<br><br>'
                                + '<span id="select-hour-prompt" class="text-danger">'
                                + 'Please select an appointment hour before continuing!'
                                + '</span>');
                    }
                    return;
                }
            }

            // If we are on the 3rd tab then we will need to validate the user's
            // input before proceeding to the next step.
            if ($(this).attr('data-step_index') === '3') {
                if (!TvCProjections.validateCustomerForm()) {
                    return; // Validation failed, do not continue.
                }
            }
        });

        /**
         * Event: Back Step Button "Clicked"
         *
         * This handler is triggered every time the user pressed the
         * "back" button on the book wizard.
         */
        $('.button-back').click(function() {
            var prevTabIndex = parseInt($(this).attr('data-step_index')) - 1;

            $(this).parents().eq(1).hide('fade', function() {
                $('.active-step').removeClass('active-step');
                $('#step-' + prevTabIndex).addClass('active-step');
                $('#wizard-frame-' + prevTabIndex).show('fade');
            });
        });

        /**
         * Event: Available Hour "Click"
         *
         * Triggered whenever the user clicks on an available hour
         * for his appointment.
         */
        $('#available-hours').on('click', '.available-hour', function() {
            $('.selected-hour').removeClass('selected-hour');
            $(this).addClass('selected-hour');
        });

        /**
         * Event: Book Appointment Form "Submit"
         *
         * Before the form is submitted to the server we need to make sure that
         * in the meantime the selected appointment date/time wasn't reserved by
         * another customer or event.
         */
        $('#book-appointment-submit').click(function(event) {
            TvCProjections.registerAppointment();
        });

        /**
         * Event: Refresh captcha image.
         */
        $('.captcha-title small').click(function(event) {
            $('.captcha-image').attr('src', GlobalVariables.baseUrl + '/index.php/captcha?' + Date.now());
        });
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
            'release_notes': $('#text-release-notes-' + pollsterId).val(),
            'pollster_id': pollsterId
        };

        $.post(postUrl, postData, function(response) {
            ///////////////////////////////////////////////////////////////
            console.log('Save Pollster Data JSON Response:', response);
            ///////////////////////////////////////////////////////////////

            $('#message-holder').append('<span class="available-hour">' + 'boo-ya' + '</span><br/>');
            
                var currColumn = 1;
                $('#available-hours').html('<div style="width:75px; float:left;"></div>');

                $.each(response, function(index, availableHour) {
                    if ((currColumn * 10) < (index + 1)) {
                        currColumn++;
                        $('#available-hours').append('<div style="width:75px; float:left;"></div>');
                    }

                    $('#available-hours div:eq(' + (currColumn - 1) + ')').append(
                            '<span class="available-hour">' + availableHour + '</span><br/>');
                });

                if (TvCProjections.manageMode) {
                    // Set the appointment's start time as the default selection.
                    $('.available-hour').removeClass('selected-hour');
                    $('.available-hour').filter(function() {
                        return $(this).text() === Date.parseExact(
                                GlobalVariables.appointmentData['start_datetime'],
                                'yyyy-MM-dd HH:mm:ss').toString('HH:mm');
                    }).addClass('selected-hour');
                } else {
                    // Set the first available hour as the default selection.
                    $('.available-hour:eq(0)').addClass('selected-hour');
                }

        }, 'json').fail(GeneralFunctions.ajaxFailureHandler);
    },

    /**
     * Every time this function is executed, it updates the confirmation
     * page with the latest customer settigns and input for the appointment
     * booking.
     */
    updateConfirmFrame: function() {
        // Appointment Details
        var selectedDate = $('#select-date').datepicker('getDate');

        var selServiceId = $('#select-service').val();
        var servicePrice, serviceCurrency;
        $.each(GlobalVariables.availableServices, function(index, service) {
            if (service.id == selServiceId) {
                servicePrice = '<br>' + service.price;
                serviceCurrency = service.currency;
                return false; // break loop
            }
        });


        var html =
            '<h4>' + $('#select-service option:selected').text() + '</h4>' +
            '<p>'
                + '<strong class="text-primary">'
                    + $('#select-provider option:selected').text() + '<br>'
                    + $('#select-duration option:selected').text() + '<br>'
                    + selectedDate + ' ' +  $('.selected-hour').text()
                    + serviceCurrency + ' ' + servicePrice
                + '</strong>' +
            '</p>';

        $('#appointment-details').html(html);

        // Customer Details

        var firstname = GeneralFunctions.escapeHtml($('#first-name').val()),
            lastname = GeneralFunctions.escapeHtml($('#last-name').val()),
            phoneNumber = GeneralFunctions.escapeHtml($('#phone-number').val()),
            email = GeneralFunctions.escapeHtml($('#email').val()),
            zipCode = GeneralFunctions.escapeHtml($('#zip-code').val()),

        html =
            '<h4>' + firstname + ' ' + lastname + '</h4>' +
            '<p>' +
                'phone' + ': ' + phoneNumber +
                '<br/>' +
                'email' + ': ' + email +
                '<br/>' +
                'zip_code' + ': ' + zipCode +
            '</p>';

        $('#customer-details').html(html);

        // Update appointment form data for submission to server when the user confirms
        // the appointment.
        var postData = new Object();

        postData['customer'] = {
            'zip_code': $('#zip-code').val()
        };

        var selectedHour = $('.selected-hour').text();
        var theHour = selectedHour.substr(0, selectedHour.indexOf(':'));
        var theMinutes = selectedHour.substr(selectedHour.indexOf(':') + 1, 2);
        if (selectedHour.indexOf('pm') > 0) {
            if (theHour !== 12) {
                theHour = Number(theHour) + 12;
            }
        } else {
            if (theHour == 12) {
                theHour = "00";
            }
        }
        selectedHour = theHour + ":" + theMinutes;

        postData['appointment'] = {
            'start_datetime': $('#select-date').datepicker('getDate').toString('yyyy-MM-dd')
                                    + ' ' + selectedHour + ':00',
            'end_datetime': TvCProjections.calcEndDatetime(selectedHour),
            'notes': $('#notes').val(),
            'id_services': $('#select-service').val()
        };

        $('input[name="_token"]').val(GlobalVariables.csrfToken);
        $('input[name="post_data"]').val(JSON.stringify(postData));
    },

    /**
     * This method calculates the end datetime of the current appointment.
     * End datetime is depending on the service and start datetime fieldss.
     *
     * @return {string} Returns the end datetime in string format.
     */
    calcEndDatetime: function(selectedHour) {
        // Find selected service duration.
        // var selServiceDuration = undefined;
        var selServiceDuration = $('#select-duration').val();

        // Obsolete EA method. Interesting filter method.
        // $.each(GlobalVariables.availableServices, function(index, service) {
        //     if (service.id == $('#select-service').val()) {
        //         selServiceDuration = service.duration;
        //         return false; // Stop searching ...
        //     }
        // });

        // Add the duration to the start datetime.
        var startDatetime = $('#select-date').datepicker('getDate').toString('dd-MM-yyyy')
                + ' ' + selectedHour;
        startDatetime = Date.parseExact(startDatetime, 'dd-MM-yyyy HH:mm');
        var endDatetime = undefined;

        if (selServiceDuration !== undefined && startDatetime !== null) {
            endDatetime = startDatetime.add({ 'minutes' : parseInt(selServiceDuration) });
        } else {
            endDatetime = new Date();
        }

        return endDatetime.toString('yyyy-MM-dd HH:mm:ss');
    },

    /**
     * This method applies the appointment's data to the wizard so
     * that the user can start making changes on an existing record.
     *
     * @param {object} appointment Selected appointment's data.
     * @param {object} provider Selected provider's data.
     * @param {object} customer Selected customer's data.
     * @returns {bool} Returns the operation result.
     */
    applyAppointmentData: function(appointment, provider, customer) {
        try {
            // Select Service & Provider
            $('#select-service').val(appointment['id_services']).trigger('change');
            $('#select-provider').val(appointment['id_users_provider']);

            // Set Appointment Date
            $('#select-date').datepicker('setDate',
                    Date.parseExact(appointment['start_datetime'], 'yyyy-MM-dd HH:mm:ss'));
            TvCProjections.getAvailableHours($('#select-date').val());

            // Apply Customer's Data
            $('#last-name').val(customer['last_name']);
            $('#first-name').val(customer['first_name']);
            var appointmentNotes = (appointment['notes'] !== null)
                    ? appointment['notes'] : '';
            $('#notes').val(appointmentNotes);

            TvCProjections.updateConfirmFrame();

            return true;
        } catch(exc) {
            console.log(exc); // log exception
            return false;
        }
    },

    /**
     * This method updates a div's html content with a brief description of the
     * user selected service (only if available in db). This is usefull for the
     * customers upon selecting the correct service.
     *
     * @param {int} serviceId The selected service record id.
     * @param {object} $div The destination div jquery object (e.g. provide $('#div-id')
     * object as value).
     */
    updateServiceDescription: function(serviceId, $div) {
        var html = '';

        $.each(GlobalVariables.availableServices, function(index, service) {
            if (service.id == serviceId) { // Just found the service.
                html = '<strong>' + service.name + ' </strong>';

                if (service.description != '' && service.description != null) {
                    html += '<br>' + service.description + '<br>';
                }

                if ($('#select-duration').val() != '' && $('#select-duration').val() != null) {
                    html += '[' + 'Duration: ' + ' ' + $('#select-duration').val()
                            + ' ' + 'minutes' + '] ';
                }

                html += '<br>';

                if (service.price != '' && service.price != null) {
                    html += '[' + 'Price ' + service.currency + ' ' + service.price  + ' first 30 minutes]';
                }

                html += '<br>';

                return false;
            }
        });

        $div.html(html);

        if (html != '') {
            $div.show();
        } else {
            $div.hide();
        }
    },

    /**
     * This method will make an ajax call to the appointments controller that will register
     * the appointment to the database.
     */
    registerAppointment: function() {
        var formData = jQuery.parseJSON($('input[name="post_data"]').val());

        var postData = {
            '_token': GlobalVariables.csrfToken,
            'post_data': formData
        };

        $('.captcha-title small').trigger('click');
    }
};
