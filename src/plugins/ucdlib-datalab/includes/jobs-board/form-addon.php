<?php

final class UcdlibDatalabJobsBoardAddon extends Forminator_Integration {

	protected static $instance = null;

	protected $_slug                   = 'dljb';
	protected $_version                = '1.0.0';
	protected $_min_forminator_version = '1.25';
	protected $_short_title            = 'Jobs Board';
	protected $_title                  = 'UC Davis Library Datalab Jobs Board';
	protected $_url                    = 'https://library.ucdavis.edu';


	public function __construct() {
		$this->_description = __( 'Integrations for the UC Davis Library Datalab Jobs Board. Activation handled automatically', 'forminator' );

    $plugin = $GLOBALS['ucdlibDatalab'];
    $image = $plugin->utils->logoUrl();
    if ( !empty($image)) {
      $this->_image = $image;
      $this->_icon = $image;
    }

	}

  public function assets_path() : string {
    $plugin = $GLOBALS['ucdlibDatalab'];
    return $plugin->assets->assetsUrl . '/forminator';
  }

	/**
	 * We don't need to check for settings, so we can just return true
	 *
	 * @return bool
	 */
	public function is_connected() {
		return true;
	}

  public function is_module_connected( $module_id, $module_slug = 'form', $check_lead = false ) {
    $form_id = $module_id;
    $plugin = $GLOBALS['ucdlibDatalab'];
    $settings = $plugin->jobsBoard->getAdminSettings();
    if ( !empty($settings['selectedForm']) && $form_id == $settings['selectedForm'] ) {
      return true;
    }
		return false;
  }
}

class Forminator_Dljb_Form_Hooks extends Forminator_Integration_Form_Hooks {
  public $plugin;
  public function __construct(Forminator_Integration $addon, int $module_id) {
    parent::__construct( $addon, $module_id );
    $this->plugin = $GLOBALS['ucdlibDatalab'];

    $this->submit_error_message = __( 'Submission failed! Please try again later.', 'forminator' );
  }

  public function add_entry_fields( $submitted_data, $form_entry_fields = array(), $entry = null ) {
    return [
      [
        'name' => $this->plugin->jobsBoard->jobStatusMetaKey,
        'value' => $this->plugin->jobsBoard->jobStatuses['pending']['slug']
      ],
      [
        'name' => $this->plugin->jobsBoard->postedDateMetaKey,
        'value' => date('Y-m-d')
      ]
    ];
  }
}
