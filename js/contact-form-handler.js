/**
 * Static site contact form handler using Web3Forms API.
 * No server-side code required — works with any static hosting (GitHub Pages, Netlify, etc.).
 *
 * Setup:
 *   1. Get a free access key at https://web3forms.com/
 *   2. Replace WEB3FORMS_ACCESS_KEY below with your key
 *   3. Optionally add hCaptcha for extra spam protection
 *
 * Web3Forms provides:
 *   - Email delivery to your inbox
 *   - Built-in honeypot spam detection (via the botcheck field)
 *   - Optional hCaptcha integration
 *   - JSON API responses for AJAX forms
 */

var WEB3FORMS_ACCESS_KEY = 'YOUR_ACCESS_KEY_HERE';

/**
 * Submit a form to Web3Forms via AJAX.
 * Adapts the Web3Forms JSON response to the format the existing UI code expects.
 *
 * @param {HTMLFormElement} form - The form element
 * @param {jQuery}          form_btn - The submit button
 * @param {string}          form_result_div - Selector for the result message div
 * @param {string}          form_btn_old_msg - Original button HTML to restore after submission
 */
function submitToWeb3Forms(form, form_btn, form_result_div, form_btn_old_msg) {
  var formData = $(form).serializeArray();

  // Inject the access key
  formData.push({ name: 'access_key', value: WEB3FORMS_ACCESS_KEY });

  // Map the honeypot field to Web3Forms' expected field name
  var botcheckVal = '';
  for (var i = 0; i < formData.length; i++) {
    if (formData[i].name === 'form_botcheck' || formData[i].name === 'contact-form-botcheck') {
      botcheckVal = formData[i].value;
      break;
    }
  }
  formData.push({ name: 'botcheck', value: botcheckVal });

  // Include the hCaptcha response if present
  var hcaptchaResponse = $(form).find('[name="h-captcha-response"]').val();
  if (hcaptchaResponse) {
    formData.push({ name: 'h-captcha-response', value: hcaptchaResponse });
  }

  $.ajax({
    url: 'https://api.web3forms.com/submit',
    type: 'POST',
    data: $.param(formData),
    dataType: 'json',
    success: function(data) {
      if (data.success) {
        $(form).find('.form-control').val('');
        // Reset hCaptcha if present
        if (typeof hcaptcha !== 'undefined') {
          hcaptcha.reset();
        }
        $(form_result_div).removeClass('alert-danger').addClass('alert-success');
        $(form_result_div).html('We have <strong>successfully</strong> received your Message and will get Back to you as soon as possible.').fadeIn('slow');
      } else {
        $(form_result_div).removeClass('alert-success').addClass('alert-danger');
        $(form_result_div).html(data.message || 'Something went wrong. Please try again.').fadeIn('slow');
      }
      form_btn.prop('disabled', false).html(form_btn_old_msg);
      setTimeout(function(){ $(form_result_div).fadeOut('slow'); }, 6000);
    },
    error: function() {
      $(form_result_div).removeClass('alert-success').addClass('alert-danger');
      $(form_result_div).html('Something went wrong. Please check your connection and try again.').fadeIn('slow');
      form_btn.prop('disabled', false).html(form_btn_old_msg);
      setTimeout(function(){ $(form_result_div).fadeOut('slow'); }, 6000);
    }
  });
}
