<?php
/*
 * Xibo - Digital Signage - http://www.xibo.org.uk
 * Copyright (C) 2014-2015 Daniel Garner
 *
 * This file is part of Xibo.
 *
 * Xibo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version. 
 *
 * Xibo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Xibo.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace Xibo\Widget;

use Emojione\Client;
use Emojione\Ruleset;
use Respect\Validation\Validator as v;
use Xibo\Factory\DisplayFactory;
use Xibo\Factory\MediaFactory;
use Xibo\Helper\Cache;
use Xibo\Helper\Config;
use Xibo\Helper\Date;
use Xibo\Helper\Log;
use Xibo\Helper\Sanitize;
use Xibo\Helper\Theme;

class Twitter extends ModuleWidget
{
    public $codeSchemaVersion = 1;

    /**
     * Install or Update this module
     */
    public function installOrUpdate()
    {
        if ($this->module == null) {
            // Install
            $module = new \Xibo\Entity\Module();
            $module->name = 'Twitter';
            $module->type = 'twitter';
            $module->class = 'Xibo\Widget\Twitter';
            $module->description = 'Twitter Search Module';
            $module->imageUri = 'forms/library.gif';
            $module->enabled = 1;
            $module->previewEnabled = 1;
            $module->assignable = 1;
            $module->regionSpecific = 1;
            $module->renderAs = 'html';
            $module->schemaVersion = $this->codeSchemaVersion;
            $module->settings = [];

            $this->setModule($module);
            $this->installModule();
        }

        // Check we are all installed
        $this->installFiles();
    }

    /**
     * Install Files
     */
    public function installFiles()
    {
        MediaFactory::createModuleSystemFile('modules/vendor/jquery-1.11.1.min.js')->save();
        MediaFactory::createModuleSystemFile('modules/xibo-text-render.js')->save();
        MediaFactory::createModuleSystemFile('modules/xibo-layout-scaler.js')->save();
        MediaFactory::createModuleSystemFile(PROJECT_ROOT . '/web/modules/emojione/emojione.sprites.svg')->save();
    }

    /**
     * Loads templates for this module
     */
    private function loadTemplates()
    {
        $this->module->settings['templates'] = [];

        // Scan the folder for template files
        foreach (glob(PROJECT_ROOT . '/modules/twitter/*.template.json') as $template) {
            // Read the contents, json_decode and add to the array
            $this->module->settings['templates'][] = json_decode(file_get_contents($template), true);
        }

        Log::debug(count($this->module->settings['templates']));
    }

    /**
     * Templates available
     * @return array
     */
    public function templatesAvailable()
    {
        if (!isset($this->module->settings['templates']))
            $this->loadTemplates();

        return $this->module->settings['templates'];
    }

    /**
     * Form for updating the module settings
     */
    public function settingsForm()
    {
        return 'twitter-form-settings';
    }

    /**
     * Process any module settings
     */
    public function settings()
    {
        // Process any module settings you asked for.
        $apiKey = Sanitize::getString('apiKey');

        if ($apiKey == '')
            throw new \InvalidArgumentException(__('Missing API Key'));

        // Process any module settings you asked for.
        $apiSecret = Sanitize::getString('apiSecret');

        if ($apiSecret == '')
            throw new \InvalidArgumentException(__('Missing API Secret'));

        $this->module->settings['apiKey'] = $apiKey;
        $this->module->settings['apiSecret'] = $apiSecret;
        $this->module->settings['cachePeriod'] = Sanitize::getInt('cachePeriod', 300);
        $this->module->settings['cachePeriodImages'] = Sanitize::getInt('cachePeriodImages', 24);

        // Return an array of the processed settings.
        return $this->module->settings;
    }

    public function validate()
    {
        if ($this->getDuration() == 0)
            throw new \InvalidArgumentException(__('Please enter a duration'));

        if (!v::string()->notEmpty()->validate($this->getOption('searchTerm')))
            throw new \InvalidArgumentException(__('Please enter a search term'));
    }

    /**
     * Add Media
     */
    public function add()
    {
        $this->setDuration(Sanitize::getInt('duration', $this->getDuration()));
        $this->setOption('name', Sanitize::getString('name'));
        $this->setOption('searchTerm', Sanitize::getString('searchTerm'));
        $this->setOption('effect', Sanitize::getString('effect'));
        $this->setOption('speed', Sanitize::getInt('speed'));
        $this->setOption('backgroundColor', Sanitize::getString('backgroundColor'));
        $this->setOption('noTweetsMessage', Sanitize::getString('noTweetsMessage'));
        $this->setOption('dateFormat', Sanitize::getString('dateFormat'));
        $this->setOption('resultType', Sanitize::getString('resultType'));
        $this->setOption('tweetDistance', Sanitize::getInt('tweetDistance'));
        $this->setOption('tweetCount', Sanitize::getInt('tweetCount'));
        $this->setOption('removeUrls', Sanitize::getCheckbox('removeUrls'));
        $this->setOption('overrideTemplate', Sanitize::getCheckbox('overrideTemplate'));
        $this->setOption('updateInterval', Sanitize::getInt('updateInterval', 60));
        $this->setOption('templateId', Sanitize::getString('templateId'));

        $this->setRawNode('template', Sanitize::getParam('ta_text', null));
        $this->setRawNode('styleSheet', Sanitize::getParam('ta_css', null));

        // Save the widget
        $this->validate();
        $this->saveWidget();
    }

    /**
     * Edit Media
     */
    public function edit()
    {
        $this->setDuration(Sanitize::getInt('duration', $this->getDuration()));
        $this->setOption('name', Sanitize::getString('name'));
        $this->setOption('searchTerm', Sanitize::getString('searchTerm'));
        $this->setOption('effect', Sanitize::getString('effect'));
        $this->setOption('speed', Sanitize::getInt('speed'));
        $this->setOption('backgroundColor', Sanitize::getString('backgroundColor'));
        $this->setOption('noTweetsMessage', Sanitize::getString('noTweetsMessage'));
        $this->setOption('dateFormat', Sanitize::getString('dateFormat'));
        $this->setOption('resultType', Sanitize::getString('resultType'));
        $this->setOption('tweetDistance', Sanitize::getInt('tweetDistance'));
        $this->setOption('tweetCount', Sanitize::getInt('tweetCount'));
        $this->setOption('removeUrls', Sanitize::getCheckbox('removeUrls'));
        $this->setOption('overrideTemplate', Sanitize::getCheckbox('overrideTemplate'));
        $this->setOption('updateInterval', Sanitize::getInt('updateInterval', 60));
        $this->setOption('templateId', Sanitize::getString('templateId'));

        $this->setRawNode('template', Sanitize::getParam('ta_text', null));
        $this->setRawNode('styleSheet', Sanitize::getParam('ta_css', null));

        // Save the widget
        $this->validate();
        $this->saveWidget();

        // Save the widget
        $this->validate();
        $this->saveWidget();
    }

    protected function getToken()
    {
        // Prepare the URL
        $url = 'https://api.twitter.com/oauth2/token';

        // Prepare the consumer key and secret
        $key = base64_encode(urlencode($this->getSetting('apiKey')) . ':' . urlencode($this->getSetting('apiSecret')));

        // Check to see if we have the bearer token already cached
        if (Cache::has('bearer_' . $key)) {
            Log::debug('Bearer Token served from cache');
            return Cache::get('bearer_' . $key);
        }

        Log::debug('Bearer Token served from API');

        // Shame - we will need to get it.
        // and store it.
        $httpOptions = array(
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => array(
                'POST /oauth2/token HTTP/1.1',
                'Authorization: Basic ' . $key,
                'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
                'Content-Length: 29'
            ),
            CURLOPT_USERAGENT => 'Xibo Twitter Module',
            CURLOPT_HEADER => false,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(array('grant_type' => 'client_credentials')),
            CURLOPT_URL => $url,
        );

        // Proxy support
        if (Config::GetSetting('PROXY_HOST') != '' && !Config::isProxyException($url)) {
            $httpOptions[CURLOPT_PROXY] = Config::GetSetting('PROXY_HOST');
            $httpOptions[CURLOPT_PROXYPORT] = Config::GetSetting('PROXY_PORT');

            if (Config::GetSetting('PROXY_AUTH') != '')
                $httpOptions[CURLOPT_PROXYUSERPWD] = Config::GetSetting('PROXY_AUTH');
        }

        $curl = curl_init();

        // Set options
        curl_setopt_array($curl, $httpOptions);

        // Call exec
        if (!$result = curl_exec($curl)) {
            // Log the error
            Log::error('Error contacting Twitter API: ' . curl_error($curl));
            return false;
        }

        // We want to check for a 200
        $outHeaders = curl_getinfo($curl);

        if ($outHeaders['http_code'] != 200) {
            Log::error('Twitter API returned ' . $result . ' status. Unable to proceed. Headers = ' . var_export($outHeaders, true));

            // See if we can parse the error.
            $body = json_decode($result);

            Log::error('Twitter Error: ' . ((isset($body->errors[0])) ? $body->errors[0]->message : 'Unknown Error'));

            return false;
        }

        // See if we can parse the body as JSON.
        $body = json_decode($result);

        // We have a 200 - therefore we want to think about caching the bearer token
        // First, lets check its a bearer token
        if ($body->token_type != 'bearer') {
            Log::error('Twitter API returned OK, but without a bearer token. ' . var_export($body, true));
            return false;
        }

        // It is, so lets cache it
        // long times...
        Cache::put('bearer_' . $key, $body->access_token, 100000);

        return $body->access_token;
    }

    protected function searchApi($token, $term, $resultType = 'mixed', $geoCode = '', $count = 15)
    {
        // Construct the URL to call
        $url = 'https://api.twitter.com/1.1/search/tweets.json';
        $queryString = '?q=' . urlencode(trim($term)) .
            '&result_type=' . $resultType .
            '&count=' . $count .
            '&include_entities=true';

        if ($geoCode != '')
            $queryString .= '&geocode=' . $geoCode;

        $httpOptions = array(
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => array(
                'GET /1.1/search/tweets.json' . $queryString . 'HTTP/1.1',
                'Host: api.twitter.com',
                'Authorization: Bearer ' . $token
            ),
            CURLOPT_USERAGENT => 'Xibo Twitter Module',
            CURLOPT_HEADER => false,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url . $queryString,
        );

        // Proxy support
        if (Config::GetSetting('PROXY_HOST') != '' && !Config::isProxyException($url)) {
            $httpOptions[CURLOPT_PROXY] = Config::GetSetting('PROXY_HOST');
            $httpOptions[CURLOPT_PROXYPORT] = Config::GetSetting('PROXY_PORT');

            if (Config::GetSetting('PROXY_AUTH') != '')
                $httpOptions[CURLOPT_PROXYUSERPWD] = Config::GetSetting('PROXY_AUTH');
        }

        Log::debug('Calling API with: ' . $url . $queryString);

        $curl = curl_init();
        curl_setopt_array($curl, $httpOptions);
        $result = curl_exec($curl);

        // Get the response headers
        $outHeaders = curl_getinfo($curl);

        if ($outHeaders['http_code'] == 0) {
            // Unable to connect
            Log::error('Unable to reach twitter api.');
            return false;
        } else if ($outHeaders['http_code'] != 200) {
            Log::error('Twitter API returned ' . $outHeaders['http_code'] . ' status. Unable to proceed. Headers = ' . var_export($outHeaders, true));

            // See if we can parse the error.
            $body = json_decode($result);

            Log::error('Twitter Error: ' . ((isset($body->errors[0])) ? $body->errors[0]->message : 'Unknown Error'));

            return false;
        }

        // Parse out header and body
        $body = json_decode($result);

        return $body;
    }

    protected function getTwitterFeed($displayId = 0, $isPreview = true)
    {
        if (!extension_loaded('curl')) {
            trigger_error(__('cURL extension is required for Twitter'));
            return false;
        }

        // Do we need to add a geoCode?
        $geoCode = '';
        $distance = $this->getOption('tweetDistance');
        if ($distance != 0) {
            // Use the display ID or the default.
            if ($displayId != 0) {
                // Look up the lat/long
                $display = DisplayFactory::getById($displayId);
                $defaultLat = $display->latitude;
                $defaultLong = $display->longitude;
            } else {
                $defaultLat = Config::GetSetting('DEFAULT_LAT');
                $defaultLong = Config::GetSetting('DEFAULT_LONG');
            }

            // Built the geoCode string.
            $geoCode = implode(',', array($defaultLat, $defaultLong, $distance)) . 'mi';
        }

        // Connect to twitter and get the twitter feed.
        $key = md5($this->getOption('searchTerm') . $this->getOption('resultType') . $this->getOption('tweetCount', 15) . $geoCode);

        if (!Cache::has($key) || Cache::get($key) == '') {

            Log::debug('Querying API for ' . $this->getOption('searchTerm'));

            // We need to search for it
            if (!$token = $this->getToken())
                return false;

            // We have the token, make a tweet
            if (!$data = $this->searchApi($token, $this->getOption('searchTerm'), $this->getOption('resultType'), $geoCode, $this->getOption('tweetCount', 15)))
                return false;

            // Cache it
            Cache::put($key, $data, $this->getSetting('cachePeriod'));
        } else {
            Log::debug('Served from Cache');
            $data = Cache::get($key);
        }

        // Get the template
        $template = $this->getRawNode('template', null);

        // Parse the text template
        $matches = '';
        preg_match_all('/\[.*?\]/', $template, $matches);

        // Build an array to return
        $return = array();

        // Expiry time for any media that is downloaded
        $expires = time() + ($this->getSetting('cachePeriodImages') * 60 * 60);

        // Remove URL setting
        $removeUrls = $this->getOption('removeUrls', 1);

        // If we have nothing to show, display a no tweets message.
        if (count($data->statuses) <= 0) {
            // Create ourselves an empty tweet so that the rest of the code can continue as normal
            $user = new \stdClass();
            $user->name = '';
            $user->screen_name = '';
            $user->profile_image_url = '';

            $tweet = new \stdClass();
            $tweet->text = $this->getOption('noTweetsMessage', __('There are no tweets to display'));
            $tweet->created_at = date("Y-m-d H:i:s");
            $tweet->user = $user;

            // Append to our statuses
            $data->statuses[] = $tweet;
        }

        // Make an emojione client
        $emoji = new Client(new Ruleset());
        $emoji->imageType = 'svg';
        $emoji->sprites = true;
        $emoji->imagePathSVGSprites = $this->getResourceUrl('emojione/emojione.sprites.svg');

        // Get the date format to apply
        $dateFormat = $this->getOption('dateFormat', Config::GetSetting('DATE_FORMAT'));

        // This should return the formatted items.
        foreach ($data->statuses as $tweet) {
            // Substitute for all matches in the template
            $rowString = $template;

            foreach ($matches[0] as $sub) {
                // Always clear the stored template replacement
                $replace = '';

                // Maybe make this more generic?
                switch ($sub) {
                    case '[Tweet]':
                        // Get the tweet text to operate on
                        $tweetText = $tweet->text;

                        // Replace URLs with their display_url before removal
                        if (isset($tweet->entities->urls)) {
                            foreach ($tweet->entities->urls as $url) {
                                $tweetText = str_replace($url->url, $url->display_url, $tweetText);
                            }
                        }

                        // Handle URL removal if requested
                        if ($removeUrls == 1) {
                            $tweetText = preg_replace("((https?|ftp|gopher|telnet|file|notes|ms-help):((\/\/)|(\\\\))+[\w\d:#\@%\/;$()~_?\+-=\\\.&]*)", '', $tweetText);
                        }

                        $replace = $emoji->toImage($tweetText);
                        break;

                    case '[User]':
                        $replace = $tweet->user->name;
                        break;

                    case '[ScreenName]':
                        $replace = $tweet->user->screen_name;
                        break;

                    case '[Date]':
                        $replace = Date::getLocalDate(strtotime($tweet->created_at), $dateFormat);
                        break;

                    case '[ProfileImage]':
                        // Grab the profile image
                        if ($tweet->user->profile_image_url != '') {
                            // Grab the profile image
                            $file = MediaFactory::createModuleFile('twitter_' . $tweet->user->id, $tweet->user->profile_image_url);
                            $file->isRemote = true;
                            $file->expires = $expires;
                            $file->save();

                            // Tag this layout with this file
                            $this->assignMedia($file->mediaId);

                            $url = $this->getApp()->urlFor('library.download', ['id' => $file->mediaId, 'type' => 'image']);
                            $replace = ($isPreview) ? '<img src="' . $url . '?preview=1&width=170&height=150" />' : '<img src="' . $file->storedAs . '"  />';
                        }
                        break;

                    case '[Photo]':
                        // See if there are any photos associated with this tweet.
                        if (isset($tweet->entities->media) && count($tweet->entities->media) > 0) {
                            // Only take the first one
                            $photoUrl = $tweet->entities->media[0]->media_url;

                            if ($photoUrl != '') {
                                $file = MediaFactory::createModuleFile('twitter_photo_' . $tweet->user->id . '_' . $tweet->entities->media[0]->id_str, $photoUrl);
                                $file->isRemote = true;
                                $file->expires = $expires;
                                $file->save();

                                // Tag this layout with this file
                                $this->assignMedia($file->mediaId);

                                $url = $this->getApp()->urlFor('library.download', ['id' => $file->mediaId, 'type' => 'image']);
                                $replace = ($isPreview) ? '<img src="' . $url . '?preview=1&width=' . $this->region->width . '&height=' . $this->region->height . '" />' : '<img src="' . $file->storedAs . '"  />';
                            }
                        }

                        break;

                    default:
                        $replace = '';
                }

                $rowString = str_replace($sub, $replace, $rowString);
            }

            // Substitute the replacement we have found (it might be '')
            $return[] = $rowString;
        }

        // Return the data array
        return $return;
    }

    /**
     * Get Resource
     * @param int $displayId
     * @return mixed
     */
    public function getResource($displayId = 0)
    {
        // Make sure we are set up correctly
        if ($this->getSetting('apiKey') == '' || $this->getSetting('apiSecret') == '') {
            Log::error('Twitter Module not configured. Missing API Keys');
            return '';
        }

        $data = [];
        $isPreview = (Sanitize::getCheckbox('preview') == 1);

        // Replace the View Port Width?
        $data['viewPortWidth'] = ($isPreview) ? $this->region->width : '[[ViewPortWidth]]';

        // Information from the Module
        $duration = $this->getDuration();

        // Generate a JSON string of substituted items.
        $items = $this->getTwitterFeed($displayId, $isPreview);

        // Return empty string if there are no items to show.
        if (count($items) == 0)
            return '';

        $marqueeEffect = (stripos($this->getOption('effect'), 'marquee') !== false);

        $options = array(
            'type' => $this->getModuleType(),
            'fx' => $this->getOption('effect', 'none'),
            'speed' => $this->getOption('speed', (($marqueeEffect) ? 1 : 500)),
            'duration' => $duration,
            'durationIsPerItem' => ($this->getOption('durationIsPerItem', 0) == 1),
            'numItems' => count($items),
            'itemsPerPage' => 1,
            'originalWidth' => $this->region->width,
            'originalHeight' => $this->region->height,
            'previewWidth' => Sanitize::getDouble('width', 0),
            'previewHeight' => Sanitize::getDouble('height', 0),
            'scaleOverride' => Sanitize::getDouble('scale_override', 0)
        );

        // Replace the control meta with our data from twitter
        $data['controlMeta'] = '<!-- NUMITEMS=' . count($items) . ' -->' . PHP_EOL . '<!-- DURATION=' . ($this->getOption('durationIsPerItem', 0) == 0 ? $duration : ($duration * count($items))) . ' -->';

        // Replace the head content
        $headContent = '';

        // Add the CSS if it isn't empty
        $css = $this->getRawNode('styleSheet', null);
        if ($css != '') {
            $headContent .= '<style type="text/css">' . $css . '</style>';
        }

        $backgroundColor = $this->getOption('backgroundColor');
        if ($backgroundColor != '') {
            $headContent .= '<style type="text/css">body, .page, .item { background-color: ' . $backgroundColor . ' }</style>';
        }

        // Add our fonts.css file
        $headContent .= '<link href="' . $this->getResourceUrl('fonts.css') . ' rel="stylesheet" media="screen">';
        $headContent .= '<style type="text/css">' . file_get_contents(Theme::uri('css/client.css', true)) . '</style>';

        // Replace the Head Content with our generated javascript
        $data['head'] = $headContent;

        // Add some scripts to the JavaScript Content
        $javaScriptContent = '<script type="text/javascript" src="' . $this->getResourceUrl('vendor/jquery-1.11.1.min.js') . '"></script>';

        // Need the cycle plugin?
        if ($this->getOption('effect') != 'none')
            $javaScriptContent .= '<script type="text/javascript" src="' . $this->getResourceUrl('vendor/jquery-cycle-2.1.6.min.js') . '"></script>';

        // Need the marquee plugin?
        if ($marqueeEffect)
            $javaScriptContent .= '<script type="text/javascript" src="' . $this->getResourceUrl('vendor/jquery.marquee.min.js') . '"></script>';

        $javaScriptContent .= '<script type="text/javascript" src="' . $this->getResourceUrl('xibo-layout-scaler.js') . '"></script>';
        $javaScriptContent .= '<script type="text/javascript" src="' . $this->getResourceUrl('xibo-text-render.js') . '"></script>';

        $javaScriptContent .= '<script type="text/javascript">';
        $javaScriptContent .= '   var options = ' . json_encode($options) . ';';
        $javaScriptContent .= '   var items = ' . json_encode($items) . ';';
        $javaScriptContent .= '   $(document).ready(function() { ';
        $javaScriptContent .= '       $("body").xiboLayoutScaler(options); $("#content").xiboTextRender(options, items); ';
        $javaScriptContent .= '   }); ';
        $javaScriptContent .= '</script>';

        // Replace the Head Content with our generated javascript
        $data['javaScript'] = $javaScriptContent;

        return $this->renderTemplate($data);
    }

    public function isValid()
    {
        // Using the information you have in your module calculate whether it is valid or not.
        // 0 = Invalid
        // 1 = Valid
        // 2 = Unknown
        return 1;
    }
}
