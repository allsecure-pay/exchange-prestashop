<form class="payment-form-seamless" data-id="{$id}" data-integration-key="{$integrationKey}" data-cards="{foreach $allowedCards as $allowedCard}{$allowedCard} {/foreach}" method="POST" action="{$action}">
    <input type="hidden" name="ccEmail" value="">
	<script>
		window.errorName="{l s='Cardholder not valid' mod='allsecureexchange'}";
		window.errorNumber="{l s='Card number not valid' mod='allsecureexchange'}";
		window.errorCvv="{l s='CVV not valid' mod='allsecureexchange'}";
		window.errorExpiry="{l s='Expiry date not valid' mod='allsecureexchange'}";
	</script>
    <div>
		<div class="row">
			<div class="form-group col-md-12" style="margin-bottom: 0px;">
				<div id="payment-error-{$id}" class="alert alert-warning" style="display: none;" tabindex="-1"></div>        
				<div id="payment-cards-{$id}" style="width: 100%; padding: 0px; text-align: right;">
				{foreach $allowedCards as $allowedCard}
					<img src="{$this_path|escape:'html':'UTF-8'}/views/img/creditcard/{$allowedCard|escape:'htmlall':'UTF-8'}.svg" class="allsecure_exchange_brandImage" alt="{$allowedCard|escape:'htmlall':'UTF-8'}"> 
				{/foreach}
				</div>
			</div>
		</div>
        <div class="row">
            <div class="form-group col-md-12">
                <label class="form-control-label">
				{l s='Card Number' mod='allsecureexchange'}
				</label>
				<div class="form-control" id="allsecure-exchange-ccCardNumber-{$id}" style="padding: 0; overflow: hidden"></div>
		   </div>
        </div>

        <div class="row">
            <div class="form-group col-md-4">
                <label class="form-control-label">
				{l s='Month' mod='allsecureexchange'}
				</label>
                <div class="">
                    <select class="form-control" name="ccExpiryMonth" id="allsecure-exchange-ccExpiryMonth-{$id}">
                        {foreach from=$months item=month}
                            <option value="{$month}">{$month}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="form-group col-md-4">
                <label class="form-control-label">
				{l s='Year' mod='allsecureexchange'}
				</label>
                <div class="">
                    <select class="form-control" name="ccExpiryYear" id="allsecure-exchange-ccExpiryYear-{$id}">
                        {foreach from=$years item=year}
                            <option value="{$year}">{$year}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
			<div class="form-group col-md-4">
                <label class="form-control-label">
				{l s='CVV' mod='allsecureexchange'}
				</label>
				<div class="form-control" id="allsecure-exchange-ccCvv-{$id}" style="padding: 0; overflow: hidden"></div>
            </div>
		</div>
		<div class="row">
			<div class="form-group col-md-12">
				<label class="form-control-label">
				{l s='Card Holder' mod='allsecureexchange'}
				</label>
				<div class="">
					<input type="text" class="form-control" name="ccCardHolder" id="allsecure-exchange-ccCardHolder-{$id}"/>
				</div>
			</div>
        </div>
    </div>
</form>