(function($) {
  $(document).ready(function() {

    // Add alternating classes to rows in some places
    $('li.available-field:nth-child(odd)').addClass('odd');
    $('li.available-field:nth-child(even)').addClass('even');

    // Switch +/- when panels collapse and open
    $('.panel').on('show.bs.collapse', function() {
      $(this).find('.panel-heading i').addClass('fa-minus').removeClass('fa-plus');
    }).on('hide.bs.collapse', function() {
      $(this).find('.panel-heading i').removeClass('fa-minus').addClass('fa-plus');
    });


    // Hide/show the checkbox, entry forms and/or existing Custom Login records available to the user 
    // or the appropriate text when they want to enable custom logins.
    $('#use_custom_login').change(function() {
      $(this).is(':checked') ? $('#custom-login-edit-options').show() : $('#custom-login-edit-options').hide();
    });
    $('.custom-login-account').click(function() {
      $('.login-account-options').show();
      $('.login-table-options').hide();
    });
    $('.custom-login-table').click(function() {
      $('.login-table-options').show();
      $('.login-account-options').hide();
    });

    $('#success_popup_enabled').change(function() {
      $(this).is(':checked') ? $('#success-popup-edit-options').show() : $('#success-popup-edit-options').hide();
    });

    // Hide/show the entry forms and/or detail view pages available to the user or the appropriate text
    // when they want to related a form or detail view to a list.
    $('#related-edit-page').change(function() {
      $(this).is(':checked') ? $('#related-edit-options').show() : $('#related-edit-options').hide();
    });
    if($('#related-edit-exists').length > 0) {
      $('#related-edit-options').show();
    }
    $('#related-detail-page').change(function() {
      $(this).is(':checked') ? $('#related-detail-options').show() : $('#related-detail-options').hide();
    });
    if($('#related-detail-exists').length > 0) {
      $('#related-detail-options').show();
    }
    $('#related-list-page').change(function() {
      $(this).is(':checked') ? $('#related-list-options').show() : $('#related-list-options').hide();
    });
    if($('#related-list-exists').length > 0) {
      $('#related-list-options').show();
    }

    // Tiny plugin to swap out the help text when the visibility checkbox is toggled. Sometimes this is in a modal.
    $.fn.authCheckbox = function() {
      $(this).is(':checked') ? $(this).closest('.form-group').find('.visibility-private').show() : $(this).closest('.form-group').find('.visibility-public').show();
      $(this).change(function() {
        $(this).closest('.form-group').find('.visibility-private, .visibility-public').toggle();
      });
    };
    $('.auth_personal input').authCheckbox();

    // Show/hide more/less options when viewing an Entry Form's config fields. Also on connection maker.
    $('.show-more-options').click(function(e) {
      if($(this).hasClass('open')) {
        $(this).closest('li, .panel-body').find('.more-options').slideUp();
        $(this).removeClass('open').text('+ More options');
        return;
      }
      $(this).closest('li, .panel-body').find('.more-options').slideDown();
      $(this).addClass('open').text('- Less options');
    });
    
    // Switch the port # if https is selected
    $('select[name="https"]').change(function() {
      $(this).closest('.row').next().find('input').val($(this).val() == 'HTTPS' ? '443' : '80');
    });

    // Completely destroy the modal if it's closed so the next time it's
    // generated from scratch again (doesn't hold old values/data).
    $('#payment-modal').on('hide.bs.modal', function () {
      $(this).removeData('bs.modal').find('.modal-heading').empty().next().empty().next().remove();
    });

    // Initialize the creditCardTypeDetector plugin on the CC field on our payment form.
    if ($('#cc_number').length) {
      $('#cc_number').creditCardTypeDetector({'credit_card_logos': '.card_logos'});
    }

    // Update existing card from account page
    $('#update-card').click(function() {
      $(this).addClass('active');
      var cancel = 'Cancel Update';
      if($('#replace-account-card').is(':visible')) {
        $('#replace-account-card, #current-card').hide();
        $('#replace-card').html('Replace <i class="fa fa-credit-card"></i>').removeClass('active');;
      }
      if($(this).text() == cancel) {
        $('#update-account-card').hide();
        $(this).html('Update <i class="fa fa-credit-card"></i>').removeClass('active');
        $('#current-card').show();
        return;
      }
      $('#current-card').hide();
      $('#update-account-card').show();
      $(this).text(cancel);
    });

    // Replace current card with another from account page
    $('#replace-card').click(function() {
      $(this).addClass('active');
      var cancel = 'Cancel Replace';      
      if($('#update-account-card').is(':visible')) {
        $('#update-account-card, #current-card').hide();
        $('#update-card').html('Update <i class="fa fa-credit-card"></i>').removeClass('active');;
      }
      if($(this).text() == cancel) {
        $('#replace-account-card').hide();
        $(this).html('Replace <i class="fa fa-credit-card"></i>').removeClass('active');;
        $('#current-card').show();
        return;
      }
      $('#current-card').hide();
      $('#replace-account-card').show();
      $(this).text(cancel);
    });

    // Once the modal is shown, bind the CC processing function (using stripe.js) to the form.
    // $('#payment-modal').on('loaded.bs.modal', function() {
    $('#btn-buy-fmw').click(function() {

      $('#payment-form').validate({
        rules: {
          fname: {
            required: true,
            minlength: 2
          },
          lname: {
            required: true,
            minlength: 2
          },
          email: {
            required: true,
            email: true
          },
          pass: {
            required: true,
            minlength: 6,
          },
          pass2: {
            required: true,
            equalTo: "#pass",
          },
          cvc: {
            required: true,
            minlength: 3
          },
          "exp-year": {
            required: true,
            minlength: 4,
            maxlength: 4
          },
          terms: {
            required: true,
          }
        },
        messages: {
          fname: {
            required: "First name, please?",
            minlength: "Hey, a first name should be, like, at least 2 letters, right?"
          },
          lname: {
            required: "Last name, please?",
            minlength: "Hey, a last name should be, like, at least 2 letters, right?"
          },
          email: {
            required: "We need your email address to contact you",
            email: "Ummm, that doesn't look like an email..."
          },
          pass: {
            minlength: "Is it too much to ask for at least 6 characters? Be smart, use a strong password!"
          },
          pass2: {
            required: "Please confirm your password.",
            equalTo: "Your passwords do not match."
          },
          cvc: {
            required: "You can find the CVC code on the back of your credit card.",
            minlength: "CVC Codes are 3 or 4 digits, depending on card type."
          },
          "exp-year": {
            required: "What's the expiration date on your card?",
            minlength: "Expiration date needs 4 digits.",
            maxlength: "Expiration date needs 4 digits."
          },
          terms: {
            required: "Please check the box to indicate you have read and agree to the FM Website terms of service.",
          }
        }
      });

      // Stop the normal submission and run the stripe callback to get our token.
      // The logic's just a smidge different for upgrades, so duplicating for now.
      $('#subscription-upgrade-from-free').submit(function(e) {
        var $form = $(this);
        if($form.find('#stripe-card').length > 0 && $form.valid()) {
          return true;
        } else {
          $form.find('.payment-errors').hide();
          $form.closest('.modal-body').next().find('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-clock-o"></i> Processing...');
          Stripe.card.createToken($form, stripeResponseHandler);
          return false;
        }
      });

      $('#payment-form').submit(function(e) {
        var $form = $(this);
        if( ! $form.hasClass('fmw_free')) {
          if($form.find('#stripe-card').length > 0 && $form.valid()) {
            return true;
          } else {
            $form.find('.payment-errors').hide();
            $form.closest('.modal-body').next().find('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-clock-o"></i> Processing...');
            Stripe.card.createToken($form, stripeResponseHandler);
            return false;
          }
        } else if ($form.valid()) {
          $form.closest('.modal-body').next().find('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-clock-o"></i> Processing...');
        }
      });

      // On the subscription change, we just want to show the change to processing button. Doesn't use secure data, just a stripe token.
      $('#subscription-change-form').submit(function(e) {
        $(this).closest('.modal-body').next().find('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-clock-o"></i> Processing...');        
      });

      // Our stripe callback, returns a stripe token if successful and appends to our form and submits.
      function stripeResponseHandler(status, response) {
        $form = $('#payment-form, #subscription-upgrade-from-free');
        if (!$form.valid() || response.error) {
          $form.find('.payment-errors').text(response.error.message).show();
          $form.find('button[type="submit"]').prop('disabled', false).html('Start Building <i class="fa fa-arrow-right"></i>');
        } else {
          var token = response.id;
          $form.append($('<input type="hidden" name="stripeToken" />').val(token));
          $form.get(0).submit();
        }
      };
    });

    /* Set the value of the dropdown to the current expiration month of the existing card */
    /* The card update form doesn't send secure data, so we just use a normal POST instead of stripe.js intevention. */
    $('#update-card-form #exp-month option').each(function() {
      if(parseFloat($(this).val()) == $('#update-card-form #exp-month').data('current-exp-month')) {
        $(this).prop('selected', true);
      }
    });

    // The above is just for payment and is only triggered within the buy/subscribe modal.
    // This is for replacing the user's CC on their account, and Stripe will charge the new card next billing cycle. 
    $('#replace-card-form').submit(function(e) {
      var $form = $(this);
      if($form.find('#stripe-card').length > 0) {
        return true; 
      } else {
        $form.find('.payment-errors').hide();
        $form.find('button').prop('disabled', true).html('<i class="fa fa-clock-o"></i> Just a moment...');
        Stripe.card.createToken($form, stripeResponseHandler);
        return false;
      }
    });

    // Our stripe callback, returns a stripe token if successful and appends to our form and submits.
    function stripeResponseHandler(status, response) {
      $form = $('#replace-card-form');
      if (response.error) {
        $form.find('.payment-errors').text(response.error.message).show();
        $form.find('button').prop('disabled', false).html('<i class="fa fa-floppy-o"></i> Update');
      } else {
        var token = response.id;
        $form.append($('<input type="hidden" name="stripeToken" />').val(token));
        $form.get(0).submit();
      }
    };

    /* initialize popovers and tooltips where needed */
    $('.popover-trigger').popover({
      container: 'body',
      html: 'true',
      trigger: 'focus',
    });
    $('.tooltip-trigger').tooltip({
      container: 'body',
    });

    // Define a simple function to briefly show and fade out a success message for any sortable stuff being reordered successfully. 
    var ajaxSuccessTimeout = setTimeout(function() {
      $('#ajax-success').fadeOut('slow').remove();
    }, 3000);
    var reorderSuccess = function(data) {
      var messageHTML = $('<div class="alert alert-success alert-dismissable" id="ajax-success">Changes to sort order successful!</div>');
      if($('#successes').length > 0) {
        messageHTML.appendTo('#successes').hide().fadeIn('slow');
      } else {
        messageHTML.insertBefore('#content').hide().fadeIn('slow');
      }
      clearTimeout(ajaxSuccessTimeout);
      ajaxSuccessTimeout = setTimeout(function() {
        $('#ajax-success').fadeOut('slow').remove();
      }, 3000);
    };

    /* Define sortable behaviors for the List Page *Display* fields. */
    if($('#page-display-field').length > 0) {
      $('#page-display-field').sortable({
        handle: '.handle',
        axis: 'y',
        update: function (event, ui) {
          var sortedSerialized = $(this).sortable('serialize');
          $.ajax({
            data: sortedSerialized,
            type: 'POST',
            url: '/maker/ajax/fields/display',
            success: reorderSuccess(),
          });
        },
      });
    };
    
    /* Define sortable behaviors for the List Page *Sort* fields. */
    if($('#page-sort-field').length > 0) {
      $('#page-sort-field').sortable({
        handle: '.handle',
        axis: 'y',
        update: function (event, ui) {
          var sortedSerialized = $(this).sortable('serialize');
          $.ajax({
            data: sortedSerialized,
            type: 'POST',
            url: '/maker/ajax/fields/sort',
            success: reorderSuccess(),
          });
        },
      });
    };
    
    /* Define sortable behaviors for the Form Page *Input* fields. */
    if($('#page-sort-input').length > 0) {
      $('#page-sort-input').sortable({
        handle: '.handle',
        axis: 'y',
        update: function (event, ui) {
          var sortedSerialized = $(this).sortable('serialize');
          $.ajax({
            data: sortedSerialized,
            type: 'POST',
            url: '/maker/ajax/fields/input',
            success: reorderSuccess(),
          });
        },
      });
    };

    // Run the cleanURLs code anywhere needed.
    if($('#url_prefix, input#route').length > 0) {
      $('input#route').cleanURLs();
    }
    $('.modal').on('shown.bs.modal', function() {
      if($(this).find('input#route').length > 0) {
        $(this).find('input#route').unbind('keypress keyup').cleanURLs();
      }
    });

    // Function to run on Pages page, to filter pages dynamically on load and on change of filters.
    var filterPages = function() {
      var allFilterVals = [];
      // Build the allFilterVals object with the current setting of each filter.
      $('#pages-filters select option:selected').each(function() {
        allFilterVals[$(this).closest('select').attr('id')] = $(this).val();
      });
      // Get a reference to all pages that we can then loop through and compare each against the filters.
      var allPages = $('#pages-list li.page');
      allPages.each(function(i) {
        var page = $(this);
        for (filterName in allFilterVals) {
          var value = allFilterVals[filterName];
          // If the page in the list doesn't  match the filter we're checking...
          if(page.data(filterName) != value) {
            // Is it set to ALL or BOTH or something? If so, leave it visible...
            if(value == '-1') {
              page.addClass('on').removeClass('off');
            } else {
              // If not, it doesn't match the filter setting. Hide it.
              page.addClass('off').removeClass('on');
              return;
            }
          } else {
            // The page DOES match the filter, keep it visible 
            // (unless it doesn't match some other filter, it will get hidden anyway).
            page.addClass('on').removeClass('off');
          }
        }
      });
      // If filter settings result in no pages being visible, let's append one as needed to say as much.
      // Provide a link to reset the filters too.
      var noResults = $('<li id="no-results" class="list-group-item">No matching pages found. Modify your filters and try again or <a href="#" id="reset-filters">reset the filters</a>.</li>');
      if($('#pages-list .page').length > 0 && $('#pages-list .page:visible').length == 0) {
        // If it's already been added, don't add it again. Duh.
        if($('#no-results').length > 0) {return;}
        $('#pages-list').append(noResults);
      } else {
        // Remove it if it's been added and we've modified filters to bring up some valid matches.
        $('#no-results').remove();
      }
      $('#reset-filters').click(function() {
        $('#pages-filters select').val('-1').trigger('change');
      });
    }
    
    // Make Pages page filters work, even if values are set on the page by php.
    $('#pages-filters').ready(filterPages);
    $('#pages-filters').change(filterPages);

    // POST ajax data regarding use of Connection Tester
    $('.connection-tester-data').click(function() {
      var id = $(this).attr('id');
      var dataString = 'id='+id;
      $.ajax({
        data: dataString,
        type: 'POST',
        url: '/maker/ajax/connection/tester',
      });
    });

    // Allow manual entry and reset of colors for paid subscribers who want to customize their colors.
    $('#use-default-logo').click(function() {
      $('#custom-logo img').attr('src','/images/fmweb_me_logo.png');
      $('#default-logo').val('/images/fmweb_me_logo.png');
    });
    $('.default-color').click(function() {
      var input = $(this).closest('.hint').prev().find('input');
      input.attr('name') == 'color_primary' ? input.val('#1b5731') : input.val('#f16638');
    });
    if(Modernizr.inputtypes.color) {
      $('.manual-color').text('Enter Manually').click(function() {
        if($(this).hasClass('use-colorpicker')) {
          $(this).removeClass('use-colorpicker').text('Enter Manually').closest('.hint').prev().find('input').attr('type','color');
        } else {
          $(this).closest('.hint').prev().find('input').attr('type','text');
          $(this).addClass('use-colorpicker').text('Use Colorpicker');
        }
      });
    }

  });
})(jQuery);