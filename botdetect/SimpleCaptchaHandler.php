<?php

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
  $command = BDC_SimpleCaptchaHttpCommand::FromQuerystring($commandString);

  switch ($command) {
    case BDC_SimpleCaptchaHttpCommand::GetImage:
      GetImage();
      break;
    case BDC_SimpleCaptchaHttpCommand::GetBase64ImageString:
      GetBase64ImageString();
      break;
    case BDC_SimpleCaptchaHttpCommand::GetSound:
      GetSound();
      break;
    case BDC_SimpleCaptchaHttpCommand::GetHtml:
      GetHtml();
      break;
    case BDC_SimpleCaptchaHttpCommand::GetValidationResult:
      GetValidationResult();
      break;

    // Sound icon
    case BDC_SimpleCaptchaHttpCommand::GetSoundIcon:
      GetSoundIcon();
      break;
    case BDC_SimpleCaptchaHttpCommand::GetSoundSmallIcon:
      GetSmallSoundIcon();
      break;
    case BDC_SimpleCaptchaHttpCommand::GetSoundDisabledIcon:
      GetDisabledSoundIcon();
      break;
    case BDC_SimpleCaptchaHttpCommand::GetSoundSmallDisabledIcon:
      GetSmallDisabledSoundIcon();
      break;

    // Reload icon
    case BDC_SimpleCaptchaHttpCommand::GetReloadIcon:
      GetReloadIcon();
      break;
    case BDC_SimpleCaptchaHttpCommand::GetReloadSmallIcon:
      GetSmallReloadIcon();
      break;
    case BDC_SimpleCaptchaHttpCommand::GetReloadDisabledIcon:
      GetDisabledReloadIcon();
      break;
    case BDC_SimpleCaptchaHttpCommand::GetReloadSmallDisabledIcon:
      GetSmallDisabledReloadIcon();
      break;

    // css, js
    case BDC_SimpleCaptchaHttpCommand::GetScriptInclude:
      GetScriptInclude();
      break;
    case BDC_SimpleCaptchaHttpCommand::GetLayoutStyleSheet:
      GetLayoutStyleSheet();
      break;
    case BDC_SimpleCaptchaHttpCommand::GetP:
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
  BDC_CaptchaEndpointConfig::EnableCors();
  BDC_CaptchaEndpointConfig::AddUserDefinedHttpHeaders();
  
  // // authenticate client-side request
  // $corsAuth = new CorsAuth();
  // if (!$corsAuth->IsClientAllowed()) {
  //   BDC_HttpHelper::BadRequest($corsAuth->GetFrontEnd() . " is not an allowed front-end");
  // }

  // saved data for the specified Captcha object in the application
  $captcha = GetCaptchaObject();

  if (is_null($captcha)) {
    BDC_HttpHelper::BadRequest('Captcha doesn\'t exist');
  }

  $rawImage = GetImageData($captcha);
  if($rawImage == null) {
    return null;
  }
  
  // MIME type
  $imageType = ImageFormat::GetName($captcha->ImageFormat);
  $imageType = strtolower($imageType[0]);
  $mimeType = "image/" . $imageType;
  header("Content-Type: {$mimeType}");

  // output image bytes
  $length = strlen($rawImage);
  header("Content-Length: {$length}");
  echo $rawImage;
}

function GetBase64ImageString() {
  BDC_CaptchaEndpointConfig::EnableCors();
  BDC_CaptchaEndpointConfig::AddUserDefinedHttpHeaders();
  
  // // authenticate client-side request
  // $corsAuth = new CorsAuth();
  // if (!$corsAuth->IsClientAllowed()) {
  //   BDC_HttpHelper::BadRequest($corsAuth->GetFrontEnd() . " is not an allowed front-end");
  // }
  
  // saved data for the specified Captcha object in the application
  $captcha = GetCaptchaObject();

  if (is_null($captcha)) {
    BDC_HttpHelper::BadRequest('Captcha doesn\'t exist');
  }

  // MIME type
  $imageType = ImageFormat::GetName($captcha->ImageFormat);
  $imageType = strtolower($imageType[0]);
  $mimeType = "image/" . $imageType;

  $rawImage = GetImageData($captcha);

  echo sprintf('data:%s;base64,%s', $mimeType, base64_encode($rawImage));
}

