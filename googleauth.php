<?php
	/*
	* Copyright 2011 Brian Rak
	* This program is free software; you can redistribute it and/or modify
	* it under the terms of the GNU General Public License as published by
	* the Free Software Foundation; either version 2 of the License, or
	* (at your option) any later version.
	*
	* This program is distributed in the hope that it will be useful,
	* but WITHOUT ANY WARRANTY; without even the implied warranty of
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	* GNU General Public License for more details.
	*
	* You should have received a copy of the GNU General Public License
	* along with this program; if not, write to the Free Software
	* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	*/
	require_once(dirname(__FILE__).'/base32.php');

	class GoogleAuth
	{
		// Check this many codes in the past + future to deal with clock differences
		// This will accept codes starting from $skew*30 seconds ago, going to $skew*30 seconds from now
		public $skew = 5;
		
		/**
		 * checkCode
		 * 
		 * Check a code produced by Google Authenticator to confirm that it's valid.
		 * This only deals with the 6 digit codes produced by the app.  It does not handle the emergency scratch codes as these are essentially random.
		 * 
		 * @param string $secretkey Secret key in base32 (RFC 3548) format.  This format is produced by the 'google-authenticator' tool that comes with the pam module
		 * @return bool True on valid code, false otherwise
		 */	
		public function checkCode($secretkey, $code)
		{
			$b = new Base32(Base32::csRFC3548);

			$key = $b->toString($secretkey);

			$start = time()/30;
			for($i=-($this->skew); $i<=$this->skew; $i++)
			{
				$checktime = (int)($start+$i);
				$thiskey = $this->oath_hotp($key, $checktime);
				
				if ((int)$code == $this->oath_truncate($thiskey,6))
				{
					return true;
				}
				
				//echo $checktime."\t".$thiskey."\t".$this->oath_truncate($thiskey,6)."\n";
			}
			return false;
		}



		private function oath_hotp ($key, $counter)
		{
			// Counter
			//the counter value can be more than one byte long, so we need to go multiple times
			$cur_counter = array(0,0,0,0,0,0,0,0);
			for($i=7;$i>=0;$i--)
			{
				$cur_counter[$i] = pack ('C*', $counter);
				$counter = $counter >> 8;
			}
			$bin_counter = implode($cur_counter);
			// Pad to 8 chars
			if (strlen ($bin_counter) < 8)
			{
				$bin_counter = str_repeat (chr(0), 8 - strlen ($bin_counter)) . $bin_counter;
			}

			// HMAC
			$hash = hash_hmac ('sha1', $bin_counter, $key);
			return $hash;
		}

		private function oath_truncate($hash, $length = 6)
		{
			// Convert to dec
			foreach(str_split($hash,2) as $hex)
			{
				$hmac_result[]=hexdec($hex);
			}

			// Find offset
			$offset = $hmac_result[19] & 0xf;

			// Algorithm from RFC
			return
			(
				(($hmac_result[$offset+0] & 0x7f) << 24 ) |
				(($hmac_result[$offset+1] & 0xff) << 16 ) |
				(($hmac_result[$offset+2] & 0xff) << 8 ) |
				($hmac_result[$offset+3] & 0xff)
			) % pow(10,$length);
		}
	}
