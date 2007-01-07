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
 * @since       File available since Release 0.1.0
 * @filesource
 */

// {{{ load dependencies

require_once 'PEAR.php';
require_once 'PEAR/ErrorStack.php';

// }}}
// {{{ constants

/**
 * The version number of Services_HyperEstraier.
 */
define('SERVICES_HYPERESTRAIER_VERSION', '@package_version@');

/**
 * Specifies debug mode.
 *
 * If set to `1', every methods check their argument datatype.
 */
if (!defined('SERVICES_HYPERESTRAIER_DEBUG')) {
    define('SERVICES_HYPERESTRAIER_DEBUG', 0);
}

// }}}
// {{{ class Services_HyperEstraier

/**
 * Root class, mostly used for error handling.
 *
 * @category    Web Services
 * @package     Services_HyperEstraier
 * @author      Ryusuke SEKIYAMA <rsky0711@gmail.com>
 * @version     Release: @package_version@
 * @since       Class available since Release 0.6.0
 * @static
 */
class Services_HyperEstraier
{
    // {{{ public methods

    /**
     * Get an instance of PEAR_ErrorStack.
     *
     * @return  object  PEAR_ErrorStack
     * @access  public
     * @static
     */
    public static function getErrorStack()
    {
        return PEAR_ErrorStack::singleton('Services_HyperEstraier');
    }

    /**
     * Push the error to the error stack.
     *
     * @param   object  $error  PEAR_Error  An error object
     * @return  void
     * @access  public
     * @static
     * @ignore
     */
    public static function pushError(PEAR_Error $error)
    {
        self::getErrorStack()->push($error->getCode(), 'error',
            array('object' => $error), $error->getMessage(),
            false, $error->getBacktrace());
    }

    // }}}
}

// }}}
// {{{ class Services_HyperEstraier

/**
 * Class for utility.
 *
 * @category    Web Services
 * @package     Services_HyperEstraier
 * @author      Ryusuke SEKIYAMA <rsky0711@gmail.com>
 * @version     Release: @package_version@
 * @since       Class available since Release 0.1.0
 * @static
 */
class Services_HyperEstraier_Utility
{
    // {{{ constants

    /**
     * HTTP request: stream funcions
     */
    const HTTP_REQUEST_STREAM = 1;

    /**
     * HTTP request: PEAR::HTTP_Request
     */
    const HTTP_REQUEST_PEAR = 2;

    /**
     * HTTP request: pecl_http
     */
    const HTTP_REQUEST_PECL = 4;

    /**
     * HTTP request: Zend_Http_Client
     */
    const HTTP_REQUEST_ZEND = 8;

    // }}}
    // {{{ private properties

    /**
     * The name of HTTP request class which is used in
     * Services_HyperEstraier_Utility::shuttleUrl().
     *
     * @var string
     * @access  private
     * @static
     */
    private static $_request;

    // }}}
    // {{{ public methods

    /**
     * Set the HTTP request class.
     *
     * @param   int $type   The type of HTTP request class.
     * - `Services_HyperEstraier_Utility::HTTP_REQUEST_STREAM' uses stream functions.
     * - `Services_HyperEstraier_Utility::HTTP_REQUEST_PEAR' uses PEAR::HTTP_Request.
     * - `Services_HyperEstraier_Utility::HTTP_REQUEST_PECL' uses pecl_http.
     * - `Services_HyperEstraier_Utility::HTTP_REQUEST_ZEND' uses Zend_Http_Client.
     * @return  bool    False if an invalid type given, else true.
     * @access  public
     * @static
     */
    public static function setHttpRequest($type)
    {
        switch ($type) {
            case self::HTTP_REQUEST_STREAM:
                require_once 'Services/HyperEstraier/Request/Stream.php';
                self::$_request = 'Services_HyperEstraier_Request_Stream';
                break;
            case self::HTTP_REQUEST_PEAR:
                require_once 'Services/HyperEstraier/Request/PEAR.php';
                self::$_request = 'Services_HyperEstraier_Request_PEAR';
                break;
            case self::HTTP_REQUEST_PECL:
                require_once 'Services/HyperEstraier/Request/PECL.php';
                self::$_request = 'Services_HyperEstraier_Request_PECL';
                break;
            case self::HTTP_REQUEST_ZEND:
                require_once 'Services/HyperEstraier/Request/Zend.php';
                self::$_request = 'Services_HyperEstraier_Request_Zend';
                break;
            default:
                return false;
        }
        return true;
    }

