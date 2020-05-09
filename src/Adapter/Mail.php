<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */ 

namespace sFire\Mail\Adapter;

use sFire\Mail\Exception\InvalidArgumentException;
use sFire\Mail\Exception\RuntimeException;
use sFire\Mail\MailInterface;
use sFire\Filecontrol\File;


/**
 * Class Mail
 * @package sFire\Mail
 */
Final class Mail implements MailInterface {


	/**
     * Contains all the mail headers
	 * @var array
	 */
	private array $headers = [];


	/**
     * Contains the subject of the mail
	 * @var string
	 */
	private ?string $subject = null;


	/**
     *
	 * @var array
	 */
	private array $message = [];


	/**
     * Contains the boundary / delimiter
	 * @var string
	 */
	private ?string $boundary = null;

	
	/**
     * Contains if mail has been successfully send
	 * @var boolean
	 */
	private bool $send = false;


	/**
     * Contains all the mail variables
	 * @var array
	 */
	private array $variables = [];


	/**
	 * Try to send the mail with optional callback.
	 * @param callable $closure [optional]
	 * @return self
	 */
	public function send(callable $closure = null): self {

		if(null !== $closure) {
			call_user_func($closure, $this);
		}

		$to 	 = $this -> formatTo();
		$headers = $this -> formatHeaders();
		$message = $this -> formatMessage();

		@mail($to, $this -> subject, $message, $headers);

		if(null === error_get_last()) {
			$this -> send = true;			
		}

		return $this;
	}


	/**
	 * Returns if mail has been successfully send
	 * @return bool
	 */
	public function success(): bool {
		return $this -> send;
	}


	/**
	 * Returns if mail has failed to send
	 * @return bool
	 */
	public function fails(): bool {
		return false === $this -> send;
	} 


	/**
	 * Adds a custom header to the mail
	 * @param string $key The key of the header
	 * @param string $value The value of the header
	 * @return self
	 */
	public function addHeader(string $key, string $value): self {

		$this -> headers['custom'] ??= [];
		$this -> headers['custom'][$key] = $value;

		return $this;
	}


	/**
	 * Adds a message in HTML by giving a string text
	 * @param string $html
	 * @return self
	 */
	public function html(string $html): self {

		$this -> message['html'] = $html;
		return $this;
	}


    /**
     * Adds a message plain text by giving a string text
     * @param string $text
     * @return self
     */
    public function text(string $text): self {

        $this -> message['text'] = $text;
        return $this;
    }


	/**
	 * Returns all the custom headers
	 * @return array
	 */
	public function getHeaders(): array {
		return false === isset($this -> headers['custom']) ? [] : $this -> headers['custom'];
	}


	/**
	 * Removes a custom header by key
	 * @param string $key The key of the header to remove
	 * @return self
	 */
	public function removeHeader(string $key): self {

		if(true === isset($this -> headers['custom'][$key])) {
			unset($this -> headers['custom'][$key]);
		}

		return $this;
	}


    /**
     * Adds an attachment to the email
     * @param string $file The path to the file
     * @param string $name [optional] The new name of the file including extension
     * @param string $mime [optional] The mime type of the file
     * @return self
     * @throws RuntimeException
     */
	public function attachment(string $file, string $name = null, string $mime = null): self {

	    $file = new File($file);

		if(false === $file -> isReadable()) {
		    throw new RuntimeException(sprintf('File "%s" passed to %s() is not an existing or readable file', $file, __METHOD__));
		}

		$this -> headers['files'] ??= [];

		$name = null !== $name ? $name : $file -> getBasename();
		$mime = null !== $mime ? $mime : $file -> getMimeType();

		$this -> headers['files'][] = (object) ['file' => $file, 'name' => $name, 'mime' => $mime];

		return $this;
	}


	/**
	 * Adds subject
	 * @param string $subject The subject for the mail
	 * @return self
	 */
	public function subject(string $subject): self {

		$this -> subject = $subject;
		return $this;
	}


	/**
	 * Assign variables to the current view
	 * @param string|array $key The key or an array of values
	 * @param mixed $value The value of the variable
     * @return self
	 */
	public function assign($key, $value = null): self {

		if(true === is_array($key)) {
			$this -> variables = array_merge_recursive($key, $this -> variables);
		}
		else {
			$this -> variables[$key] = $value;
		}

		return $this;
	}


    /**
     * Sets the priority Level between 1 and 5
     * @param int $level The priority. The higher the number, the lower the priority
     * @return self
     * @throws InvalidArgumentException
     */
	public function priority(int $level = 1): self {

		if($level < 1 || $level > 5) {
		    throw new InvalidArgumentException(sprintf('Argument 1 passed to %s() should be between 1 and 5, "%s" given', __METHOD__, $level));
		}

		$priorities = [
			
			1 => ['1 (Highest)', 'High', 'High'],
			2 => ['2 (High)', 'High', 'High'],
			3 => ['3 (Normal)', 'Normal', 'Normal'],
			4 => ['4 (Low)', 'Low', 'Low'],
			5 => ['5 (Lowest)', 'Low', 'Low'],
		];

		$this -> headers['priority'] = $priorities[$level];

		return $this;
	}


    /**
     * Adds to email
     * @param string $email The email address of the receiver
     * @param string $name [optional] The name of the receiver
     * @return self
     * @throws InvalidArgumentException
     */
	public function to(string $email, string $name = null): self {

		if(false === $this -> validateEmail($email)) {
            throw new InvalidArgumentException(sprintf('Argument 1 passed to %s() must be a valid email', __METHOD__));
		}

		$this -> headers['to'] ??= [];
		$this -> headers['to'][] = (object) ['email' => $email, 'name' => $name];

		return $this;	
	}


    /**
     * Adds from email to headers
     * @param string $email The email address of the sender
     * @param string $name [optional] The name of the sender
     * @return self
     * @throws InvalidArgumentException
     */
	public function from(string $email, string $name = null): self {

		if(false === $this -> validateEmail($email)) {
			throw new InvalidArgumentException(sprintf('Argument 1 passed to %s() must be a valid email', __METHOD__));
		}

		$this -> headers['from']   = [];
		$this -> headers['from'][] = (object) ['email' => $email, 'name' => $name];

		return $this;	
	}


    /**
     * Adds reply-to to headers
     * @param string $email The email address of the reply to
     * @param string $name [optional] The name of the reply to email address
     * @return self
     * @throws InvalidArgumentException
     */
	public function reply(string $email, string $name = null): self {

		if(false === $this -> validateEmail($email)) {
			throw new InvalidArgumentException(sprintf('Argument 1 passed to %s() must be a valid email', __METHOD__));
		}

		$this -> headers['reply-to']   = [];
		$this -> headers['reply-to'][] = (object) ['email' => $email, 'name' => $name];

		return $this;	
	}


    /**
     * Adds email to bcc
     * @param string $email The email address of the receiver
     * @param string $name [optional] The name of the receiver
     * @return self
     * @throws InvalidArgumentException
     */
	public function bcc(string $email, string $name = null): self {

		if(false === $this -> validateEmail($email)) {
			throw new InvalidArgumentException(sprintf('Argument 1 passed to %s() must be a valid email', __METHOD__));
		}

		$this -> headers['bcc'] ??= [];
		$this -> headers['bcc'][] = (object) ['email' => $email, 'name' => $name];

		return $this;	
	}


    /**
     * Adds email to cc
     * @param string $email The email address of the receiver
     * @param string $name [optional] The name of the receiver
     * @return self
     * @throws InvalidArgumentException
     */
	public function cc(string $email, string $name = null): self {

		if(false === $this -> validateEmail($email)) {
			throw new InvalidArgumentException(sprintf('Argument 1 passed to %s() must be a valid email', __METHOD__));
		}

		$this -> headers['cc'] ??= [];
		$this -> headers['cc'][] = (object) ['email' => $email, 'name' => $name];

		return $this;	
	}


    /**
     * Adds a notify email
     * @param string $email The email address of the person who will get the notify after the receiver opened the mail
     * @param string $name [optional] The name of the person who will get the notify after the receiver opened the mail
     * @return self
     * @throws InvalidArgumentException
     */
	public function notify(string $email, string $name = null): self {

		if(null !== $email && false === $this -> validateEmail($email)) {
			throw new InvalidArgumentException(sprintf('Argument 1 passed to %s() must be a valid email', __METHOD__));
		}

		$this -> headers['notify']   = [];
		$this -> headers['notify'][] = (object) ['email' => $email, 'name' => $name];

		return $this;	
	}

	
	/**
	 * Returns if a email is valid or not
	 * @param string email
	 * @return boolean
	 */
	private function validateEmail(string $email): bool {
		return false !== filter_var(trim($email), FILTER_VALIDATE_EMAIL);
	}


	/**
	 * Formats an array with emails to string
	 * @param array $emails
	 * @return null|string
	 */
	private function emailsToString(array $emails): ?string {

		$format = [];

		foreach($emails as $email) {

			if(true === isset($email -> name) && trim($email -> name) !== '') {
				$format[] = sprintf('"%s" <%s>', $email -> name, filter_var($email -> email, FILTER_SANITIZE_EMAIL));
			}
			else {
				$format[] = filter_var($email -> email, FILTER_SANITIZE_EMAIL);
			}
		}

		$emails = implode(',', $format);

		return ('' !== trim($emails)) ? $emails : null;
	}


	/**
	 * Formats the "to" email and returns it
	 * @return null|string
	 */
	private function formatTo(): ?string {

		$to = null;

		if(true === isset($this -> headers['to'])) {
			$to = $this -> emailsToString($this -> headers['to']);
		}

		return $to;
	}


	/**
	 * Formats the headers and returns it as a string
	 * @return string
	 */
	private function formatHeaders(): string {

		$headers = [];
		$files 	 = true === isset($this -> headers['files']) ? $this -> headers['files'] : [];

		//Prepare email addresses
		foreach(['BCC', 'CC', 'Reply-To', 'From'] as $type) {

			if(true === isset($this -> headers[strtolower($type)])) {
				$headers[] = sprintf("%s: %s\r\n", $type, $this -> emailsToString($this -> headers[strtolower($type)]));
			}
		}

		//Notify
		if($notify = $this -> formatNotify()) {
			
			$headers[] = sprintf("Disposition-Notification-To: %s\r\n", $notify);
			$headers[] = sprintf("X-Confirm-Reading-To: %s\r\n", $notify);
		}

		//Priority
		if(true === isset($this -> headers['priority'])) {

			$headers[] = sprintf("X-Priority: %s\r\n", 			$this -> headers['priority'][0]);
			$headers[] = sprintf("X-MSMail-Priority: %s\r\n", 	$this -> headers['priority'][1]);
			$headers[] = sprintf("Importance: %s\r\n", 			$this -> headers['priority'][2]);
		}

		//Custom headers
		if(true === isset($this -> headers['custom'])) {

			foreach($this -> headers['custom'] as $key => $value) {
				$headers[] = sprintf("%s: %s\r\n", $key, $value);
			}
		}

		//Files
		if(count($files) > 0) {
			$headers[] = sprintf("Content-Type: multipart/mixed; boundary=\"Boundary-mixed-%s\"\r\n", $this -> getBoundary());
		}
		else {
			$headers[] = sprintf("Content-Type: multipart/alternative; boundary=\"Boundary-alt-%s\"\r\n\r\n", $this -> getBoundary());
		}

		return implode('', $headers);
	}


	/**
	 * Formats the messages and returns it as a string
	 * @return string
	 */
	private function formatMessage(): string {

		$message = [];
		$files 	 = true === isset($this -> headers['files']) ? $this -> headers['files'] : [];

		if(count($files) > 0) {
			
			$message[] = sprintf("--Boundary-mixed-%s\r\n", $this -> getBoundary());
			$message[] = sprintf("Content-Type: multipart/alternative; boundary=\"Boundary-alt-%s\"\r\n\r\n", $this -> getBoundary());
		}

		if(true === isset($this -> message['text'])) {

			$message[] = sprintf("--Boundary-alt-%s\r\n", $this -> getBoundary());
			$message[] = "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";
			$message[] = "Content-Transfer-Encoding: 7bit\r\n\r\n";
			$message[] = sprintf("%s\r\n\r\n", $this -> message['text']);
		}

		if(true === isset($this -> message['html'])) {

			$message[] = sprintf("--Boundary-alt-%s\r\n", $this -> getBoundary());
			$message[] = "Content-Type: text/html; charset=\"iso-8859-1\"\r\n";
			$message[] = "Content-Transfer-Encoding: 7bit\r\n\r\n";
			$message[] = sprintf("%s\r\n\r\n", $this -> message['html']);
		}

		$message[] = sprintf("--Boundary-alt-%s--\r\n\r\n", $this -> getBoundary());

		if(count($files) > 0) {
			
			foreach($files as $attachment) {

				$stream 	= fopen($attachment -> file -> entity() -> getBasepath(), 'rb');
				$data 		= fread($stream, $attachment -> file -> getFilesize());
				$data 		= chunk_split(base64_encode($data));

				$message[] = sprintf("--Boundary-mixed-%s\r\n", $this -> getBoundary());
				$message[] = sprintf("Content-Type: %s; name=\"%s\"\r\n", $attachment -> mime, $attachment -> name);
				$message[] = "Content-Transfer-Encoding: base64\r\n";
				$message[] = "Content-Disposition: attachment \r\n\r\n";
				$message[] = sprintf("%s\r\n", $data);
			}

			$message[] = sprintf("--Boundary-mixed-%s--\r\n", $this -> getBoundary());
		}

		return implode('', $message);
	}


	/**
	 * Formats the notify email and returns it
	 * @return null|string 
	 */
	private function formatNotify(): ?string {

		$notify = null;

		if(true === isset($this -> headers['notify'])) {

			$notify = $this -> emailsToString($this -> headers['notify']);

			if(null === $notify) {

				if(true === isset($this -> headers['from'])) {
					$notify = $this -> emailsToString($this -> headers['from']);
				}
				else {
					$notify = ini_get('sendmail_from');
				}
			}
		}
		
		return $notify;
	}


	/**
	 * Generates if needed and returns the boundary
	 * @return string
	 */
	private function getBoundary(): string {

		if(null === $this -> boundary) {
			$this -> boundary = md5(date('r', time()));
		}

		return $this -> boundary;
	}
}