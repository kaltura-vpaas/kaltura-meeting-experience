<?php
// ------------------------------
// newrow_lti_launch.php
//    - Launch a KME room by passing LTI parameters to a newrow endpoint.
//    - The room to be joined is specified by a category ID or resource ID.
//    - Category ID can be a from a Channel in KMS (a channel's KME room) or a KAF Media Gallery.
//    - Resource ID documenation: https://github.com/kaltura-vpaas/virtual-meeting-rooms
// ------------------------------

// ------------------------------
// Configuration
// ------------------------------
$launch_url = "https://smart.newrow.com/backend/lti/course";
$key = "xxxxxxx";     // KME key - to be provided by Kaltura admin (via NAP)
$secret = "xxxxxxxx"; // KME secret - to be provided by Kaltura admin (via NAP)

$launch_data = array(
    // Consumer identifier (Vendor name like: desire2learn, moodle, etc.)
    "tool_consumer_info_product_family_code" => "kaltura",

    //------------------
    // User details
    //------------------
    "user_id" => "some.email@domain.com", // Internal authenticated user id in your system
    "roles" => "Instructor", // User role ('Administrator','Instructor', 'Student')
    "lis_person_contact_email_primary" => "some.email@domain.com", // User email
    "lis_person_name_given" => "John", // User first name
    "lis_person_name_family" => "Doe", // User last name

    //------------------
    // Room details
    //------------------
    "context_id" => "xxxxxxxxx", // Internal Room identifier - category/channel Id or resource Id
    "context_title" => "My Room Name", // room name
    "custom_kaltura_room_type" => "channel", // 'channel' or 'resource'; this field may not matter
    "custom_rs_user_lang" => "en-VE", // specify room's language to Virtual Events English (this is optional or can be set to any language)
    "custom_company_logo" => "https://corp.kaltura.com/wp-content/uploads/2020/07/All_Logos_Kaltura_Logo_Vertical_ColorSun_BlackText.jpg" // room logo (optional)
);

// ------------------------------ 
// OAUTH CONFIGURATION
// ------------------------------
$now = new DateTime();
$launch_data["lti_version"] = "LTI-1p0";

# Basic LTI uses OAuth to sign requests
# OAuth Core 1.0 spec: http://oauth.net/core/1.0/
$launch_data["oauth_callback"] = "about:blank";
$launch_data["oauth_consumer_key"] = $key;
$launch_data["oauth_version"] = "1.0";
$launch_data["oauth_nonce"] = uniqid('', true);
$launch_data["oauth_timestamp"] = $now->getTimestamp();
$launch_data["oauth_signature_method"] = "HMAC-SHA1";

# In OAuth, request parameters must be sorted by name
$launch_data_keys = array_keys($launch_data);
sort($launch_data_keys);
$launch_params = array();
foreach ($launch_data_keys as $key) {
    array_push($launch_params, $key . "=" . rawurlencode($launch_data[$key]));
}
$base_string = "POST&" . urlencode($launch_url) . "&" . rawurlencode(implode("&", $launch_params));
$secret = urlencode($secret) . "&";
$signature = base64_encode(hash_hmac("sha1", $base_string, $secret, true));
?>

<form id="ltiLaunchForm" name="ltiLaunchForm" method="POST" action="<?php printf($launch_url); ?>">
    <?php foreach ($launch_data as $k => $v ) { ?>
        <input type="hidden" name="<?php echo $k ?>" value="<?php echo $v ?>">
    <?php } ?>
    <input type="hidden" name="oauth_signature" value="<?php echo $signature ?>"> 
</form>
<script language="javascript"> document.getElementById("ltiLaunchForm").style.display = "none"; 
document.ltiLaunchForm.submit();

</script>