    /**
     * Check types of arguments.
     *
     * @param   array   $types  Pairs of the argument and the expected type.
     * @return  void
     * @throws  Services_HyperEstraier_ArgumentError
     * @access  public
     * @static
     * @ignore
     */
    public static function checkTypes()
    {
        $i = 0;
        foreach (func_get_args() as $types) {
            $i++;
            $var = array_shift($types);
            $type = gettype($var);
            if (!in_array($type, $types)) {
                $errmsg = sprintf('Argument#%d should be a kind of %s, %s given.',
                    $i, implode(' or ', $types), $type);
                throw new Services_HyperEstraier_ArgumentError($errmsg);
            }
        }
    }

    /**
     * Perform an interaction of a URL.
     *
     * @param   string  $url        A URL.
     * @param   string  $pxhost     The host name of a proxy.
     *                              If it is `null', it is not used. (optional)
     * @param   int     $pxport     The port number of the proxy. (optional)
     * @param   int     $outsec     Timeout in seconds.
     *                              If it is negative, it is not used. (optional)
     * @param   array   $reqheads   An array of extension headers.
     *                              If it is `null', it is not used. (optional)
     * @param   string  $reqbody    The pointer of the entitiy body of request.
     *                              If it is `null', "GET" method is used. (optional)
     * @return  object  Services_HyperEstraier_Response
     *                  An object into which headers and the entity body
     *                  of response are stored. On error, returns PEAR_Error.
     * @access  public
     * @static
     * @see Services_HyperEstraier_Request::perform()
     * @ignore
     */
    public static function shuttleUrl($url, $pxhost = null, $pxport = null,
            $outsec = -1, $reqheads = null, $reqbody = null)
    {
        if (is_null($reqheads)) {
            $reqheads = array();
        }
        $params = array($url, $pxhost, $pxport, $outsec, $reqheads, $reqbody);
        return call_user_func(array(self::$_request, 'perform'), $params);
    }

    /**
     * Serialize a condition object into a query string.
     *
     * @param   object  $cond   Services_HyperEstraier_Condition
     *                          which is a condition object.
     * @param   int     $depth  Depth of meta search
     * @param   int     $wwidth Whole width of a snippet
     * @param   int     $hwidth Width of strings picked up from the beginning of the text
     * @param   int     $awidth Width of strings picked up around each highlighted word
     * @return  string  The serialized string.
     * @access  public
     * @static
     * @ignore
     */
    public static function conditionToQuery(Services_HyperEstraier_Condition $cond,
            $depth, $wwidth, $hwidth, $awidth)
    {
        $query = '';
        if ($phrase = $cond->getPhrase()) {
            $query .= '&phrase=' . urlencode($phrase);
        }
        if ($attrs = $cond->getAttributes()) {
            $i = 0;
            foreach ($attrs as $attr) {
                $query .= '&attrs' . strval(++$i) . '=' . urlencode($attr);
            }
        }
        if (strlen($order = $cond->getOrder()) > 0) {
            $query .= '&order=' . urlencode($order);
        }
        $query .= '&max=' . strval((($max = $cond->getMax()) >= 0) ? $max : 1 << 30);
        if (($options = $cond->getOptions()) > 0) {
            $query .= '&options=' . strval($options);
        }
        $query .= '&auxiliary=' . strval($cond->getAuxiliary());
        if (strlen($distinct = $cond->getDistinct()) > 0) {
            $query .= '&distinct=' . urlencode($distinct);
        }
        if ($depth > 0) {
            $query .= '&depth=' . strval($depth);
        }
        $query .= '&wwidth=' . strval($wwidth);
        $query .= '&hwidth=' . strval($hwidth);
        $query .= '&awidth=' . strval($awidth);
        $query .= '&skip=' . strval($cond->getSkip());
        $query .= '&mask=' . strval($cond->getMask());
        return substr($query, 1);
    }

    /**
     * Sanitize an attribute name, an attribute value or a hidden sentence.
     *
     * @param   string  $str    A non-sanitized string.
     * @return  string  The sanitized string.
     * @access  public
     * @static
     * @ignore
     */
    public static function sanitize($str)
    {
        return trim(preg_replace('/[ \\t\\r\\n\\x0B\\f]+/', ' ', $str), ' ');
    }

