/**
 * seamless form
 */
$('.payment-options .payment-option').on('change', function () {
  setTimeout(function () {
    var $seamlessForm = $('.js-payment-option-form:visible .payment-form-seamless');
    if ($seamlessForm.length) {
      initAllSecureExchangeSeamless($seamlessForm[0]);
    }
  }, 10);
});

var initAllSecureExchangeSeamless = function (seamlessForm) {
  var validNumber;
  var validCvv;

  var $seamlessForm = $(seamlessForm);
  var integrationKey = $seamlessForm.data('integrationKey');
  var formId = $seamlessForm.data('id');
  // var allowedCards = $seamlessForm.data('cards');

  var $seamlessCardHolderFirstNameInput = $('#allsecure-exchange-ccFirstName-' + formId, $seamlessForm);
  var $seamlessCardHolderLastNameInput = $('#allsecure-exchange-ccLastName-' + formId, $seamlessForm);
  var $seamlessCardHolderInput = $('#allsecure-exchange-ccCardHolder-' + formId, $seamlessForm);
  var $seamlessCardNumberInput = $('#allsecure-exchange-ccCardNumber-' + formId, $seamlessForm);
  var $seamlessCvvInput = $('#allsecure-exchange-ccCvv-' + formId, $seamlessForm);
  var $seamlessExpiryMonthInput = $('#allsecure-exchange-ccExpiryMonth-' + formId, $seamlessForm);
  var $seamlessExpiryYearInput = $('#allsecure-exchange-ccExpiryYear-' + formId, $seamlessForm);
  var $seamlessError = $('#payment-error-' + formId, $seamlessForm);

  /**
   * fixed seamless input heights
   */
  $seamlessCardNumberInput.css('height', $seamlessCardHolderInput.css('height'));
  $seamlessCvvInput.css('height', $seamlessCardHolderInput.css('height'));
  
  /**
   * copy styles
   */
  var style = {
	'background': $seamlessCardHolderInput.css('background'),																  
    'border': 'none',
    'height': '100%',
    'padding': $seamlessCardHolderInput.css('padding'),
    'font-size': $seamlessCardHolderInput.css('font-size'),
    'color': $seamlessCardHolderInput.css('color'),
	
  };

  /**
   * initialize
   */
  var payment = new PaymentJs('1.2');
  payment.init(integrationKey, $seamlessCardNumberInput.prop('id'), $seamlessCvvInput.prop('id'),
    function (payment) {
      payment.setNumberStyle(style);
      payment.setCvvStyle(style);
      payment.numberOn('input', function (data) {
        validNumber = data.validNumber;
      });
      payment.cvvOn('input', function (data) {
        validCvv = data.validCvv;
      });
    });

  /**
   * handler
   */
  $seamlessForm.submit(function (e) {
    e.preventDefault();

    payment.tokenize(
      {
		card_holder: $seamlessCardHolderInput.val(),
        month: $seamlessExpiryMonthInput.val(),
        year: $seamlessExpiryYearInput.val(),
      },
      function (token, cardData) {
        $seamlessForm.off('submit');
		$seamlessError.hide();
        $seamlessForm.append('<input type="hidden" name="token" value="' + token + '"/>');
        $seamlessForm.submit();
      },
	  function (errors) {
		$seamlessError.show().html('');
		$seamlessError.append('<ul class="error" style="margin: 0px;">');

        errors.forEach(function (error) {
			if (error.attribute.includes('number') === true) {
				$('.error').append('<li><b>!</b> '+window.errorNumber+'</li>');
			}
			if (error.attribute.includes('year') === true) {
				$('.error').append('<li><b>!</b> '+window.errorExpiry+'</li>');
			}
			if (error.attribute.includes('cvv') === true) {
				$('.error').append('<li><b>!</b> '+window.errorCvv+'</li>');
			}
			if (error.attribute.includes('card_holder') === true) {
				$('.error').append('<li><b>!</b> '+window.errorName+'</li>');
			}
		$seamlessError.attr("tabindex",-1).focus();
		});
		$seamlessError.append('</ul>');
	},
  );
  });
};