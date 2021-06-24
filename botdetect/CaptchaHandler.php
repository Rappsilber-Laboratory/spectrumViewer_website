<?php
if (is_callable('session_start')) { session_start(); }


while (ob_get_length()) {
  ob_end_clean();
}
ob_start();
try {
  BDC_HttpHelper::FixEscapedQuerystrings();
  BDC_HttpHelper::CheckForIgnoredRequests();

  // There are several Captcha commands accessible through the Http interface;
  // first we detect which of the valid commands is the current Http request for.
  if (!array_key_exists('get', $_GET) || !BDC_StringHelper::HasValue($_GET['get'])) {
    BDC_HttpHelper::BadRequest('command');
  }
  $commandString = BDC_StringHelper::Normalize($_GET['get']);
  $command = BDC_CaptchaHttpCommand::FromQuerystring($commandString);
  switch ($command) {
    case BDC_CaptchaHttpCommand::GetImage:
      GetImage();
      break;
    case BDC_CaptchaHttpCommand::GetSound:
      GetSound();
      break;
    case BDC_CaptchaHttpCommand::GetValidationResult:
      GetValidationResult();
      break;
    case BDC_CaptchaHttpCommand::GetScriptInclude:
      GetScriptInclude();
      break;
    case BDC_CaptchaHttpCommand::GetP:
      GetP();
      break;
    default:
      BDC_HttpHelper::BadRequest('command');
      break;
  }

} catch (Exception $e) {
  header('Content-Type: text/plain');
  echo $e->getMessage();
}
ob_end_flush();
exit;



// Returns the Captcha image binary data
function GetImage() {

  // saved data for the specified Captcha object in the application
  $captcha = GetCaptchaObject();
  if (is_null($captcha)) {
    BDC_HttpHelper::BadRequest('Captcha doesn\'t exist');
  }
  
  // identifier of the particular Captcha object instance
  $instanceId = GetInstanceId();
  if (is_null($instanceId)) {
    BDC_HttpHelper::BadRequest('Instance doesn\'t exist');
  }


  // image generation invalidates sound cache, if any  
  ClearSoundData($instanceId); 

  // response headers
  BDC_HttpHelper::DisallowCache();

  // MIME type
  $mimeType = $captcha->ImageMimeType;
  header("Content-Type: {$mimeType}");

  // we don't support content chunking, since image files
  // are regenerated randomly on each request
  header('Accept-Ranges: none');

  // disallow audio file search engine indexing
  header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet');

  // image generation
  $rawImage = $captcha->CaptchaBase->GetImage($instanceId);
  $captcha->CaptchaBase->SaveCodeCollection(); // record generated Captcha code for validation
  session_write_close();

  // output image bytes
  $length = strlen($rawImage);
  header("Content-Length: {$length}");
  echo $rawImage;
  
}



function GetSound() {

  $captcha = GetCaptchaObject();
  if (is_null($captcha)) {
    BDC_HttpHelper::BadRequest('Captcha doesn\'t exist');
  }
  
  if (!$captcha->SoundEnabled) { // sound requests can be disabled with this config switch / instance property
    BDC_HttpHelper::BadRequest('Sound disabled');
  }

  $instanceId = GetInstanceId();
  if (is_null($instanceId)) {
    BDC_HttpHelper::BadRequest('Instance doesn\'t exist');
  }

  $soundBytes = GetSoundData($captcha, $instanceId);

  if (is_callable('session_write_close')) { session_write_close(); }
  
  if (is_null($soundBytes)) {
    BDC_HttpHelper::BadRequest('Please reload the form page before requesting another Captcha sound');
    exit;
  }
  
  $totalSize = strlen($soundBytes);
  
  // response headers
  BDC_HttpHelper::SmartDisallowCache();
  
  $mimeType = $captcha->SoundMimeType;
  header("Content-Type: {$mimeType}");

  header('Content-Transfer-Encoding: binary');

  if (!array_key_exists('d', $_GET)) { // javascript player not used, we send the file directly as a download
    $downloadId = BDC_CryptoHelper::GenerateGuid();
    header("Content-Disposition: attachment; filename=captcha_{$downloadId}.wav");
  }
  
  header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet'); // disallow audio file search engine indexing
  
  
  if (DetectIosRangeRequest()) { // iPhone/iPad sound issues workaround: chunked response for iOS clients
    // sound byte subset
    $range = GetSoundByteRange();
    $rangeStart = $range['start'];
    $rangeEnd = $range['end'];
    $rangeSize = $rangeEnd - $rangeStart + 1;

    // initial iOS 6.0.1 testing; leaving as fallback since we can't be sure it won't happen again:
    // we depend on observed behavior of invalid range requests to detect
    // end of sound playback, cleanup and tell AppleCoreMedia to stop requesting
    // invalid "bytes=rangeEnd-rangeEnd" ranges in an infinite(?) loop
    if ($rangeStart == $rangeEnd || $rangeEnd > $totalSize) {
      BDC_HttpHelper::BadRequest('invalid byte range');
    }
    
    $rangeBytes = substr($soundBytes, $rangeStart, $rangeSize);
    
    // partial content response with the requested byte range
    header('HTTP/1.1 206 Partial Content');
    header('Accept-Ranges: bytes');
    header("Content-Length: {$rangeSize}");
    header("Content-Range: bytes {$rangeStart}-{$rangeEnd}/{$totalSize}");
    echo $rangeBytes; // chrome needs this kind of response to be able to replay Html5 audio
  } else if (DetectFakeRangeRequest()) {
    header('Accept-Ranges: bytes');
    header("Content-Length: {$totalSize}");
    $end = $totalSize - 1;
    header("Content-Range: bytes 0-{$end}/{$totalSize}");
    echo $soundBytes;
  } else { // regular sound request
    header('Accept-Ranges: none');
    header("Content-Length: {$totalSize}");
    echo $soundBytes;
  }
}


