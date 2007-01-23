<?php
/**
 * PHP interface of Hyper Estraier
 *
 * A porting of estraierpure.rb which is a part of Hyper Estraier.
 *
 * Hyper Estraier is a full-text search system. You can search lots of
 * documents for some documents including specified words. If you run a web
 * site, it is useful as your own search engine for pages in your site.
 * Also, it is useful as search utilities of mail boxes and file servers.
 *
 * PHP version 5
 *
 * Copyright (c) 2005-2007 Ryusuke SEKIYAMA. All rights reserved.
 *
 * Permission is hereby granted, free of charge, to any personobtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 * @category    Web Services
 * @package     Services_HyperEstraier
 * @author      Ryusuke SEKIYAMA <rsky0711@gmail.com>
 * @copyright   2005-2007 Ryusuke SEKIYAMA
 * @license     http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version     SVN: $Id:$
 * @link        http://page2.xrea.jp/  (Project web site)
 * @link        http://hyperestraier.sourceforge.net/  (Hyper Estraier)
 * @since       File available since Release 0.6.0
 * @filesource
 */

// {{{ load dependencies

require_once 'PEAR/ErrorStack.php';

// }}}
// {{{ class Services_HyperEstraier

/**
 * Class for error handling.
 *
 * @category    Web Services
 * @package     Services_HyperEstraier
 * @author      Ryusuke SEKIYAMA <rsky0711@gmail.com>
 * @version     Release: @package_version@
 * @since       Class available since Release 0.6.0
 * @static
 */
class Services_HyperEstraier_Error
{
    // {{{ constants

    /**
     * error code: invalid argument
     */
    const INVALID_ARGUMENT = -1;

    /**
     * error code: failed to connect the node server
     */
    const CONNECTION_FAILED = -2;

    /**
     * error code: connection timed out
     */
    const CONNECTION_TIMEDOUT = -3;

    /**
     * error code: non HTTP response
     */
    const MALFORMED_RESPONSE = -4;

    /**
     * error code: HTTP status was not 2XX
     */
    const HTTP_NOT_2XX = -5;

    // }}}
    // {{{ private properties

    /**
     * An instance of PEAR_ErrorStack
     *
     * @var object PEAR_ErrorStack
     * @access  private
     */
    private static $_stack = null;

    // }}}
    // {{{ public methods

    /**
     * Get an instance of PEAR_ErrorStack.
     *
     * @return  object  PEAR_ErrorStack
     * @access  public
     * @static
     */
    public static function getStack()
    {
        if (!self::$_stack) {
            self::$_stack = &PEAR_ErrorStack::singleton('Services_HyperEstraier');
        }
        return self::$_stack;
    }

    /**
     * Push an error callback.
     *
     * @param   callback    $callback   Error callback function/method.
     * @return  void
     * @access  public
     * @static
     * @see PEAR_ErrorStack::pushCallback()
     */
    public static function pushCallback($callback)
    {
        self::getStack()->pushCallback($callback);
    }

    /**
     * Pop an error callback.
     *
     * @return  callback|false
     * @access  public
     * @static
     * @see PEAR_ErrorStack::popCallback()
     */
    public static function popCallback()
    {
        return self::getStack()->popCallback();
    }

    /**
     * Add an error to the stack.
     *
     * @param   int     $code       Error code.
     * @param   string  $message    Error message.
     * @param   string  $level      Error level.
     * @param   array   $params     Associated array of error parameters.
     * @param   array   $repackage  Associated array of repackaed
     *                              error/exception classes.
     * @param   array   $backtrace  Error backtrace.
     * @return  PEAR_Error|array|Exception
     * @access  public
     * @static
     * @see PEAR_ErrorStack::push()
     */
    public static function push($code, $message = false,
                                $level = 'error', $params = array(),
                                $repackage = false, $backtrace = false)
    {
        if (!$backtrace) {
            $backtrace = debug_backtrace();
        }
        return self::getStack()->push($code, $level, $params,
                                      $message, $repackage, $backtrace);
    }

    /**
     * Pop an error off of the error stack.
     *
     * @return  array|false
     * @access  public
     * @static
     * @see PEAR_ErrorStack::pop()
     */
    public static function pop()
    {
        return self::getStack()->pop();
    }

    /**
     * Determine whether there are any errors on the stack.
     *
     * @param   string  $level  Level name.
     * @return  bool
     * @access  public
     * @static
     * @see PEAR_ErrorStack::hasErrors()
     */
    public static function hasErrors($level = false)
    {
        return self::getStack()->hasErrors($level);
    }

    /**
     * Retrieve all errors since last purge.
     *
     * @param   bool    $purge  Whether empty the error statck or not.
     * @param  string   $level  Level name.
     * @return  array
     * @access  public
     * @static
     * @see PEAR_ErrorStack::getErrors()
     */
    public static function getErrors($purge = false, $level = false)
    {
        return self::getStack()->getErrors($purge, $level);
    }

    // }}}
}

// }}}

/*
 * Local variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=iso-8859-1 ai et ts=4 sw=4 sts=4 fdm=marker:
