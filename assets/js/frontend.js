/**
 * Frontend JavaScript
 * Each .bfmsf-form-wrapper is initialized independently
 * to prevent cross-form interference on multi-form pages.
 */

jQuery(document).ready(function ($) {

    $('.bfmsf-form-wrapper').each(function () {

        var $wrapper    = $(this);
        var maxSteps    = $wrapper.find('.bfmsf-form-step').length;
        var currentStep = 1;

        /**
         * Update progress bar
         */
        function updateProgress() {
            var progress = (currentStep / maxSteps) * 100;
            $wrapper.find('.bfmsf-progress-fill').css('width', progress + '%');
        }

        /**
         * Show specific step
         */
        function showStep(step) {
            $wrapper.find('.bfmsf-form-step').hide();
            $wrapper.find('.bfmsf-form-step[data-step="' + step + '"]').show();

            // Update button visibility
            if (step > 1) {
                $wrapper.find('.bfmsf-prev-btn').show();
            } else {
                $wrapper.find('.bfmsf-prev-btn').hide();
            }

            if (step < maxSteps) {
                $wrapper.find('.bfmsf-next-btn').show();
                $wrapper.find('.bfmsf-submit-btn').hide();
            } else {
                $wrapper.find('.bfmsf-next-btn').hide();
                $wrapper.find('.bfmsf-submit-btn').show();
            }

            updateProgress();

            // Scroll to this specific form
            $('html, body').animate({
                scrollTop: $wrapper.offset().top - 100
            }, 500);
        }

        /**
         * Validate current step
         */
        function validateCurrentStep() {
            var $currentStepForm = $wrapper.find('.bfmsf-form-step[data-step="' + currentStep + '"]');
            var inputs           = $currentStepForm.find('input[required], select[required], textarea[required]');
            var isValid          = true;

            inputs.each(function () {
                var $input  = $(this);
                var isEmpty = false;

                if ($input.attr('type') === 'checkbox' || $input.attr('type') === 'radio') {
                    // Check within this form only
                    var groupName = $input.attr('name');
                    isEmpty = $wrapper.find('input[name="' + groupName + '"]:checked').length === 0;
                } else {
                    isEmpty = !$input.val();
                }

                if (isEmpty) {
                    isValid = false;
                    $input.addClass('error');
                } else {
                    $input.removeClass('error');
                }
            });

            return isValid;
        }

        /**
         * Next button click
         */
        $wrapper.find('.bfmsf-next-btn').on('click', function (e) {
            e.preventDefault();

            if (validateCurrentStep() && currentStep < maxSteps) {
                currentStep++;
                showStep(currentStep);
            } else if (!validateCurrentStep()) {
                alert('Please fill in all required fields.');
            }
        });

        /**
         * Previous button click
         */
        $wrapper.find('.bfmsf-prev-btn').on('click', function (e) {
            e.preventDefault();
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });

        /**
         * Form submission
         */
        $wrapper.find('.bfmsf-form').on('submit', function (e) {
            e.preventDefault();

            // Final validation
            if (!validateCurrentStep()) {
                alert('Please fill in all required fields.');
                return;
            }

            var form     = this;
            var formData = new FormData(form);

            // Append the AJAX action so the server routes this request.
            formData.append('action', 'BFMSF_submit_form');

            // --- Collect CAPTCHA token if a widget is present in this form ---
            var captchaToken = '';
            var captchaType  = '';

            // hCaptcha: response stored in a hidden textarea[name="h-captcha-response"]
            var $hcaptchaResponse = $wrapper.find('textarea[name="h-captcha-response"]');
            if ($hcaptchaResponse.length && $hcaptchaResponse.val()) {
                captchaToken = $hcaptchaResponse.val();
                captchaType  = 'hcaptcha';
            }

            // Google reCAPTCHA v2: response stored in textarea[name="g-recaptcha-response"]
            if (!captchaToken) {
                var $recaptchaResponse = $wrapper.find('textarea[name="g-recaptcha-response"]');
                if ($recaptchaResponse.length && $recaptchaResponse.val()) {
                    captchaToken = $recaptchaResponse.val();
                    captchaType  = 'recaptcha';
                }
            }

            // Cloudflare Turnstile: response stored in input[name="cf-turnstile-response"]
            if (!captchaToken) {
                var $turnstileResponse = $wrapper.find('input[name="cf-turnstile-response"]');
                if ($turnstileResponse.length && $turnstileResponse.val()) {
                    captchaToken = $turnstileResponse.val();
                    captchaType  = 'turnstile';
                }
            }
            // --- End CAPTCHA collection ---

            // Honeypot: if any hidden anti-spam field was filled, flag it for the server.
            var honeypotFilled = false;
            $wrapper.find('input[name^="bfmsf_hp_"]').each(function () {
                if ($(this).val()) {
                    honeypotFilled = true;
                    return false;
                }
            });

            // Append CAPTCHA and honeypot data to the FormData payload.
            formData.append('captcha_token', captchaToken);
            formData.append('captcha_type', captchaType);
            formData.append('honeypot', honeypotFilled ? 1 : 0);

            // Show loading state — scoped to this form
            var $submitBtn   = $wrapper.find('.bfmsf-submit-btn');
            var originalText = $submitBtn.text();
            $submitBtn.prop('disabled', true).text('Submitting...');

            // Submit form
            $.ajax({
                url:         BFMSF_vars.ajax_url,
                type:        'POST',
                dataType:    'json',
                processData: false,
                contentType: false,
                data:        formData,
                success: function (response) {
                    if (response.success) {
                        var resData = response.data || {};

                        $wrapper.find('.bfmsf-form').fadeOut(function () {
                            $(this).remove();
                        });
                        $wrapper.find('.bfmsf-form-navigation').fadeOut();

                        var $successMsg = $wrapper.find('.bfmsf-success-message');
                        if (resData.confirmation_message) {
                            $successMsg.find('p').first().text(resData.confirmation_message);
                        }
                        $successMsg.show();

                        // Scroll to this form's success message
                        $('html, body').animate({
                            scrollTop: $successMsg.offset().top - 100
                        }, 500);

                        // If configured to redirect, show the success message briefly first,
                        // then send the visitor to the configured URL.
                        if (resData.confirmation_type === 'redirect' && resData.redirect_url) {
                            setTimeout(function () {
                                window.location.href = resData.redirect_url;
                            }, 1800);
                        }
                    } else {
                        alert(response.data.message || 'Error submitting form');
                        $submitBtn.prop('disabled', false).text(originalText);
                    }
                },
                error: function () {
                    alert('Error submitting form. Please try again.');
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });

        /**
         * Conditional Logic — evaluate and show/hide fields
         * Conditions stored as: [{fieldId, operator, value}]
         * Supported operators: equals, not_equals, contains, not_empty
         */
        function evaluateConditions() {
            $wrapper.find('[data-bfmsf-conditions]').each(function () {
                var $fieldWrapper = $(this);
                var conditions;
                try {
                    conditions = JSON.parse($fieldWrapper.attr('data-bfmsf-conditions'));
                } catch (e) {
                    return;
                }
                if (!conditions || !conditions.length) return;

                // All conditions must pass (AND logic)
                var allPass = true;
                $.each(conditions, function (i, cond) {
                    if (!cond.fieldId) return; // skip blank

                    // Find the trigger field within this form only
                    var $triggerWrapper = $wrapper.find('[data-field-id="' + cond.fieldId + '"]');
                    var triggerVal      = '';

                    if ($triggerWrapper.length) {
                        var $inp = $triggerWrapper.find('input, select, textarea').first();
                        if ($inp.attr('type') === 'checkbox') {
                            triggerVal = $inp.is(':checked') ? $inp.val() || 'on' : '';
                        } else if ($inp.attr('type') === 'radio') {
                            triggerVal = $triggerWrapper.find('input[type="radio"]:checked').val() || '';
                        } else {
                            triggerVal = $inp.val() || '';
                        }
                    }

                    var pass = false;
                    switch (cond.operator) {
                        case 'equals':     pass = (triggerVal === cond.value);               break;
                        case 'not_equals': pass = (triggerVal !== cond.value);               break;
                        case 'contains':   pass = triggerVal.indexOf(cond.value) !== -1;     break;
                        case 'not_empty':  pass = (triggerVal !== '' && triggerVal !== null); break;
                        default:           pass = true;
                    }
                    if (!pass) allPass = false;
                });

                if (allPass) {
                    $fieldWrapper.show();
                } else {
                    $fieldWrapper.hide();
                    // Clear values so hidden required fields don't block submission
                    $fieldWrapper.find('input, select, textarea').each(function () {
                        if ($(this).attr('type') === 'checkbox' || $(this).attr('type') === 'radio') {
                            $(this).prop('checked', false);
                        } else {
                            $(this).val('');
                        }
                    });
                }
            });
        }

        // Re-evaluate whenever any field value changes within this form
        $wrapper.on('input change', '.bfmsf-form input, .bfmsf-form select, .bfmsf-form textarea', function () {
            evaluateConditions();
        });

        // Clear error on input change — scoped to this form
        $wrapper.on('change input', '.bfmsf-form input[required], .bfmsf-form select[required], .bfmsf-form textarea[required]', function () {
            if ($(this).val()) {
                $(this).removeClass('error');
            }
        });

        // Initial evaluation on page load
        evaluateConditions();

        // Initialize
        if (maxSteps > 0) {
            showStep(1);
        }

    }); // end .each

}); // end ready