<?php
/**
 * sFire Framework (https://sfire.io)
 *
 * @link      https://github.com/sfire-framework/ for the canonical source repository
 * @copyright Copyright (c) 2014-2020 sFire Framework.
 * @license   http://sfire.io/license BSD 3-CLAUSE LICENSE
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use sFire\Mail\Adapter\Mail;

final class MailTest extends TestCase {


    /**
     * Contains instance of Mail
     * @var null|Mail
     */
    private ?Mail $mail = null;


    /**
     * Setup. Created new Mail cache instance
     * @return void
     */
    protected function setUp(): void {
        $this -> mail = new Mail();
    }


    /**
     * Test if cache can be stored and retrieved
     * @return void
     */
    public function test(): void {
    }
}