function GetImageData($p_Captcha) {
  // identifier of the particular Captcha object instance
  $captchaId = GetCaptchaId();
  if (is_null($captchaId)) {
    BDC_HttpHelper::BadRequest('Captcha Id doesn\'t exist');
  }

  if (IsObviousBotRequest($p_Captcha)) {
    return;
  }

  // image generation invalidates sound cache, if any
  ClearSoundData($p_Captcha, $captchaId);

  // response headers
  BDC_HttpHelper::DisallowCache();

  // we don't support content chunking, since image files
  // are regenerated randomly on each request
  header('Accept-Ranges: none');

  // disallow audio file search engine indexing
  header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet');

  // image generation
  $rawImage = $p_Captcha->CaptchaBase->GetImage($captchaId);
  $p_Captcha->SaveCode($captchaId, $p_Captcha->CaptchaBase->Code); // record generated Captcha code for validation

  return $rawImage;
}


function GetSound() {
  BDC_CaptchaEndpointConfig::EnableCors();
  BDC_CaptchaEndpointConfig::AddUserDefinedHttpHeaders();
  
  // // authenticate client-side request
  // $corsAuth = new CorsAuth();
  // if (!$corsAuth->IsClientAllowed()) {
  //   BDC_HttpHelper::BadRequest($corsAuth->GetFrontEnd() . " is not an allowed front-end");
  // }

  $captcha = GetCaptchaObject();
  if (is_null($captcha)) {
    BDC_HttpHelper::BadRequest('Captcha doesn\'t exist');
  }

  if (!$captcha->SoundEnabled) { // sound requests can be disabled with this config switch / instance property
    BDC_HttpHelper::BadRequest('Sound disabled');
  }

  $captchaId = GetCaptchaId();
  if (is_null($captchaId)) {
    BDC_HttpHelper::BadRequest('Captcha Id doesn\'t exist');
  }

  if (IsObviousBotRequest($captcha)) {
    return;
  }
  
  $soundBytes = GetSoundData($captcha, $captchaId);

  if (is_null($soundBytes)) {
    BDC_HttpHelper::BadRequest('Please reload the form page before requesting another Captcha sound');
    exit;
  }

  $totalSize = strlen($soundBytes);

  // response headers
  BDC_HttpHelper::SmartDisallowCache();

  header("Content-Type: audio/x-wav");

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


function GetSoundData($p_Captcha, $p_CaptchaId) {

  $shouldCache = (
    ($p_Captcha->SoundRegenerationMode == SoundRegenerationMode::None) || // no sound regeneration allowed, so we must cache the first and only generated sound
    DetectIosRangeRequest() // keep the same Captcha sound across all chunked iOS requests
  );

  if ($shouldCache) {
    $loaded = LoadSoundData($p_Captcha, $p_CaptchaId);
    if (!is_null($loaded)) {
      return $loaded;
    }
  } else {
    ClearSoundData($p_Captcha, $p_CaptchaId);
  }

  $soundBytes = GenerateSoundData($p_Captcha, $p_CaptchaId);

  if ($shouldCache) {
    SaveSoundData($p_Captcha, $p_CaptchaId, $soundBytes);
  }
  return $soundBytes;
}

function GenerateSoundData($p_Captcha, $p_CaptchaId) {
  $rawSound = $p_Captcha->get_CaptchaBase()->GetSound($p_CaptchaId);
  $p_Captcha->SaveCode($p_CaptchaId, $p_Captcha->CaptchaBase->Code); // always record sound generation count
  return $rawSound;
}

function SaveSoundData($p_Captcha, $p_CaptchaId, $p_SoundBytes) {
  $p_Captcha->get_CaptchaPersistence()->GetPersistenceProvider()->Save("BDC_Cached_SoundData_" . $p_CaptchaId, $p_SoundBytes);
}

function LoadSoundData($p_Captcha, $p_CaptchaId) {
  $soundBytes = $p_Captcha->get_CaptchaPersistence()->GetPersistenceProvider()->Load("BDC_Cached_SoundData_" . $p_CaptchaId);
  return $soundBytes;
}

function ClearSoundData($p_Captcha, $p_CaptchaId) {
  $p_Captcha->get_CaptchaPersistence()->GetPersistenceProvider()->Remove("BDC_Cached_SoundData_" . $p_CaptchaId);
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
    'start' => (int)$matches[1][0],
    'end' => (int)$matches[2][0]
  );
}

function DetectFakeRangeRequest() {
  $detected = false;
  if (array_key_exists('HTTP_RANGE', $_SERVER)) {
    $rangeStr = $_SERVER['HTTP_RANGE'];
    if (BDC_StringHelper::HasValue($rangeStr) &&
        preg_match('/bytes=0-$/', $rangeStr)
    ) {
      $detected = true;
    }
  }
  return $detected;
}

