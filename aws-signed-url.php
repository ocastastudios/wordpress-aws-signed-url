<?hh

/*
Plugin Name: AWS Signed URLs
Description: Generates signed urls for Cloudfront assets
Version: 1.0.0
Author: Ocasta Studios

Copyright 2016 Ocasta Studios Ltd

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/


register_activation_hook(   __FILE__, array( 'AWSSignedURL', 'aws_signed_url_activation' ) );
register_deactivation_hook(   __FILE__, array( 'AWSSignedURL', 'aws_signed_url_deactivation' ) );

class AWSSignedURL
{

  public function __construct() : void {
    require_once(plugin_dir_path(__FILE__) . '/aws-signed-url-options.php');
    new AWSSignedURL_Options();

    add_filter('wp_get_attachment_url', array($this,'get_signed_URL'),100);
  }

  // Create a Signed URL for media assets stored on S3 and served up via CloudFront
  function get_signed_URL($resource) {
    $options = get_option('aws_signed_url_settings');

    $expires = time() + $options['aws_signed_url_lifetime']; //Time out in seconds
    $json = '{"Statement":[{"Resource":"'.$resource.'","Condition":{"DateLessThan":{"AWS:EpochTime":'.$expires.'}}}]}';

    //Read the private key
    $key = openssl_get_privatekey($options['aws_signed_url_pem']);
    if(!$key)
    {
      error_log( 'Failed to read private key: '.openssl_error_string() );
      return $resource;
    }

    //Sign the policy with the private key
    if(!openssl_sign($json, $signed_policy, $key, OPENSSL_ALGO_SHA1))
    {
      error_log( 'Failed to sign url: '.openssl_error_string());
      return $resource;
    }

    //Create signiature
    $base64_signed_policy = base64_encode($signed_policy);
    $signature = str_replace(array('+','=','/'), array('-','_','~'), $base64_signed_policy);

    //Construct the URL
    $url = $resource.'?Expires='.$expires.'&Signature='.$signature.'&Key-Pair-Id='.$options['aws_signed_url_key_pair_id'];

    return $url;
  }

  public static function aws_signed_url_activation() : void {

  }

  public static function aws_signed_url_deactivation() : void {

  }

}

new AWSSignedURL();
