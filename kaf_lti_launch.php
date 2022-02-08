<?php
// ------------------------------
// kaf_lti_launch.php
//    - Launch a KME room by passing LTI parameters to a KAF endpoint.
//    - The room to be joined is specified by a resource ID or event ID.
//    - KME KAF Integration guide: https://github.com/kaltura-vpaas/virtual-meeting-rooms
// ------------------------------

// ------------------------------
// Configuration
// ------------------------------
$launch_url = "https://xxxxxxx.kaf.kaltura.com/virtualEvent/launch";
$key = "xxxxxxx"; // Kaltura Partner ID
$secret = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"; // Kaltura Admin Secret

$launch_data = array(
    //------------------
    // User details
    //------------------
    "user_id" => "xxxxxxxx", // Internal authenticated user id
    "roles" => "Instructor", // User role: 'Instructor', 'Student'
    "lis_person_contact_email_primary" => "pam@school.edu", // User email
    "lis_person_name_given" => "Pam", // User first name
    "lis_person_name_family" => "Little", // User last name

    //------------------
    // Room details
    //------------------
    // Populate either custom_resource_id OR custom_event_id (not both)
    "custom_resource_id" => "xxxxxxx", // Kaltura resource ID
    //"custom_event_id" => "xxxxxxx", // Kaltura event ID
    "resource_link_id" => "0", // LTI identifier that is ignored by KAF; any value is suffice
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
