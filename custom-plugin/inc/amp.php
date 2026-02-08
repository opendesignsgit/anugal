<?php
add_shortcode('is_amp','is_amp');
function is_amp( $atts ){
	ob_start();
	echo is_amp_endpoint();
	$stringa = ob_get_contents();
	ob_end_clean();
	return $stringa;
}
add_shortcode('industries_slider','industries_slider');
function industries_slider(){ 
if(ampforwp_is_amp_endpoint()){ 
ob_start(); ?>
	<amp-carousel width="300" height="600" layout="responsive" type="slides" role="region" aria-label="Basic carousel" autoplay delay="6000">
		<div class="tab-con-item stab1">
			<div class="LeftService">
				<h5>Industries</h5>
				<h2>Discrete Industries</h2>
				<p>Let the Discrete Industry Owners leverage on their Industry-specific intelligent solutions, to gain deeper insights into client requirements, increase operational efficiencies, and personalize customer interaction. </p>
				<p>
					<a class="epmoreBtn" href="#">Willing to know how?</a>
				</p>
			</div>
		</div>
		<div class="tab-con-item stab2" style="">
			<div class="LeftService">
				<h5>Industries</h5>
				<h2>Energy &amp; Natural <br> Resources </h2>
				<p>As the Energy &amp; Natural Resources industry continues to adopt digital advancements like cloud computing, predictive analytics, and artificial intelligence, they are pioneering the industry. Our experts can channelize your transformation with enhanced efficiency, reduced cost, and reliable services. </p>
				<p>
					<a class="epmoreBtn" href="#">Willing to know how?</a>
				</p>
			</div>
		</div>
		<div class="tab-con-item stab3">
			<div class="LeftService">
				<h5>Industries</h5>
				<h2>Services </h2>
				<p>Modern customers expect modern solution; with our guided digital transformation, get to experience better and faster customer services, personalized customer experiences, reduced operational costs, and increased efficiency for your Services Industry. </p>
				<p>
					<a class="epmoreBtn" href="#">Willing to know how? </a>
				</p>
			</div>
		</div>
		<div class="tab-con-item stab4">
			<div class="LeftService">
				<h5>Industries</h5>
				<h2>Consumer </h2>
				<p>Together let us integrate digital technology into all aspects of your business. With the rise of e-commerce, Digital Transformation has become essential for consumer industries to remain competitive and meet the demands of their customers. </p>
				<p>
					<a class="epmoreBtn" href="#">Willing to know how?</a>
				</p>
			</div>
		</div>
		<div class="tab-con-item stab5">
			<div class="LeftService">
				<h5>Industries</h5>
				<h2>Public <br> Services </h2>
				<p>Focus to streamlining your operations, improving efficiency, and ensuring data integrity to better serve your citizens securely. Let us develop a highly responsive framework with improved agility, optimized processes, and improved customer experience together! </p>
				<p>
					<a class="epmoreBtn" href="#">Willing to know how?</a>
				</p>
			</div>
		</div>
		<div class="tab-con-item stab6">
			<div class="LeftService">
				<h5>Industries</h5>
				<h2>Financial <br> Services </h2>
				<p>In response to the increasing demand for convenience, transparency, and cost efficiency from customers, the finance sector has started to adapt future-proof technologies. We are here to help you digitally streamline your operations beyond the existing digital channels. </p>
				<p>
					<a class="epmoreBtn" href="#">Willing to know how? </a>
				</p>
			</div>
		</div>
	</amp-carousel>
<?php
$stringa = ob_get_contents();
ob_end_clean();
return $stringa; 
}}