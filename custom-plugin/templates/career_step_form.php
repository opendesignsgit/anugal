<!-- Start Step Navigation -->
<div class="d-flex align-items-start mb-3 sm:mb-5 progress-form__tabs" role="tablist">
	<button id="progress-form__tab-1" class="flex-1 px-0 pt-2 progress-form__tabs-item" type="button" role="tab" aria-controls="progress-form__panel-1" aria-selected="true">
		<span class="d-block step" aria-hidden="true">Step 1 <span class="sm:d-none">of 3</span></span>
		General Information
	</button>
	<button id="progress-form__tab-2" class="flex-1 px-0 pt-2 progress-form__tabs-item" type="button" role="tab" aria-controls="progress-form__panel-2" aria-selected="false" tabindex="-1" aria-disabled="true">
		<span class="d-block step" aria-hidden="true">Step 2 <span class="sm:d-none">of 3</span></span>
		Academic Information
	</button>
	<button id="progress-form__tab-3" class="flex-1 px-0 pt-2 progress-form__tabs-item" type="button" role="tab" aria-controls="progress-form__panel-3" aria-selected="false" tabindex="-1" aria-disabled="true">
		<span class="d-block step" aria-hidden="true">Step 3 <span class="sm:d-none">of 3</span></span>
		Job Role
	</button>
</div>
<!-- End Step Navigation -->
<!-- Step 1 Start -->
<section id="progress-form__panel-1" role="tabpanel" aria-labelledby="progress-form__tab-1" tabindex="0">
	<div class="row">
		<div class="col-md-6 form__field">
			[text* full-name placeholder "Full Name (as per university records)*"]
		</div>
		<div class="col-md-6 form__field">
			[text* registeration-number placeholder "Student Registration No.*"]
		</div>
		<div class="col-md-6 form__field">
			[email* email-id placeholder "Personal Email ID*"]
		</div>
		<div class="col-md-6 form__field">
			[tel* mobile-number placeholder "Personal Mobile Number*"]
		</div>
		<div class="col-md-6 form__field">
			[tel* alternate-number placeholder "Alternate Mobile/Landline/Emergency Contact Number*"]
		</div>
		<div class="col-md-6 form__field">
			[date* date-of-birth placeholder "Date of Birth*"]
		</div>
		<div class="col-md-6 form__field">
			[text* place-of-birth placeholder "Place of Birth*"]
		</div>
		<div class="col-md-6 form__field">
			[select* gender first_as_label "Choose Your Gender*" "Female" "Male" "Prefer not to say"]
		</div>
		<div class="col-md-6 form__field">
			[select* blood-group first_as_label "Choose Your Blood Group*" "A+" "A-" "B+" "B-" "O+" "O-" "AB+" "AB-"]
		</div>
		<div class="col-md-6 form__field">
			[select* mother-tongue first_as_label "Choose Your Mother Tongue*" "Hindi" "Bengali" "Telugu" "Marathi" "Tamil" "Kannada" "Odia" "Malayalam" "Sanskrit" "Assamese" "Bodo" "Dogri" "Kashmiri" "Konkani" "Maithili" "Manipuri" "Punjabi" "Nepal" "Santali" "Sindhi" "Urdu"]
		</div>
		<div class="col-md-12">
			<div class="languages">
				<span>Languages known*</span>
				<fieldset class="form__field"><div class="language-english"><span>English : </span>[custom_checkbox* languages-known-english use_label_element "Read" "Write" "Speak" "None"]</div></fieldset>
				<fieldset class="form__field"><div class="language-tamil"><span>Tamil : </span>[custom_checkbox* languages-known-tamil use_label_element "Read" "Write" "Speak" "None"]</div></fieldset>
				<fieldset class="form__field"><div class="language-hindi"><span>Hindi : </span>[custom_checkbox* languages-known-hindi use_label_element "Read" "Write" "Speak" "None"]</div></fieldset>
				<fieldset class="form__field"><div class="language-telugu"><span>Telugu : </span>[custom_checkbox* languages-known-telugu use_label_element "Read" "Write" "Speak" "None"]</div></fieldset>
				<fieldset class="form__field"><div class="language-malayalam"><span>Malayalam : </span>[custom_checkbox* languages-known-malayalam use_label_element "Read" "Write" "Speak" "None"]</div></fieldset>
				<fieldset class="form__field"><div class="language-kanada"><span>Kanada : </span>[custom_checkbox* languages-known-kanada use_label_element "Read" "Write" "Speak" "None"]</div></fieldset>
				<fieldset class="form__field"><div class="language-urdu"><span>Urdu : </span>[custom_checkbox* languages-known-urdu use_label_element "Read" "Write" "Speak" "None"]</div></fieldset>
				<fieldset class="form__field"><div class="language-arabic"><span>Arabic : </span>[custom_checkbox* languages-known-arabic use_label_element "Read" "Write" "Speak" "None"]</div></fieldset>
				<fieldset class="form__field"><div class="language-french"><span>French : </span>[custom_checkbox* languages-known-french use_label_element "Read" "Write" "Speak" "None"]</div></fieldset>
				<fieldset class="form__field"><div class="language-german"><span>German : </span>[custom_checkbox* languages-known-german use_label_element "Read" "Write" "Speak" "None"]</div></fieldset>
				<fieldset class="form__field"><div class="language-italian"><span>Italian : </span>[custom_checkbox* languages-known-italian use_label_element "Read" "Write" "Speak" "None"]</div></fieldset>
				<span>Note : This question requires at least one response per row</span>
			</div>
		</div>
		<div class="col-md-6 form__field">
			<div class="passport-number">
				[text* passport-number placeholder "Mention Your Passport Number*"]
				<span>Note : If you don't have passport please mention NIL</span>
			</div>
		</div>
		<div class="col-md-6 form__field">
			<span>Willing to Travel Onsite* : </span>[radio willing-to-travel use_label_element default:1 "Yes" "No"]
		</div>
		<div class="col-md-6 form__field">
			[select* your-commitment-to-kaartech first_as_label "Choose Your Commitment to Kaartech" "0-2 Years" "2-3 Years" "3-4 Years" "4-5 Years" "5-6 Years" "6-7 Years" "7-8 Years" "8-9 Years" "9-10 Years" "10 + Years"]
		</div>
	</div>
	<div class="d-flex flex-column-reverse sm:flex-row align-items-center justify-center sm:justify-end mt-4 sm:mt-5">
		<button type="button" data-action="next">Next</button>
	</div>