function GetHtml() {
  // $corsAuth = new CorsAuth();
  // if (!$corsAuth->IsClientAllowed()) {
  //   BDC_HttpHelper::BadRequest($corsAuth->GetFrontEnd() . " is not an allowed front-end");
  // }
  $captcha = null;
  try {
    BDC_CaptchaEndpointConfig::EnableCors();
    BDC_CaptchaEndpointConfig::AddUserDefinedHttpHeaders();
    BDC_CaptchaEndpointConfig::AddContentTypeTextHtmlCharsetHeader();

    $captcha = GetCaptchaObject();
  } catch (CaptchaStyleNameNotFoundException $ex) {
    BDC_HttpHelper::BadRequest($ex->getMessage());
  } catch (Exception $ex) {
    BDC_HttpHelper::InternalServerError($ex->getMessage());
  }

  if (is_null($captcha)) {
    BDC_HttpHelper::BadRequest('Captcha doesn\'t exist');
  }

  $html = "<div>" . $captcha->Html() . "</div>";
  echo $html;
}

// Used for client-side validation, returns Captcha validation result as JSON
function GetValidationResult() {
  BDC_CaptchaEndpointConfig::EnableCors();
  BDC_CaptchaEndpointConfig::AddUserDefinedHttpHeaders();
  
  // // authenticate client-side request
  // $corsAuth = new CorsAuth();
  // if (!$corsAuth->IsClientAllowed()) {
  //   BDC_HttpHelper::BadRequest($corsAuth->GetFrontEnd() . " is not an allowed front-end");
  //   return null;
  // }

  // saved data for the specified Captcha object in the application
  $captcha = GetCaptchaObject();
  if (is_null($captcha)) {
    BDC_HttpHelper::BadRequest('captcha');
  }

  // identifier of the particular Captcha object instance
  $captchaId = GetCaptchaId();
  if (is_null($captchaId)) {
    BDC_HttpHelper::BadRequest('instance');
  }

  // code to validate
  $userInput = GetUserInput();

  // response MIME type & headers
  header('Content-Type: application/json');
  header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet');

  // JSON-encoded validation result
  $result = false;
  if (isset($userInput) && (isset($captchaId))) {
    $result = $captcha->AjaxValidate($userInput, $captchaId);
  }

  $resultJson = GetJsonValidationResult($result);
  echo $resultJson;
}

// Get Reload Icon group
function GetSoundIcon() {
  $filePath = BDC_URL_ROOT . 'bdc-sound-icon.gif';
  echo GetWebResource($filePath, 'image/gif');
}

function GetSmallSoundIcon() {
  $filePath = BDC_URL_ROOT . 'bdc-sound-small-icon.gif';
  echo GetWebResource($filePath, 'image/gif');
}

function GetDisabledSoundIcon() {
  $filePath = BDC_URL_ROOT . 'bdc-sound-disabled-icon.gif';
  echo GetWebResource($filePath, 'image/gif');
}

function GetSmallDisabledSoundIcon() {
  $filePath = BDC_URL_ROOT . 'bdc-sound-small-disabled-icon.gif';
  echo GetWebResource($filePath, 'image/gif');
}

// Get Reload Icon group
function GetReloadIcon() {
  $filePath = BDC_URL_ROOT . 'bdc-reload-icon.gif';
  echo GetWebResource($filePath, 'image/gif');
}

function GetSmallReloadIcon() {
  $filePath = BDC_URL_ROOT . 'bdc-reload-small-icon.gif';
  echo GetWebResource($filePath, 'image/gif');
}

function GetDisabledReloadIcon() {
  $filePath = BDC_URL_ROOT . 'bdc-reload-disabled-icon.gif';
  echo GetWebResource($filePath, 'image/gif');
}

function GetSmallDisabledReloadIcon() {
  $filePath = BDC_URL_ROOT . 'bdc-reload-small-disabled-icon.gif';
  echo GetWebResource($filePath, 'image/gif');
}

function GetLayoutStyleSheet() {
  $filePath = BDC_URL_ROOT . 'bdc-layout-stylesheet.css';
  echo GetWebResource($filePath, 'text/css');
}

function GetScriptInclude() {
  BDC_CaptchaEndpointConfig::EnableCors();
  BDC_CaptchaEndpointConfig::AddUserDefinedHttpHeaders();

  // saved data for the specified Captcha object in the application
  $captcha = GetCaptchaObject();

  if (is_null($captcha)) {
    BDC_HttpHelper::BadRequest('Captcha doesn\'t exist');
  }
  // identifier of the particular Captcha object instance
  $captchaId = GetCaptchaId();
  if (is_null($captchaId)) {
    BDC_HttpHelper::BadRequest('Captcha Id doesn\'t exist');
  }

  // response MIME type & headers
  header('Content-Type: text/javascript');
  header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet');

  // 1. load BotDetect script
  $script = GetWebResource(BDC_URL_ROOT . 'bdc-simple-api-script-include.js', 'text/javascript', false);

  // 2. load BotDetect Init script
  $script .= BDC_SimpleCaptchaScriptsHelper::GetInitScriptMarkup($captcha, $captchaId);

  // add remote scripts if enabled
  if ($captcha->RemoteScriptEnabled) {
    $script .= "\r\n";
    $script .= BDC_SimpleCaptchaScriptsHelper::GetRemoteScript($captcha, getClientSideFramework());
  }

  echo $script;
}

