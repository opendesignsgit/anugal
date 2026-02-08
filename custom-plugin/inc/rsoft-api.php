<?php
add_action('wpcf7_mail_sent', 'sellapi_script');
function sellapi_script($contact_form) {
    // Get form ID
    $form_id = $contact_form->id();

    // Array of form IDs to handle
    $valid_form_ids = ['2710', '651', '652', '1037', '653'];

    // Check if form ID is in the valid forms list
    if (in_array($form_id, $valid_form_ids)) {
        $submission = WPCF7_Submission::get_instance();
        if ($submission) {
            $posted_data = $submission->get_posted_data();

            // Extract the form data
            $your_name = sanitize_text_field($posted_data['your-name']);
            $your_email = sanitize_email($posted_data['your-email']);
            $your_phone = sanitize_text_field($posted_data['your-phone']);

            // Prepare data for API request
            $data = [
                'webformid'     => '23',
                'moduletype'    => 'Basic',
                'company_name'  => 'MPDEV',
                'name'          => $your_name,
                'mobileno'      => $your_phone,
                'email'         => $your_email,
                'projectname'   => '',
                'description'   => '',
                'location'      => '',
                'enquiremedium' => 'Google Ads',
            ];

            // Send data to the API
            $response = wp_remote_post('https://www.thesalezrobot.com/public/api/WebformIntegration', [
                'method'      => 'POST',
                'body'        => $data,
                'timeout'     => 45,
                'headers'     => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);
            // return print_r($response);

            // Check for response error
            if (is_wp_error($response)) {
                error_log('SalezRobot API Error: ' . $response->get_error_message());
            } else {
                // Redirect to thank you page
                wp_safe_redirect(home_url('/thank-you'));
                exit;
            }
        }
    } else {
        // Redirect to thank you page for other forms
        wp_safe_redirect(home_url('/thank-you'));
        exit;
    }
}
//final