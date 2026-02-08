<?php
add_action('wp_enqueue_scripts', 'international_tel');
function international_tel(){
    //if(!is_front_page()){
        //wp_enqueue_script('Validate', '/wp-content/themes/salient/custom-files/validation.js', array(), 1.5, true);
        wp_enqueue_script('intlTelInput', 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/11.0.14/js/intlTelInput.js', true);
        wp_enqueue_style('intlTelInputCss', 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/11.0.14/css/intlTelInput.css');
        // wp_enqueue_script('utiljs', 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/11.0.14/js/utils.js', true);
        // wp_enqueue_script('mask', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.3.4/jquery.inputmask.bundle.min.js', true);
    //}
}

function getLocationInfoByIp(){
    $client = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote = @$_SERVER['REMOTE_ADDR'];
    $result = array('country' => '', 'city' => '');
    if (filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    } else {
        $ip = $remote;
    }
    $ip_data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
    if ($ip_data && $ip_data->geoplugin_countryName != null) {
        $result['country'] = $ip_data->geoplugin_countryCode;
        $result['city'] = $ip_data->geoplugin_city;
    }
    return $result;
}

add_action( 'wp_footer' , 'custom_international_tel' );
function custom_international_tel(){
$cCode = getLocationInfoByIp();    
?>
<style>
input.phone {
    margin-left: 90px !important;
}
</style>
<script type="text/javascript">
    if(true){
        jQuery( function( $ ) {
            $(".phone").intlTelInput({
                initialCountry: "IN",
                autoHideDialCode: true,
                autoPlaceholder: "polite",
                nationalMode: false,
                separateDialCode: true,
                // geoIpLookup: function (callback) {
                //     $.get('https://ipinfo.io', function () {}, "jsonp").always(function (resp) {
                //         var countryCode = (resp && resp.country) ? resp.country : "";
                //         callback(countryCode);
                //     });
                // },
                // utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/11.0.14/js/utils.js",
            });
            // var mask = $(".phone").attr('placeholder').replace(/[0-9]/g, 9);
            // $('.phone').inputmask(mask);
            // $(".phone").on("countrychange", function (e, countryData) {
            //     $(".phone").val('');
            //     var mask = $(".phone").attr('placeholder').replace(/[0-9]/g, 9);
            //     console.log(mask);
            //     $(".phone").inputmask(mask);
            // });
        });
    }
</script>
<?php }
