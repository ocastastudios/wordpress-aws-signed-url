<?hh

class AWSSignedURL_Options
{

  public function __construct() {
    add_action('admin_menu', array($this, 'aws_signed_url_add_admin_menu'));
    add_action('admin_init', array($this, 'aws_signed_url_settings_init'));
  }


  function aws_signed_url_add_admin_menu() : void {
    add_options_page(__('AWS Signed URL'), __('AWS Signed URL'), 'manage_options', 'aws_signed_url', array($this,'aws_signed_url_options_page'));
  }


  function aws_signed_url_settings_init() : void {

    register_setting('pluginPage', 'aws_signed_url_settings', array($this, 'validate_input'));

    add_settings_section(
      'aws_signed_url_pluginPage_section',
      __('CloudFront Key Pair Details', 'wordpress'),
      array($this,'aws_signed_url_settings_section_callback'),
      'pluginPage'
    );

    add_settings_field(
      'aws_signed_url_key_pair_id',
      __('CloudFront Key Pair ID', 'wordpress'),
      array($this, 'aws_signed_url_key_pair_id_render'),
      'pluginPage',
      'aws_signed_url_pluginPage_section'
    );

    add_settings_field(
      'aws_signed_url_pem',
      __('Private Key PEM', 'wordpress'),
      array($this, 'aws_signed_url_pem_render'),
      'pluginPage',
      'aws_signed_url_pluginPage_section'
    );

    add_settings_field(
      'aws_signed_url_lifetime',
      __('URL Lifetime', 'wordpress'),
      array($this, 'aws_signed_url_lifetime_render'),
      'pluginPage',
      'aws_signed_url_pluginPage_section'
    );

  }


  function aws_signed_url_key_pair_id_render() : void {
    $options = get_option('aws_signed_url_settings');
    echo "<input type='text' size='25' name='aws_signed_url_settings[aws_signed_url_key_pair_id]' value='{$options['aws_signed_url_key_pair_id']}' />";
  }


  function aws_signed_url_pem_render() : void {
    $options = get_option('aws_signed_url_settings');
    echo "<textarea cols='65' rows='28' style='font-family:Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New, monospaced;' name='aws_signed_url_settings[aws_signed_url_pem]'> {$options['aws_signed_url_pem']}</textarea>";
  }


  function aws_signed_url_settings_section_callback() : void {
    echo __('Set the Key Pair ID and the Private key values for creating AWS Signed URLs');
  }

  function aws_signed_url_lifetime_render() : void {
    $options = get_option('aws_signed_url_settings');
    if (!array_key_exists('aws_signed_url_lifetime', $options)){
      $options['aws_signed_url_lifetime'] = '5';
    }
    echo "<input type='number' min='1' max='20000' name='aws_signed_url_settings[aws_signed_url_lifetime]' value='{$options['aws_signed_url_lifetime']}'</input> Minutes";
  }

  function aws_signed_url_options_page() : void {
    echo <<< START
    <form action='options.php' method='post'>
    <h2>AWS Signed URL</h2>
    <p>To create CloudFront signed URLs your trusted signer must have its own CloudFront key pair,
     and the key pair must be active. For details see
    <a href=http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/PrivateContent.html>Serving Private Content through CloudFront</a>
    </p><p>To help secure your applications, AWS recommends that you change CloudFront key pairs every 90 days or more often.</p>
START;
    settings_fields('pluginPage');
    do_settings_sections('pluginPage');
    submit_button();


    echo "</form>";
  }

  function validate_input($input) {
    // Create our array for storing the validated options
    $input['aws_signed_url_key_pair_id'] = trim($input['aws_signed_url_key_pair_id']);
    $input['aws_signed_url_pem'] = trim($input['aws_signed_url_pem']);

    if (strlen($input['aws_signed_url_key_pair_id']) == 0) {
      add_settings_error('aws_signed_url_key_pair_id', '' ,'Key Pair ID must be set', 'error');
    }

    if (strlen($input['aws_signed_url_pem']) == 0) {
      add_settings_error('aws_signed_url_pem', '' ,'Private Key must be set', 'error');
    } else {
      $key = openssl_get_privatekey($input['aws_signed_url_pem']);
      if (!$key) {
        add_settings_error('aws_signed_url_pem', '', 'Cannot parse Private Key. OpenSSL error: ' . openssl_error_string(), 'error');
      }
    }
    return $input;
  }
}