function GetSoundData($p_Captcha, $p_InstanceId) {

  $shouldCache = (
    ($p_Captcha->SoundRegenerationMode == SoundRegenerationMode::None) || // no sound regeneration allowed, so we must cache the first and only generated sound
    DetectIosRangeRequest() // keep the same Captcha sound across all chunked iOS requests
  );
  
  if ($shouldCache) {
    $loaded = LoadSoundData($p_InstanceId);
    if (!is_null($loaded)) {
      return $loaded;
    }
  } else {
    ClearSoundData($p_InstanceId);
  }
  
  $soundBytes = GenerateSoundData($p_Captcha, $p_InstanceId);
  if ($shouldCache) {
    SaveSoundData($p_InstanceId, $soundBytes);
  }
  return $soundBytes;
}

function GenerateSoundData($p_Captcha, $p_InstanceId) {
  $rawSound = $p_Captcha->CaptchaBase->GetSound($p_InstanceId);
  $p_Captcha->CaptchaBase->SaveCodeCollection(); // always record sound generation count
  return $rawSound;
}

function SaveSoundData($p_InstanceId, $p_SoundBytes) {
  BDC_Persistence_Save("BDC_Cached_SoundData_" . $p_InstanceId, $p_SoundBytes);
}

function LoadSoundData($p_InstanceId) {
   return BDC_Persistence_Load("BDC_Cached_SoundData_" . $p_InstanceId);
}

function ClearSoundData($p_InstanceId) {
  BDC_Persistence_Clear("BDC_Cached_SoundData_" . $p_InstanceId);
}


