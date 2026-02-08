<?php
add_action('wp_footer', 'currency_custom__script', 1000);
function currency_custom__script()
{ ?>
	<script type='text/javascript'>
		// jQuery( function( $ ) {
		// });
		if(General.site_url){
			const input_currency = document.querySelector('#input_currency');
			const output_currency = document.querySelector('#output_currency');
			const input_amount = document.querySelector('#input_amount');
			const output_amount = document.querySelector('#output_amount');
			const exchange = document.querySelector('#exchange');
			const rate = document.querySelector('#rate');
			input_currency.addEventListener('change', compute);
			output_currency.addEventListener('change', compute);
			input_amount.addEventListener('input', compute);
			output_amount.addEventListener('input', compute);
			exchange.addEventListener('click', () => {
				const temp = input_currency.value;
				input_currency.value = output_currency.value;
				output_currency.value = temp;
				compute();
			});
			function compute() {
				const input_currency1 = input_currency.value;
				const output_currency1 = output_currency.value;
				fetch(`https://api.exchangerate-api.com/v4/latest/${input_currency1}`)
					.then(res => res.json())
					.then(res => {
						const new_rate = res.rates[output_currency1];
						rate.innerText = `1 ${input_currency1} = ${new_rate} ${output_currency1}`
						output_amount.value = (input_amount.value * new_rate).toFixed(2);
					})
			}
			compute();
		}
	</script>
<?php }
add_shortcode('currency_converter', 'currency_converter');
function currency_converter($atts)
{
	ob_start(); ?>
	<div class="currencySec">
		<div class="currencyOut">
		<div class="currency">
			<select name="" id="input_currency">
				<option value="AED">AED</option>
				<option value="ARS">ARS</option>
				<option value="AUD">AUD</option>
				<option value="BGN">BGN</option>
				<option value="BRL">BRL</option>
				<option value="BSD">BSD</option>
				<option value="CAD">CAD</option>
				<option value="CHF">CHF</option>
				<option value="CLP">CLP</option>
				<option value="CNY">CNY</option>
				<option value="COP">COP</option>
				<option value="CZK">CZK</option>
				<option value="DKK">DKK</option>
				<option value="DOP">DOP</option>
				<option value="EGP">EGP</option>
				<option value="EUR" selected>EUR</option>
				<option value="FJD">FJD</option>
				<option value="GBP">GBP</option>
				<option value="GTQ">GTQ</option>
				<option value="HKD">HKD</option>
				<option value="HRK">HRK</option>
				<option value="HUF">HUF</option>
				<option value="IDR">IDR</option>
				<option value="ILS">ILS</option>
				<option value="INR">INR</option>
				<option value="ISK">ISK</option>
				<option value="JPY">JPY</option>
				<option value="KRW">KRW</option>
				<option value="KZT">KZT</option>
				<option value="MXN">MXN</option>
				<option value="MYR">MYR</option>
				<option value="NOK">NOK</option>
				<option value="NZD">NZD</option>
				<option value="PAB">PAB</option>
				<option value="PEN">PEN</option>
				<option value="PHP">PHP</option>
				<option value="PKR">PKR</option>
				<option value="PLN">PLN</option>
				<option value="PYG">PYG</option>
				<option value="RON">RON</option>
				<option value="RUB">RUB</option>
				<option value="SAR">SAR</option>
				<option value="SEK">SEK</option>
				<option value="SGD">SGD</option>
				<option value="THB">THB</option>
				<option value="TRY">TRY</option>
				<option value="TWD">TWD</option>
				<option value="UAH">UAH</option>
				<option value="USD">USD</option>
				<option value="UYU">UYU</option>
				<option value="VND">VND</option>
				<option value="ZAR">ZAR</option>
			</select>
			<input type="number" name="" class="numberF" id="input_amount" value="1">
		</div>
		<button id="exchange">
			↕
		</button>
		<div class="currency">
			<select name="" id="output_currency">
				<option value="AED">AED</option>
				<option value="ARS">ARS</option>
				<option value="AUD">AUD</option>
				<option value="BGN">BGN</option>
				<option value="BRL">BRL</option>
				<option value="BSD">BSD</option>
				<option value="CAD">CAD</option>
				<option value="CHF">CHF</option>
				<option value="CLP">CLP</option>
				<option value="CNY">CNY</option>
				<option value="COP">COP</option>
				<option value="CZK">CZK</option>
				<option value="DKK">DKK</option>
				<option value="DOP">DOP</option>
				<option value="EGP">EGP</option>
				<option value="EUR">EUR</option>
				<option value="FJD">FJD</option>
				<option value="GBP">GBP</option>
				<option value="GTQ">GTQ</option>
				<option value="HKD">HKD</option>
				<option value="HRK">HRK</option>
				<option value="HUF">HUF</option>
				<option value="IDR">IDR</option>
				<option value="ILS">ILS</option>
				<option value="INR">INR</option>
				<option value="ISK">ISK</option>
				<option value="JPY">JPY</option>
				<option value="KRW">KRW</option>
				<option value="KZT">KZT</option>
				<option value="MXN">MXN</option>
				<option value="MYR">MYR</option>
				<option value="NOK">NOK</option>
				<option value="NZD">NZD</option>
				<option value="PAB">PAB</option>
				<option value="PEN">PEN</option>
				<option value="PHP">PHP</option>
				<option value="PKR">PKR</option>
				<option value="PLN">PLN</option>
				<option value="PYG">PYG</option>
				<option value="RON">RON</option>
				<option value="RUB">RUB</option>
				<option value="SAR">SAR</option>
				<option value="SEK">SEK</option>
				<option value="SGD">SGD</option>
				<option value="THB">THB</option>
				<option value="TRY">TRY</option>
				<option value="TWD">TWD</option>
				<option value="UAH">UAH</option>
				<option value="USD" selected>USD</option>
				<option value="UYU">UYU</option>
				<option value="VND">VND</option>
				<option value="ZAR">ZAR</option>
			</select>
			<input type="number" name="" class="numberF" id="output_amount" placeholder="0" />
		</div>
			</div>
		<div class="result">
			<div class="rate" id="rate"></div>
		</div>
	</div>
	<?php $stringa = ob_get_contents();
	ob_end_clean();
	return $stringa;
}