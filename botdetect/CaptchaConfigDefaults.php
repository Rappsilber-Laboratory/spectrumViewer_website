<?php

$BDC_CaptchaConfig = new stdClass();

// Captcha codes
// ---------------------------------------------------------------------------
$BDC_CaptchaConfig->CodeLength = CaptchaRandomization::GetRandomCodeLength(4, 6);
$BDC_CaptchaConfig->CodeStyle = CodeStyle::Alphanumeric;
$BDC_CaptchaConfig->CodeTimeout = 1200;
$BDC_CaptchaConfig->DisallowedCodeSubstrings = '';
$BDC_CaptchaConfig->TestModeEnabled = false;

// Captcha images
// ---------------------------------------------------------------------------
$BDC_CaptchaConfig->ImageStyle = CaptchaRandomization::GetRandomImageStyle();
$BDC_CaptchaConfig->ImageWidth = 250;
$BDC_CaptchaConfig->ImageHeight = 50;
$BDC_CaptchaConfig->ImageFormat = ImageFormat::Jpeg;
$BDC_CaptchaConfig->CustomDarkColor = '';
$BDC_CaptchaConfig->CustomLightColor = '';
$BDC_CaptchaConfig->DisabledImageStyles = '';
$BDC_CaptchaConfig->ImageColorMode = BDC_CaptchaDefaults::$ImageColorMode;

// Captcha sounds
// ---------------------------------------------------------------------------
$BDC_CaptchaConfig->SoundEnabled = true;
$BDC_CaptchaConfig->SoundStyle = CaptchaRandomization::GetRandomSoundStyle();
$BDC_CaptchaConfig->SoundFormat = SoundFormat::WavPcm16bit8kHzMono;
$BDC_CaptchaConfig->SoundRegenerationMode = SoundRegenerationMode::Limited;
$BDC_CaptchaConfig->WarnAboutMissingSoundPackages = true;
$BDC_CaptchaConfig->DisabledSoundStyles = '';
$BDC_CaptchaConfig->SoundPackagesFolder = BDC_INCLUDE_PATH . 'Resources/Sounds';

// Captcha localization & locale-dependent strings
// ---------------------------------------------------------------------------
$BDC_CaptchaConfig->Locale = 'en-US';
$BDC_CaptchaConfig->ImageTooltip = '';
$BDC_CaptchaConfig->SoundTooltip = '';
$BDC_CaptchaConfig->ReloadTooltip = '';
$BDC_CaptchaConfig->HelpLinkUrl = '';
$BDC_CaptchaConfig->HelpLinkText = '';

// Captcha controls & appearance
// ---------------------------------------------------------------------------
$BDC_CaptchaConfig->ReloadEnabled = true;
$BDC_CaptchaConfig->UseSmallIcons = null;
$BDC_CaptchaConfig->UseHorizontalIcons = null;
$BDC_CaptchaConfig->ReloadIconUrl = null;
$BDC_CaptchaConfig->SoundIconUrl = null;
$BDC_CaptchaConfig->IconsDivWidth = null;
$BDC_CaptchaConfig->HelpLinkEnabled = true;
$BDC_CaptchaConfig->HelpLinkMode = HelpLinkMode::Text;
$BDC_CaptchaConfig->AdditionalCssClasses = '';
$BDC_CaptchaConfig->AdditionalInlineCss = '';

// Captcha client-side
// ---------------------------------------------------------------------------
$BDC_CaptchaConfig->AddScriptInclude = true;
$BDC_CaptchaConfig->AddInitScriptInclude = true;
$BDC_CaptchaConfig->AutoUppercaseInput = true;
$BDC_CaptchaConfig->AutoFocusInput = true;
$BDC_CaptchaConfig->AutoClearInput = true;
$BDC_CaptchaConfig->AutoReloadExpiredCaptchas = true;
$BDC_CaptchaConfig->AutoReloadTimeout = 7200; // 2 hours
$BDC_CaptchaConfig->SoundStartDelay = 0;
$BDC_CaptchaConfig->RemoteScriptEnabled = true;
$BDC_CaptchaConfig->JavaScriptRequired = true;

// Captcha-related PHP applicaton settings
// ---------------------------------------------------------------------------
$BDC_CaptchaConfig->ErrorLoggingEnabled = false;
$BDC_CaptchaConfig->HandlerUrl = 'botdetect.php';
$BDC_CaptchaConfig->SaveFunctionName = 'PHP_Session_Save';
$BDC_CaptchaConfig->LoadFunctionName = 'PHP_Session_Load';
$BDC_CaptchaConfig->ClearFunctionName = 'PHP_Session_Clear';

$BDC_CaptchaConfig->GlobalDeclarationsProcessed = false; // init flag
CaptchaConfiguration::SaveSettings($BDC_CaptchaConfig);

?>