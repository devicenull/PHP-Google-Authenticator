Base32 class from http://fremnet.net/article/215/class-base32
oath_hotp and oath_truncate are from http://www.php.net/manual/en/function.hash-hmac.php#91031

This class will perform basic validation on the codes produced by Google Authenticator.  It will not validate the emergency scratch codes that are produced by the 'google-authenticator' tool.

Securely storing the keys and matching up a key to a username is left up to the code that actually uses this.  