function GetCaptchaStyleName() {
  $captchaStyleName = $_GET['c'];

  if (!BDC_StringHelper::HasValue($captchaStyleName)) {
    return null;
  }
  
  if (1 !== preg_match(BDC_SimpleCaptchaBase::VALID_CAPTCHA_STYLE_NAME, $captchaStyleName)) {
    return null;
  }
  
  return $captchaStyleName;
}
    
// gets Captcha instance according to the CaptchaId passed in querystring
function GetCaptchaObject() {
  $captchaStyleName = GetCaptchaStyleName();

  if (!BDC_StringHelper::HasValue($captchaStyleName)) {
    BDC_HttpHelper::BadRequest('Invalid style name.');
  }

  $captchaId = null;
  if (isset($_GET['t'])) {
    $captchaId = BDC_StringHelper::Normalize($_GET['t']);
    if (1 !== preg_match(BDC_SimpleCaptchaBase::VALID_CAPTCHA_ID, $captchaId)) {
      return null;
    }
  }

  $captcha = new SimpleCaptcha($captchaStyleName, $captchaId);
  return $captcha;
}


function GetWebResource($p_Resource, $p_MimeType, $hasEtag = true) {
  header("Content-Type: $p_MimeType");
  if ($hasEtag) {
    BDC_HttpHelper::AllowEtagCache($p_Resource);
  }

  return file_get_contents($p_Resource);
}

function IsObviousBotRequest($p_Captcha) {

  $captchaRequestValidator = new SimpleCaptchaRequestValidator($p_Captcha->Configuration);

  // some basic request checks
  $captchaRequestValidator->RecordRequest();

  if ($captchaRequestValidator->IsObviousBotAttempt()) {
    BDC_HttpHelper::TooManyRequests('IsObviousBotAttempt');
  }

  return false;
}

// extract the exact Captcha code instance referenced by the request
function GetCaptchaId() {
  $captchaId = $_GET['t'];
  if (!BDC_StringHelper::HasValue($captchaId)) {
    return;
  }
  
  // captchaId consists of 32 lowercase hexadecimal digits
  if(strlen($captchaId) != 32) {
    return null;
  }
  
  if (1 !== preg_match(BDC_SimpleCaptchaBase::VALID_CAPTCHA_ID, $captchaId)) {
      return null;
  }
  
  return $captchaId;
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
    foreach ($_GET as $key => $value) {
      if (!in_array($key, $recognized)) {
        $input = $value;
        break;
      }
    }
  }

  return $input;
}

function getClientSideFramework() {
  if (isset($_GET['cs'])) {
    $clientSide = BDC_StringHelper::Normalize($_GET['cs']);
    if (!BDC_StringHelper::HasValue($clientSide)) {
      return null;
    }
    return $clientSide;
  } else {
    return null;
  }
}

// encodes the Captcha validation result in a simple JSON wrapper
function GetJsonValidationResult($p_Result) {
  $resultStr = ($p_Result ? 'true' : 'false');
  return $resultStr;
}

function GetP() {
  BDC_CaptchaEndpointConfig::EnableCors();
  BDC_CaptchaEndpointConfig::AddUserDefinedHttpHeaders();
  
  // // authenticate client-side request
  // $corsAuth = new CorsAuth();
  // if (!$corsAuth->IsClientAllowed()) {
  //   BDC_HttpHelper::BadRequest($corsAuth->GetFrontEnd() . " is not an allowed front-end");
  // }
  
  $captcha = GetCaptchaObject();
  if (is_null($captcha)) {
    BDC_HttpHelper::BadRequest('Captcha doesn\'t exist');
  }
  
  $captchaId = GetCaptchaId();
  
  if (is_null($captchaId)) {
    BDC_HttpHelper::BadRequest('Captcha Id doesn\'t exist');
  }
  
  // generate new pow for the current instance id
  $p = $captcha->GenPw($captchaId);
  
  // save
  $captcha->SavePw($captcha, $captchaId);
  
  // response data
  $response = "{\"sp\":\"{$p->GetSP()}\",\"hs\":\"{$p->GetHs()}\"}";
  
  // response MIME type & headers
  header('Content-Type: application/json');
  header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet');
  BDC_HttpHelper::SmartDisallowCache();
  
  echo $response;
}
?>