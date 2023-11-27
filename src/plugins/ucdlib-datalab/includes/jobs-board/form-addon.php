<?php

final class UcdlibDatalabJobsBoardAddon extends Forminator_Addon_Abstract {

	private static $_instance = null;

	protected $_slug                   = 'dl-jb';
	protected $_version                = '1.0.0';
	protected $_min_forminator_version = '1.25';
	protected $_short_title            = 'Jobs Board';
	protected $_title                  = 'UC Davis Library Datalab Jobs Board';
	protected $_url                    = 'https://library.ucdavis.edu';
	protected $_full_path              = __FILE__;
	protected $_icon                   = '';
	protected $_icon_x2                = '';
	protected $_image_x2               = '';

  protected $_form_hooks    = 'UcdlibDatalabJobsBoardAddonHooks';

	public function __construct() {
		$this->_description = __( 'Integrations for the UC Davis Library Datalab Jobs Board. Activation handled automatically', 'forminator' );

    $plugin = $GLOBALS['ucdlibDatalab'];
    $image = $plugin->utils->logoUrl();
    if ( !empty($image)) {
      $this->_image = $image;
      $this->_icon = $image;
    }

	}

	/**
	 * @return self|null
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * We don't need to check for settings, so we can just return true
	 *
	 * @return bool
	 */
	public function is_connected() {
		return true;
	}

	/**
	 * @description Checks if a form is selected as the job submission form
	 *
	 * @param $form_id
	 *
	 * @return bool
	 */
	public function is_form_connected( $form_id ) {
    $plugin = $GLOBALS['ucdlibDatalab'];
    $settings = $plugin->jobsBoard->getAdminSettings();
    if ( !empty($settings['selectedForm']) && $form_id == $settings['selectedForm'] ) {
      return true;
    }
		return false;
	}
}

class UcdlibDatalabJobsBoardAddonHooks extends Forminator_Addon_Form_Hooks_Abstract {
  public function __construct( Forminator_Addon_Abstract $addon, $form_id ) {
    parent::__construct( $addon, $form_id );
    $this->plugin = $GLOBALS['ucdlibDatalab'];

    $this->_submit_form_error_message = __( 'Submission failed! Please try again later.', 'forminator' );
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