// Instead of relying on unreliable user agent checks, we detect the iOS sound
// requests by the Http headers they will always contain
function DetectIosRangeRequest() {
  if (array_key_exists('HTTP_RANGE', $_SERVER) 
        && BDC_StringHelper::HasValue($_SERVER['HTTP_RANGE'])) {
      
    // Safari on MacOS and all browsers on <= iOS 10.x
    if (array_key_exists('HTTP_X_PLAYBACK_SESSION_ID', $_SERVER)
          && BDC_StringHelper::HasValue($_SERVER['HTTP_X_PLAYBACK_SESSION_ID'])) {
      return true;
    }
    
    $userAgent = array_key_exists('HTTP_USER_AGENT', $_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : null;

    // all browsers on iOS 11.x and later
    if (BDC_StringHelper::HasValue($userAgent)) {
      $userAgentLC = BDC_StringHelper::Lowercase($userAgent);
      if (BDC_StringHelper::Contains($userAgentLC, "like mac os") || BDC_StringHelper::Contains($userAgentLC, "like macos")) {
        return true;
      }
    }
  }

  return false;
}

function GetSoundByteRange() {
  // chunked requests must include the desired byte range
  $rangeStr = $_SERVER['HTTP_RANGE'];
  if (!BDC_StringHelper::HasValue($rangeStr)) {
    return;
  }

  $matches = array();
  preg_match_all('/bytes=([0-9]+)-([0-9]+)/', $rangeStr, $matches);
  return array(
    'start' => (int) $matches[1][0],
    'end'   => (int) $matches[2][0]
  );
}

function DetectFakeRangeRequest() {
  $detected = false;
  if (array_key_exists('HTTP_RANGE', $_SERVER)) {
    $rangeStr = $_SERVER['HTTP_RANGE'];
    if (BDC_StringHelper::HasValue($rangeStr) &&
        preg_match('/bytes=0-$/', $rangeStr)) {
      $detected = true;
    }
  }
  return $detected;
}


// Used for client-side validation, returns Captcha validation result as JSON
function GetValidationResult() {

  // saved data for the specified Captcha object in the application
  $captcha = GetCaptchaObject();
  if (is_null($captcha)) {
    BDC_HttpHelper::BadRequest('captcha');
  }

  // identifier of the particular Captcha object instance
  $instanceId = GetInstanceId();
  if (is_null($instanceId)) {
    BDC_HttpHelper::BadRequest('instance');
  }

  // code to validate
  $userInput = GetUserInput();

  // response MIME type & headers
  header('Content-Type: text/javascript');
  header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet');

  // JSON-encoded validation result
  $result = false;
   if (isset($userInput) && (isset($instanceId))) {
    $result = $captcha->AjaxValidate($userInput, $instanceId);
    $captcha->CaptchaBase->SaveCodeCollection();
  }
  session_write_close();
  
  $resultJson = GetJsonValidationResult($result);
  echo $resultJson;
}


function GetScriptInclude() {
  // saved data for the specified Captcha object in the application
  $captcha = GetCaptchaObject();
  if (is_null($captcha)) {
    BDC_HttpHelper::BadRequest('captcha');
  }

  // identifier of the particular Captcha object instance
  $instanceId = GetInstanceId();
  if (is_null($instanceId)) {
    BDC_HttpHelper::BadRequest('instance');
  }

  // response MIME type & headers
  header('Content-Type: text/javascript');
  header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet');

  // 1. load BotDetect script
  $script = GetWebResource(BDC_URL_ROOT . 'bdc-traditional-api-script-include.js', 'text/javascript', false);

  // 2. load BotDetect Init script
  $script .= BDC_CaptchaScriptsHelper::GetInitScriptMarkup($captcha, $instanceId);

  // add remote scripts if enabled
  if ($captcha->RemoteScriptEnabled) {
    $script .= "\r\n";
    $script .= BDC_CaptchaScriptsHelper::GetRemoteScript($captcha);
  }

  echo $script;
}

function GetCaptchaId() {
  $captchaId = $_GET['c'];
  
  if (!BDC_StringHelper::HasValue($captchaId)) {
    return null;
  }
  
  if (1 !== preg_match(BDC_CaptchaBase::VALID_CAPTCHA_ID, $captchaId)) {
    return null;
  }
  
  return $captchaId;
}

// gets Captcha instance according to the CaptchaId passed in querystring
function GetCaptchaObject() {
  $captchaId = GetCaptchaId();
  
  if (!BDC_StringHelper::HasValue($captchaId)) {
    BDC_HttpHelper::BadRequest('Invalid captcha id.');
  }

  $captchaInstanceId = BDC_StringHelper::Normalize($_GET['t']);
  if (!BDC_StringHelper::HasValue($captchaInstanceId) ||
      !BDC_CaptchaBase::IsValidInstanceId($captchaInstanceId)) {
    return;
  }

  $captcha = new Captcha($captchaId, $captchaInstanceId);
  return $captcha;
}


// extract the exact Captcha code instance referenced by the request
function GetInstanceId() {
  $instanceId = $_GET['t'];
  if (!BDC_StringHelper::HasValue($instanceId) ||
      !BDC_CaptchaBase::IsValidInstanceId($instanceId)) {
    return;
  }
  return $instanceId;
}


// extract the user input Captcha code string from the Ajax validation request
function GetUserInput() {
  $input = null;

  if (isset($_GET['i'])) {
    // BotDetect built-in Ajax Captcha validation
    $input = BDC_StringHelper::Normalize($_GET['i']);
  } else {
    // jQuery validation support, the input key may be just about anything,
    // so we have to loop through fields and take the first unrecognized one
    $recognized = array('get', 'c', 't', 'd');
    foreach($_GET as $key => $value) {
      if (!in_array($key, $recognized)) {
        $input = $value;
        break;
      }
    }
  }

  return $input;
}

// encodes the Captcha validation result in a simple JSON wrapper
function GetJsonValidationResult($p_Result) {
  $resultStr = ($p_Result ? 'true': 'false');
  return $resultStr;
}

function GetWebResource($p_Resource, $p_MimeType, $hasEtag = true) {
  header("Content-Type: $p_MimeType");
  if ($hasEtag) {
    BDC_HttpHelper::AllowEtagCache($p_Resource);
  }

  return file_get_contents($p_Resource);
}

function GetP() {
  $captcha = GetCaptchaObject();
  if (is_null($captcha)) {
    BDC_HttpHelper::BadRequest('captcha');
  }
  
  $instanceId = GetInstanceId();
  
  if (is_null($instanceId)) {
    BDC_HttpHelper::BadRequest('instance');
  }
  
  // create new one
  $p = $captcha->GenPw($instanceId);
  $captcha->SavePw($captcha);
  
  // response data
  $response = "{\"sp\":\"{$p->GetSP()}\",\"hs\":\"{$p->GetHs()}\"}";
  
  // response MIME type & headers
  header('Content-Type: application/json');
  header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet');
  BDC_HttpHelper::SmartDisallowCache();
  
  echo $response;
}

?>