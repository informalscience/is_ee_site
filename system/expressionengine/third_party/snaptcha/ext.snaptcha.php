<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine Snaptcha Extension
 *
 * @package		Snaptcha
 * @category	Extension
 * @description	Simple Non-obtrusive Automated Public Turing test to tell Computers and Humans Apart
 * @author		Ben Croker
 * @link		http://www.putyourlightson.net/snaptcha
 */
 
 
// get config
require_once PATH_THIRD.'snaptcha/config'.EXT;


class Snaptcha_ext
{
	var $name			= SNAPTCHA_NAME;
	var $version		= SNAPTCHA_VERSION;
	var $description	= SNAPTCHA_DESCRIPTION;
	var $settings_exist	= SNAPTCHA_SETTINGS_EXIST;
	var $docs_url		= SNAPTCHA_URL;
	
	var $settings		= array();
	
	// --------------------------------------------------------------------
	
	/**
	 * Constructor
	 */
	function __construct($settings = '')
	{
		$this->EE =& get_instance();

		$this->settings = $settings;

		// get settings in case they are not set
		$this->_get_settings();
	} 
	
	// --------------------------------------------------------------------
	
	/**
	 * Field
	 */
	function snaptcha_field($security_level='')
	{	
		$snaptcha_field = $this->settings['field_name'];
		
		// allow override of security level
		$security_level = $security_level ? $security_level : $this->settings['security_level'];
		
		
		// append random characters to field name
		$name = $snaptcha_field.'_'.$this->EE->functions->random('alpha', 9);
		
		// set value for high security level
		$value = ($security_level > 1) ? $this->EE->functions->random('alpha', 13) : '';
				
		// get field html
		$field = '<div style="position: absolute !important; top: -10000px !important;"><input type="text" id="'.$name.'" name="'.$name.'" value="'.$value.'" /></div>'.NL;
		
		
		// add javascript for medium and high security levels
		if ($security_level > 1)
		{
			$secret = $this->settings['unique_secret'];
			
			// create secret for high security level
			if ($security_level == 3)
			{
				$secret = $this->EE->functions->random('alpha', 13);
				
				// add secret to database
				$data = array(
					'name' => $name,
					'secret' => $secret,
					'timestamp' => time(),
					'ip_address' => $this->EE->input->ip_address()
				);
				
				$this->EE->db->insert('snaptcha', $data);
			}	
			
			$field .= '<script type="text/javascript">document.getElementById("'.$name.'").value = "'.$secret.'";</script>'.NL;
		} 
		
		
		return $field;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Validate
	 */
	function snaptcha_validate($security_level='')
	{			
		// check if already validated
		if ($this->_get_session_cache('snaptcha', 'validated'))
		{
			return TRUE;
		}

		
		$snaptcha_field = $this->settings['field_name'];
		
		// allow override of security level
		$security_level = $security_level ? $security_level : $this->settings['security_level'];
		
		
		// search for field in posted values
		$field = '';
		
		foreach ($_POST as $key => $value) 
		{
			if (strpos($key, $snaptcha_field.'_') === 0)
			{
				$field = $key;
			}	
		}
		
		
		// if field not found
		if (!$field)
		{
			$this->_log('Snaptcha field not found');
			return FALSE;
		}
		
			
		// low security level
		if ($security_level == 1)
		{
			// check if field is blank
			if ($this->EE->input->post($field) != '')
			{
				$this->_log('Snaptcha field not blank');
				return FALSE;
			}
		}
		
		// medium security level
		else if ($security_level == 2)
		{
			// check if secret is correct
			if ($this->EE->input->post($field) != $this->settings['unique_secret'])
			{
				$this->_log('Snaptcha field not equal to unique secret');
				return FALSE;
			}
		}
		
		// high security level
		else 
		{
			// set expiry time to 1 hour
			$expiry = time() - 3600;
			
			// check if secret exists in database			
			$this->EE->db->where('name', $field);
			$this->EE->db->where('secret', $this->EE->input->post($field));
			$this->EE->db->where('timestamp > '.$expiry);
			$this->EE->db->where('ip_address', $this->EE->input->ip_address());
			$query = $this->EE->db->get('snaptcha');
			
			if (!$query OR !$query->num_rows())
			{
				$this->_log('Snaptcha field not found in database');
				return FALSE;
			}
			
			// delete and clean out old secrets from database
			$this->EE->db->where('name', $field);
			$this->EE->db->or_where('timestamp < '.$expiry);
			$this->EE->db->delete('snaptcha');
		}
		
			
		// mark as validated in session
		$this->_set_session_cache('snaptcha', 'validated', TRUE);
		
		return TRUE;
	}

	// --------------------------------------------------------------------
	/**
	  *  Start integration with other modules 
	  */
	// --------------------------------------------------------------------
	
	/**
	 * Comment Form Field
	 */
	function comment_field($tagdata)
	{	
		// append field to tagdata
		$tagdata .= $this->snaptcha_field();
		
		return $tagdata;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Comment Form Validation
	 */
	function comment_validate()
	{	
		if (!$this->snaptcha_validate())
		{
			$this->_show_error();
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Safecracker Field
	 */
	function safecracker_field($return)
	{	
		// append field to form
		$return = str_replace('</form>', $this->snaptcha_field().NL.'</form>', $return);
		
		return $return;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Safecracker Validation
	 */
	function safecracker_validate($object)
	{
		if (!$this->snaptcha_validate())
		{
			$this->_show_error();
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Freeform Field
	 */
	function freeform_field($r)
	{	
		// append field to form
		$r = str_replace('</form>', $this->snaptcha_field().'</form>', $r);
		
		return $r;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Freeform Validation
	 */
	function freeform_validate($errors)
	{	
		// skip if we are in the CP
		if (REQ == 'CP')
		{
			return $errors;
		}
		
		// if no errors exist
		if (count($errors) == 0)
		{
			if (!$this->snaptcha_validate())
			{
				$this->_show_error();
			}
		}
		
		return $errors;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Member Register Validation
	 */
	function member_register_validate($obj)
	{
		// if enabled and no errors exist in member object
		if ($this->settings['member_registration_validation'] AND count($obj->errors) == 0)
		{
			// member register security level must be less than 3
			$member_register_security_level = ($this->settings['security_level'] < 3) ? $this->settings['security_level'] : 2;
			
			if (!$this->snaptcha_validate($member_register_security_level))
			{
				$this->_show_error();
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Member Register Validation Deprecated (for versions earlier than EE2.5.0)
	 */
	function member_register_validate_deprecated()
	{
		// check if version is 2.5.0 or later
		if (version_compare(APP_VER, '2.5.0', '>='))
		{
			return;
		}
		
		// if enabled
		if ($this->settings['member_registration_validation'])
		{
			// member register security level must be less than 3
			$member_register_security_level = ($this->settings['security_level'] < 3) ? $this->settings['security_level'] : 2;
			
			if (!$this->snaptcha_validate($member_register_security_level))
			{
				$this->_show_error();
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Forum Field
	 */
	function forum_field($obj, $str)
	{	
		// if submission form is in string or we are on view thread page
		if (substr_count($str, '{form_declaration:forum:submit_post}') OR $this->EE->uri->segment(2) == 'viewthread')
		{
			// get field
			$field = $this->snaptcha_field();
			
			// extract input field name
			preg_match('/name="(.*?)"/', $field, $matches);
			$name = $matches[1];
			
			// append field
			$str .= $field;
			
			// append javascript that will move the field into the form
			$str .= '<script type="text/javascript">window.onload = function() { document.getElementById("submit_post").appendChild(document.getElementById("'.$name.'").parentNode); }</script>'.NL;
		}
		
		return $str;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Forum Validation
	 */
	function forum_validate()
	{	
		if (!$this->snaptcha_validate())
		{
			$this->_show_error();
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * User Register Validation
	 */
	function user_register_validate()
	{	
		// if enabled
		if ($this->settings['member_registration_validation'])
		{
			if (!$this->snaptcha_validate())
			{
				$this->_show_error();
			}
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Freemember Register Validation
	 */
	function freemember_register_validate()
	{
		// if enabled and submitted form is validated
		if ($this->settings['member_registration_validation'] AND $this->EE->form_validation->run())
		{
			if (!$this->snaptcha_validate())
			{
				$this->_show_error();
			}
		}
	}

	// --------------------------------------------------------------------
	
	/**
	 * Proform Field
	 */
	function proform_field()
	{	
		// prepend field with required fake captcha input field
		$output = '<input type="hidden" name="captcha" value="xyz" />'.$this->snaptcha_field();
		
		return array($output, FALSE);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Proform Process
	 */
	function proform_process_captcha($obj, $form_obj, $form_session)
	{
		// return TRUE boolean to indicate that we are handling captcha
		return array(TRUE, $form_obj, $form_session);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Proform Validation
	 */
	function proform_validate($obj, $form_obj, &$form_session)
	{
		// if no errors
		if (count($form_session->errors) == 0)
		{
			if (!$this->snaptcha_validate())
			{
				$form_session->add_error('captcha', $this->settings['error_message']);
			}
		}
	}

	// --------------------------------------------------------------------
	/**
	  *  End integration with other modules 
	  */
	// --------------------------------------------------------------------
	
	/**
	  *  Get settings
	  */
	private function _get_settings()
	{		
		if (empty($this->settings))
		{
			$this->EE->db->where('class', __CLASS__);
			$this->EE->db->limit(1);	
			$query = $this->EE->db->get('extensions');
		
			if ($row = $query->row())
			{
				if ($row->settings)
				{
					$this->settings = strip_slashes(unserialize($row->settings));
				}
			}
		}
	}	
	
	// --------------------------------------------------------------------
	
	/**
	  *  Show error message
	  */
	private function _show_error()
	{
		$this->EE->output->show_user_error('submission', $this->settings['error_message']);
	}	
	
	// --------------------------------------------------------------------
	
	/**
	  *  Log a rejected submission
	  */
	private function _log($message)
	{
		// if logging is enabled
		if ($this->settings['logging'])
		{
			// create log string
			$log = date(DATE_ATOM).' Rejected form submission at '.$this->EE->functions->create_url($this->EE->session->tracker['1']).' from '.$this->EE->input->ip_address().' ('.$message.')'.NL;
			
			// write to log file (open in append mode)
			$fh = fopen(PATH_THIRD.'snaptcha/log.txt', 'a');	
			fwrite($fh, $log);
			fclose($fh);
		}
	}	
	
	// --------------------------------------------------------------------
	
	/**
	  *  Checks if is valid license
	  */
	private function _valid_license($string)
	{
		return preg_match("/^([a-z0-9]{8})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{4})-([a-z0-9]{12})$/", trim($string));
	}	
	
	// --------------------------------------------------------------------
	
	/**
	  *  Get session cache - adds support for EE < 2.2
	  */
	private function _get_session_cache($class_name, $variable_name)
	{
		if (method_exists($this->EE->session, 'cache'))
		{
			return $this->EE->session->cache($class_name, $variable_name);
		}

		else
		{
			return (isset($this->EE->session->cache[$class_name][$variable_name]) AND $this->EE->session->cache[$class_name][$variable_name]);
		}
	}

	// --------------------------------------------------------------------
	
	/**
	  *  Set session cache - adds support for EE < 2.2
	  */
	private function _set_session_cache($class_name, $variable_name, $value)
	{
		if (method_exists($this->EE->session, 'set_cache'))
		{
			$this->EE->session->set_cache($class_name, $variable_name, $value);
		}

		else
		{
			$this->EE->session->cache[$class_name][$variable_name] = $value;
		}
	}	

	// --------------------------------------------------------------------
	
	/**
	 * Settings Form
	 */
	function settings_form($settings)
	{
		$this->EE->load->helper('form');
		$this->EE->load->library('table');
	
		$this->settings = $settings;
		
		// member register security level must be less than 3
		$member_register_security_level = ($this->settings['security_level'] < 3) ? $this->settings['security_level'] : 2;
		
		$vars = array();		
		$vars['unique_secret'] = $settings['unique_secret'];			
		$vars['valid_license'] = $this->_valid_license($settings['license_number']);
		$vars['member_registration_html'] = $this->snaptcha_field($member_register_security_level);
		$vars['log_file_not_writable'] = !is_writable(PATH_THIRD.'snaptcha/log.txt');
		
		$vars['settings'] = array
		(
			'license_number' => form_input(array(
						'name' => 'license_number', 
						'value' => $settings['license_number'],
						'style' => ($vars['valid_license'] ? '' : 'border-color: #CE0000; width: 70%;')
			)),
			'security_level' => form_dropdown(
						'security_level',
						array(3 => lang('high'), 2 => lang('medium'), 1 => lang('low')), 
						$settings['security_level']
			),
			'field_name' => form_input(
						'field_name', 
						$settings['field_name']
			),
			'logging' => form_dropdown(
						'logging',
						array(0 => lang('disabled'), 1 => lang('enabled')), 
						$settings['logging']
			),
			'member_registration_validation' => form_dropdown(
						'member_registration_validation',
						array(0 => lang('disabled'), 1 => lang('enabled')), 
						$settings['member_registration_validation']
			),
			'error_message' => form_textarea(array(
						'name' => 'error_message', 
						'value' => $settings['error_message'], 
						'rows' => '6'
			))
		);
	
		return $this->EE->load->view('settings', $vars, TRUE);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Save Settings
	 */
	function save_settings()
	{
		$this->EE->lang->loadfile('snaptcha');
		
		if (empty($_POST))
		{
			show_error($this->EE->lang->line('unauthorized_access'));
		}
		
		// check that field name prefix is at least four characters
		if (strlen($this->EE->input->post('field_name')) < 4)
		{
			show_error($this->EE->lang->line('field_name_prefix_too_short'));
		}
		
		unset($_POST['submit']);
			
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->update('extensions', array('settings' => serialize($_POST)));
	
		$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('preferences_updated'));
	}

	// --------------------------------------------------------------------
	
	/**
	 * Update Extension
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
		
		if ($current < 1.1)
		{
			// get current settings
			$this->EE->db->where('class', __CLASS__);
			$query = $this->EE->db->get('extensions');
			$settings = unserialize($query->row()->settings);
			
			// add unique_secret and member_registration_validation to settings
			$settings['unique_secret'] = $this->EE->functions->random('alpha', 13);
			$settings['member_registration_validation'] = 0;
			$this->EE->db->where('class', __CLASS__);
			$this->EE->db->update('extensions', array('settings' => serialize($settings)));
		
			// add member register hook
			$data = array(
				'class'	 	=> __CLASS__,
				'method'	=> 'member_register_validate',
				'hook'	  	=> 'member_member_register_start',
				'settings'  => serialize($this->settings),
				'priority'  => 10,
				'version'   => $this->version,
				'enabled'   => 'y'
			);	
			$this->EE->db->insert('extensions', $data);			
		}
		
		if ($current < 1.2)
		{
			// add forum hooks		
			$data = array(
				'class'	 	=> __CLASS__,
				'method'	=> 'forum_field',
				'hook'	  	=> 'forum_threads_template',
				'settings'  => serialize($this->settings),
				'priority'  => 10,
				'version'   => $this->version,
				'enabled'   => 'y'
			);	
			$this->EE->db->insert('extensions', $data);
								
			$data = array(
				'class'	 	=> __CLASS__,
				'method'	=> 'forum_validate',
				'hook'	  	=> 'forum_submit_post_start',
				'settings'  => serialize($this->settings),
				'priority'  => 10,
				'version'   => $this->version,
				'enabled'   => 'y'
			);	
			$this->EE->db->insert('extensions', $data);		
		}
		
		if (version_compare($current, '1.2.1', '<'))
		{
			// add forum hook for submission form
			$data = array(
				'class'	 	=> __CLASS__,
				'method'	=> 'forum_field',
				'hook'	  	=> 'forum_submission_form_end',
				'settings'  => serialize($this->settings),
				'priority'  => 10,
				'version'   => $this->version,
				'enabled'   => 'y'
			);	
			$this->EE->db->insert('extensions', $data);				
		}
		
		if (version_compare($current, '1.3', '<'))
		{
			// get current settings
			$this->EE->db->where('class', __CLASS__);
			$query = $this->EE->db->get('extensions');
			$settings = unserialize($query->row()->settings);
			
			// add logging to settings
			$settings['logging'] = 0;
			$this->EE->db->where('class', __CLASS__);
			$this->EE->db->update('extensions', array('settings' => serialize($settings)));
		}
		
		if (version_compare($current, '1.4', '<'))
		{
			// add zoo visitor hook
			$data = array(
				'class'	 	=> __CLASS__,
				'method'	=> 'zoo_visitor_register_validate',
				'hook'	  	=> 'zoo_visitor_register_validation_start',
				'settings'  => serialize($this->settings),
				'priority'  => 10,
				'version'   => $this->version,
				'enabled'   => 'y'
			);	
			$this->EE->db->insert('extensions', $data);		
			
			// add user module hook
			$data = array(
				'class'	 	=> __CLASS__,
				'method'	=> 'user_register_validate',
				'hook'	  	=> 'user_register_start',
				'settings'  => serialize($this->settings),
				'priority'  => 10,
				'version'   => $this->version,
				'enabled'   => 'y'
			);	
			$this->EE->db->insert('extensions', $data);		
		}
		
		if (version_compare($current, '1.4.1', '<'))
		{
			// update safecracker validation hook
			$this->EE->db->where(array(
				'class' => __CLASS__, 
				'hook' => 'safecracker_submit_entry_end'
			));
			$this->EE->db->update('extensions', array('hook' => 'safecracker_submit_entry_start'));	
		}
		
		if (version_compare($current, '1.5.1', '<'))
		{
			// add freemember hook
			$data = array(
				'class'	 	=> __CLASS__,
				'method'	=> 'freemember_register_validate',
				'hook'	  	=> 'freemember_register_validation',
				'settings'  => serialize($this->settings),
				'priority'  => 10,
				'version'   => $this->version,
				'enabled'   => 'y'
			);	
			$this->EE->db->insert('extensions', $data);	
		}

		if (version_compare($current, '1.6', '<'))
		{
			// update member validation hook
			$this->EE->db->where(array(
				'class' => __CLASS__, 
				'hook' => 'member_member_register_start'
			));
			$this->EE->db->update('extensions', array('hook' => 'member_member_register_errors'));
			

			// delete zoo visitor hook (safecracker will deal with zoo visitor)
			if (version_compare($current, '1.4', '>='))
			{
				$this->EE->db->where(array(
					'class' => __CLASS__, 
					'hook' => 'zoo_visitor_register_validation_start'
				));
				$this->EE->db->delete('extensions');	
			}


			// add proform hooks		
			$data = array(
				'class'	 	=> __CLASS__,
				'method'	=> 'proform_field',
				'hook'	  	=> 'proform_create_captcha',
				'settings'  => serialize($this->settings),
				'priority'  => 10,
				'version'   => $this->version,
				'enabled'   => 'y'
			);	
			$this->EE->db->insert('extensions', $data);
								
			$data = array(
				'class'	 	=> __CLASS__,
				'method'	=> 'proform_process_captcha',
				'hook'	  	=> 'proform_process_captcha',
				'settings'  => serialize($this->settings),
				'priority'  => 10,
				'version'   => $this->version,
				'enabled'   => 'y'
			);	
			$this->EE->db->insert('extensions', $data);
						
			$data = array(
				'class'	 	=> __CLASS__,
				'method'	=> 'proform_validate',
				'hook'	  	=> 'proform_validation_end',
				'settings'  => serialize($this->settings),
				'priority'  => 10,
				'version'   => $this->version,
				'enabled'   => 'y'
			);	
			$this->EE->db->insert('extensions', $data);	
		}
		
		
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->update(
					'extensions',
					array('version' => $this->version)
		);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 */
	 function activate_extension()
	{
		$this->EE->lang->loadfile('snaptcha');
		
		
		// default settings
		$this->settings = array(
			'license_number' => '',
			'unique_secret' => $this->EE->functions->random('alpha', 13),
			'security_level' => 3,
			'field_name' => 'snap',
			'error_message' => lang('default_error_message'),
			'member_registration_validation' => 0,
			'logging' => 0
		);
		
		
		// comment form	hooks			
		$data = array(
			'class'	 	=> __CLASS__,
			'method'	=> 'comment_field',
			'hook'	  	=> 'comment_form_tagdata',
			'settings'  => serialize($this->settings),
			'priority'  => 10,
			'version'   => $this->version,
			'enabled'   => 'y'
		);	
		$this->EE->db->insert('extensions', $data);
		
		$data = array(
			'class'	 	=> __CLASS__,
			'method'	=> 'comment_validate',
			'hook'	  	=> 'insert_comment_start',
			'settings'  => serialize($this->settings),
			'priority'  => 10,
			'version'   => $this->version,
			'enabled'   => 'y'
		);	
		$this->EE->db->insert('extensions', $data);
		
		
		// safecracker hooks			
		$data = array(
			'class'	 	=> __CLASS__,
			'method'	=> 'safecracker_field',
			'hook'	  	=> 'safecracker_entry_form_tagdata_end',
			'settings'  => serialize($this->settings),
			'priority'  => 10,
			'version'   => $this->version,
			'enabled'   => 'y'
		);	
		$this->EE->db->insert('extensions', $data);
		
		$data = array(
			'class'	 	=> __CLASS__,
			'method'	=> 'safecracker_validate',
			'hook'	  	=> 'safecracker_submit_entry_start',
			'settings'  => serialize($this->settings),
			'priority'  => 10,
			'version'   => $this->version,
			'enabled'   => 'y'
		);	
		$this->EE->db->insert('extensions', $data);
		
		
		// freeform hooks
		$data = array(
			'class'	 	=> __CLASS__,
			'method'	=> 'freeform_field',
			'hook'	  	=> 'freeform_module_form_end',
			'settings'  => serialize($this->settings),
			'priority'  => 10,
			'version'   => $this->version,
			'enabled'   => 'y'
		);
	
		$this->EE->db->insert('extensions', $data);
		
		$data = array(
			'class'	 	=> __CLASS__,
			'method'	=> 'freeform_validate',
			'hook'	  	=> 'freeform_module_validate_end',
			'settings'  => serialize($this->settings),
			'priority'  => 10,
			'version'   => $this->version,
			'enabled'   => 'y'
		);
	
		$this->EE->db->insert('extensions', $data);
		
		
		// member register hooks			
		$data = array(
			'class'	 	=> __CLASS__,
			'method'	=> 'member_register_validate',
			'hook'	  	=> 'member_member_register_errors',
			'settings'  => serialize($this->settings),
			'priority'  => 10,
			'version'   => $this->version,
			'enabled'   => 'y'
		);	
		$this->EE->db->insert('extensions', $data);

		$data = array(
			'class'	 	=> __CLASS__,
			'method'	=> 'member_register_validate_deprecated',
			'hook'	  	=> 'member_member_register_start',
			'settings'  => serialize($this->settings),
			'priority'  => 10,
			'version'   => $this->version,
			'enabled'   => 'y'
		);	
		$this->EE->db->insert('extensions', $data);
		
		
		// forum hooks			
		$data = array(
			'class'	 	=> __CLASS__,
			'method'	=> 'forum_field',
			'hook'	  	=> 'forum_threads_template',
			'settings'  => serialize($this->settings),
			'priority'  => 10,
			'version'   => $this->version,
			'enabled'   => 'y'
		);	
		$this->EE->db->insert('extensions', $data);
				
		$data = array(
			'class'	 	=> __CLASS__,
			'method'	=> 'forum_field',
			'hook'	  	=> 'forum_submission_form_end',
			'settings'  => serialize($this->settings),
			'priority'  => 10,
			'version'   => $this->version,
			'enabled'   => 'y'
		);	
		$this->EE->db->insert('extensions', $data);	
		
		$data = array(
			'class'	 	=> __CLASS__,
			'method'	=> 'forum_validate',
			'hook'	  	=> 'forum_submit_post_start',
			'settings'  => serialize($this->settings),
			'priority'  => 10,
			'version'   => $this->version,
			'enabled'   => 'y'
		);	
		$this->EE->db->insert('extensions', $data);
		
		
		// user module hooks
		$data = array(
			'class'	 	=> __CLASS__,
			'method'	=> 'user_register_validate',
			'hook'	  	=> 'user_register_start',
			'settings'  => serialize($this->settings),
			'priority'  => 10,
			'version'   => $this->version,
			'enabled'   => 'y'
		);	
		$this->EE->db->insert('extensions', $data);		
		
		
		// freemember hooks
		$data = array(
			'class'	 	=> __CLASS__,
			'method'	=> 'freemember_register_validate',
			'hook'	  	=> 'freemember_register_validation',
			'settings'  => serialize($this->settings),
			'priority'  => 10,
			'version'   => $this->version,
			'enabled'   => 'y'
		);	
		$this->EE->db->insert('extensions', $data);		
		

		// proform hooks
		$data = array(
			'class'	 	=> __CLASS__,
			'method'	=> 'proform_field',
			'hook'	  	=> 'proform_create_captcha',
			'settings'  => serialize($this->settings),
			'priority'  => 10,
			'version'   => $this->version,
			'enabled'   => 'y'
		);
		$this->EE->db->insert('extensions', $data);
		
		$data = array(
			'class'	 	=> __CLASS__,
			'method'	=> 'proform_process_captcha',
			'hook'	  	=> 'proform_process_captcha',
			'settings'  => serialize($this->settings),
			'priority'  => 10,
			'version'   => $this->version,
			'enabled'   => 'y'
		);
		$this->EE->db->insert('extensions', $data);
		
		$data = array(
			'class'	 	=> __CLASS__,
			'method'	=> 'proform_validate',
			'hook'	  	=> 'proform_validation_end',
			'settings'  => serialize($this->settings),
			'priority'  => 10,
			'version'   => $this->version,
			'enabled'   => 'y'
		);
		$this->EE->db->insert('extensions', $data);
		

		// create snaptcha table
		$this->EE->load->dbforge();
		
		$fields = array(
						'id' 			=> array(
											'type'			=> 'int',
											'constraint'	=> 11,
											'unsigned'		=> TRUE,
											'null'			=> FALSE,
											'auto_increment'=> TRUE
										),
						'name'			=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '100',
											'null'			=> FALSE,
											'default'		=> ''
										),
						'secret'  		=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '100',
											'null'			=> FALSE,
											'default'		=> ''
										),
						'timestamp'  	=> array(
											'type' 			=> 'varchar',
											'constraint'	=> '16',
											'null'			=> FALSE,
											'default'		=> ''
										),
						'ip_address' 			 => array(
											'type' 			=> 'varchar',
											'constraint'	=> '16',
											'null'			=> FALSE,
											'default'		=> ''
										)
		);
		
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table('snaptcha', TRUE);
	}

	// --------------------------------------------------------------------
	
	/**
	 * Disable Extension
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
		
		// delete snaptcha table
		$this->EE->load->dbforge();
		$this->EE->dbforge->drop_table('snaptcha');
	}
		
}
// END CLASS

/* End of file ext.snaptcha.php */
/* Location: ./system/expressionengine/third_party/snaptcha/ext.snaptcha.php */