    /**
     * Get an instance of PEAR_ErrorStack.
     *
     * @return  object  PEAR_ErrorStack
     * @access  public
     * @static
     * @deprecated  Method deprecated in Release 0.6.0
     */
    public static function getErrorStack()
    {
        return Services_HyperEstraier::getErrorStack();
    }

    /**
     * Push the error to the error stack.
     *
     * @param   object  $error  PEAR_Error  An error object
     * @return  void
     * @access  public
     * @static
     * @deprecated  Method deprecated in Release 0.6.0
     * @ignore
     */
    public static function pushError(PEAR_Error $error)
    {
        return Services_HyperEstraier::pushError($error);
    }

    /**
     * Get the HTTP User-Agent header value
     *
     * @param   string  $type   The type of HTTP request handler.
     * @return  string  The name of HTTP User-Agent.
     * @access  public
     * @static
     * @ignore
     */
    public static function getUserAgent($type)
    {
        return sprintf('Services_HyperEstraier/%s (PHP %s + %s)',
            SERVICES_HYPERESTRAIER_VERSION, PHP_VERSION, $type);
    }

    // }}}
}

// }}}
// {{{ interface Services_HyperEstraier_Request

/**
 * Interface for HTTP request.
 *
 * @category    Web Services
 * @package     Services_HyperEstraier
 * @author      Ryusuke SEKIYAMA <rsky0711@gmail.com>
 * @version     Release: @package_version@
 * @since       Interface available since Release 0.6.0
 * @ignore
 */
interface Services_HyperEstraier_Request
{
    // {{{ public methods

    /**
     * Perform an interaction of a URL.
     *
     * @param   string  $url        A URL.
     * @param   string  $pxhost     The host name of a proxy.
     *                              If it is `null', it is not used.
     * @param   int     $pxport     The port number of the proxy.
     * @param   int     $outsec     Timeout in seconds.
     *                              If it is negative, it is not used.
     * @param   array   $reqheads   An array of extension headers.
     *                              If it is `null', it is not used.
     * @param   string  $reqbody    The pointer of the entitiy body of request.
     *                              If it is `null', "GET" method is used.
     * @return  object  Services_HyperEstraier_Response
     *                  An object into which headers and the entity body
     *                  of response are stored. On error, returns PEAR_Error.
     * @access  public
     * @static
     */
    public static function perform($url, $pxhost, $pxport, $outsec, $reqheads, $reqbody);

    // }}}
}

// }}}
// {{{ interface Services_HyperEstraier_Response

/**
 * Interface for HTTP response.
 *
 * @category    Web Services
 * @package     Services_HyperEstraier
 * @author      Ryusuke SEKIYAMA <rsky0711@gmail.com>
 * @version     Release: @package_version@
 * @since       Interface available since Release 0.6.0
 * @ignore
 */
interface Services_HyperEstraier_Response
{
    // {{{ getter methods

    /**
     * Determine success.
     *
     * @return  bool    True if the status code is 200, else false.
     * @access  public
     */
    public function isSuccess();

    /**
     * Determine error.
     *
     * @return  bool    True if the status code is not 200, else false.
     * @access  public
     */
    public function isError();

    /**
     * Get the status code.
     *
     * @return  int     The status code of the response.
     * @access  public
     */
    public function getResponseCode();

    /**
     * Get the value of a header.
     *
     * @param   string  $name  The name of a header.
     * @return  string  The value of the header.
     *                  If it does not exist, returns `null'.
     * @access  public
     */
    public function getResponseHeader($name);

    /**
     * Get a hash of headers.
     *
     * @return  array   All response headers.
     * @access  public
     */
    public function getResponseHeaders();

    /**
     * Get the entity body of response,
     *
     * @return  string  The entity body of response.
     * @access  public
     */
    public function getResponseBody();

    // }}}
}

// }}}
// {{{ class Services_HyperEstraier_ArgumentError

/**
 * Exception for the argument error.
 *
 * @category    Web Services
 * @package     Services_HyperEstraier
 * @author      Ryusuke SEKIYAMA <rsky0711@gmail.com>
 * @version     Release: @package_version@
 * @since       Class available since Release 0.1.0
 * @ignore
 */
class Services_HyperEstraier_ArgumentError extends InvalidArgumentException
{
    // Just a rename of InvalidArgumentException class.
}

// }}}
// {{{ set the default HTTP request type

Services_HyperEstraier_Utility::setHttpRequest(Services_HyperEstraier_Utility::HTTP_REQUEST_STREAM);

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
