<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Logging Class
 *
 * @package		Worldfone4xs
 * @subpackage	Libraries
 * @category	Logging
 * @author		dung.huynh@southlecom.vn
 */

class WFF_Log extends CI_Log {

	public function write_log($level, $msg)
	{
		if ($this->_enabled === FALSE)
		{
			return FALSE;
		}

		$level = strtoupper($level);
		// Implement for WFF
		$this->notify_error($level, $msg);

		if (( ! isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold))
			&& ! isset($this->_threshold_array[$this->_levels[$level]]))
		{
			return FALSE;
		}

		$filepath = $this->_log_path.'log-'.date('Y-m-d').'.'.$this->_file_ext;
		$message = '';

		if ( ! file_exists($filepath))
		{
			$newfile = TRUE;
			// Only add protection to php files
			if ($this->_file_ext === 'php')
			{
				$message .= "<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>\n\n";
			}
		}

		if ( ! $fp = @fopen($filepath, 'ab'))
		{
			return FALSE;
		}

		flock($fp, LOCK_EX);

		// Instantiating DateTime with microseconds appended to initial date is needed for proper support of this format
		if (strpos($this->_date_fmt, 'u') !== FALSE)
		{
			$microtime_full = microtime(TRUE);
			$microtime_short = sprintf("%06d", ($microtime_full - floor($microtime_full)) * 1000000);
			$date = new DateTime(date('Y-m-d H:i:s.'.$microtime_short, $microtime_full));
			$date = $date->format($this->_date_fmt);
		}
		else
		{
			$date = date($this->_date_fmt);
		}

		$message .= $this->_format_line($level, $date, $msg);

		for ($written = 0, $length = self::strlen($message); $written < $length; $written += $result)
		{
			if (($result = fwrite($fp, self::substr($message, $written))) === FALSE)
			{
				break;
			}
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		if (isset($newfile) && $newfile === TRUE)
		{
			chmod($filepath, $this->_file_permissions);
		}

		return is_int($result);
	}

	function notify_error($level, $msg)
	{
		if($level == "ERROR") 
		{
			$CI =& get_instance();
			if($CI->load->is_loaded('session') && $CI->session->userdata("my_session_id")) 
			{
				$message = "<b>" . $msg . "</b><br>";
				$message .= "In: <i>" . base_url($CI->uri->uri_string()) . "</i>";
				$CI->session->set_userdata("phperror", $message);
			}
		}
	}
}