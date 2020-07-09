<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

namespace sFire\Mail;


/**
 * Interface MailInterface
 * @package sFire\Mail
 */
interface MailInterface {


    /**
     * Try to send the mail with optional callback.
     * @param callable $closure [optional]
     */
    public function send(callable $closure = null);


    /**
     * Adds to email
     * @param string $email The email address of the receiver
     * @param string $name [optional] The name of the receiver
     */
    public function to(string $email, ?string $name = null);


    /**
     * Adds reply-to to headers
     * @param string $email The email address of the reply to
     * @param string $name [optional] The name of the reply to email address
     */
    public function reply(string $email, ?string $name = null);


    /**
     * Adds email to bcc
     * @param string $email The email address of the receiver
     * @param string $name [optional] The name of the receiver
     */
    public function bcc(string $email, ?string $name = null);


    /**
     * Adds an attachment to the email
     * @param string $file The path to the file
     * @param string $name [optional] The new name of the file including extension
     * @param string $mime [optional] The mime type of the file
     */
    public function attachment(string $file, string $name = null, string $mime = null);


    /**
     * Adds subject
     * @param string $subject The subject for the mail
     */
    public function subject(string $subject);


    /**
     * Adds email to cc
     * @param string $email The email address of the receiver
     * @param string $name [optional] The name of the receiver
     */
    public function cc(string $email, ?string $name = null);


    /**
     * Adds from email to headers
     * @param string $email The email address of the sender
     * @param string $name [optional] The name of the sender
     */
    public function from(string $email, string $name);
}