</section>
<!-- Step 1 End -->
<!-- Step 2 Start -->
<section id="progress-form__panel-2" role="tabpanel" aria-labelledby="progress-form__tab-2" tabindex="0" hidden>
	<div class="row">
		<div class="col-md-6 form__field">
			[text* tenth-percentage placeholder "10th Percentage (%)*"]
		</div>
		<div class="col-md-6 form__field">
			[text* tenth-percentage placeholder "10th Percentage (%)*"]
		</div>
		<div class="col-md-6 form__field">
			[text* ug-college-name placeholder "UG College Name*"]
		</div>
		<div class="col-md-6 form__field">
			[select* ug-in first_as_label "Choose Your Degree in UG*" "B.Com" "B.Sc" "BBA" "BA" "BE"]
		</div>
		<div class="col-md-6 form__field">
			[text* ug-branch-stream placeholder "UG Branch / Stream*"]
		</div>
		<div class="col-md-6 form__field">
			[text* ug-percentage placeholder "UG (% or CGPA)*"]
		</div>
		<div class="col-md-6 form__field">
			[text pg-college-name placeholder "PG College Name"]
		</div>
		<div class="col-md-6 form__field">
			[select* pg-in "Choose Your Degree in PG" "M.Com" "MBA" "MSW" "MA"]
		</div>
		<div class="col-md-6 form__field">
			[text pg-branch-stream placeholder "PG Branch / Stream*"]
		</div>
		<div class="col-md-6 form__field">
			[text pg-percentage placeholder "PG (% or CGPA)"]
		</div>
		<div class="col-md-6 form__field">
			<span>Upload your self-attested copies of educational certificates (engineering mark sheets), along with an updated resume(PDF/ JPG less than 6MB)* :</span> [file* resume-marksheet limit:6291456]
		</div>
		<div class="col-md-12">
			<div class="arrears">
				<span>Arrears in UG/PG*</span>
				<fieldset class="form__field"><div class="ug-arrears"><span>UG* : </span>[custom_checkbox* ug-arrears use_label_element "1st semester" "2nd semester" "3rd semester" "4th semester" "5th semester" "6th semester" "7th Semester" "8th Semester" "None"]</div></fieldset>
				<fieldset class="form__field"><div class="pg-arrears"><span>PG : </span>[custom_checkbox pg-arrears use_label_element "1st semester" "2nd semester" "3rd semester" "4th semester" "5th semester" "6th semester" "7th Semester" "8th Semester" "None"]</div></fieldset>
			</div>
		</div>
		<div class="col-md-6 form__field">
			[text* placement-officer-name placeholder "Placement Officer Name*"]
		</div>
		<div class="col-md-6 form__field">
			[tel* placement-officer-phone placeholder "Placement Officer Contact No.*"]
		</div>
		<div class="col-md-6 form__field">
			[email* placement-officer-email placeholder "Placement Officer E-Mail ID*"]
		</div>
	</div>
	<div class="d-flex flex-column-reverse sm:flex-row align-items-center justify-center sm:justify-end mt-4 sm:mt-5">
		<button type="button" class="mt-1 sm:mt-0 button--simple" data-action="prev">Back</button>
		<button type="button" data-action="next">Next</button>
	</div>
</section>
<!-- Step 2 End -->
<!-- Step 3 Start -->
<section id="progress-form__panel-3" role="tabpanel" aria-labelledby="progress-form__tab-3" tabindex="0" hidden>
	<div class="row">
		<div class="col-md-6 form__field">
			[text* linkedin-link placeholder "Provide Link of Your LinkedIn Profile or Blog Website*"]
		</div>
		<div class="col-md-6 form__field">
			[text* courses-done placeholder "List Down Computer Courses / Certifications Done (if no certification, mention none)*"]
		</div>
	</div>
	<div class="d-flex flex-column-reverse sm:flex-row align-items-center justify-center sm:justify-end mt-4 sm:mt-5">
		<button type="button" class="mt-1 sm:mt-0 button--simple" data-action="prev">Back</button>
		[submit "Submit"]
	</div>
</section>
<!-- Step 3 End -->