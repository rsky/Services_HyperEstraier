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

require_once 'Services/HyperEstraier.php';
require_once 'Services/HyperEstraier/Error.php';
require_once 'Services/HyperEstraier/HttpResponse.php';

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
    // {{{ public methods

    /**
     * Check types of arguments.
     *
     * @param   array   $types  Pairs of the argument and the expected type.
     * @return  void
     * @throws  InvalidArgumentException
     * @access  public
     * @static
     * @ignore
     */
    public static function checkTypes()
    {
        $backtrace = debug_backtrace();
        $arguments = func_get_args();
        $i = 0;
        foreach ($arguments as $types) {
            $i++;
            $var = array_shift($types);
            $type = gettype($var);
            if (!in_array($type, $types)) {
                $params = array(
                    'argnum' => $i,
                    'expected' => implode(' or ', $types),
                    'given' => $type,
                );
                $message = vsprintf('Argument#%d should be a kind of %s, %s given.',
                                    array_values($params));
                $exception = new InvalidArgumentException($message);
                Services_HyperEstraier_Error::push(
                    Services_HyperEstraier_Error::INVALID_ARGUMENT, $message,
                    'warning', $params, array('exception' => $exception), $backtrace);
                throw $exception;
            }
        }
    }

    /**
     * Perform an interaction of a URL.
     *
     * @param   string  $url        A URL.
     * @param   string  $auth       Authentication username and password. (optional)
     * @param   string  $pxhost     The host name of a proxy.
     *                              If it is `null', it is not used. (optional)
     * @param   int     $pxport     The port number of the proxy. (optional)
     * @param   int     $outsec     Timeout in seconds.
     *                              If it is negative, it is not used. (optional)
     * @param   array   $reqheads   An array of extension headers.
     *                              If it is `null', it is not used. (optional)
     * @param   string  $reqbody    The pointer of the entitiy body of request.
     *                              If it is `null', "GET" method is used. (optional)
     * @return  object  Services_HyperEstraier_HttpResponse
     *                  An object into which headers and the entity body
     *                  of response are stored. On error, returns false.
     * @throws  InvalidArgumentException, RuntimeException
     * @access  public
     * @static
     * @ignore
     */
    public static function shuttleUrl($url, $auth = null,
            $pxhost = null, $pxport = null,
            $outsec = -1, $reqheads = null, $reqbody = null)
    {
        if (!preg_match('@^(https?://)(\\w.+)@', $url, $matches)) {
            throw new InvalidArgumentException('Invalid URL given.');
        }

        if (is_null($reqheads)) {
            $reqheads = array();
        }

        // set request parameters
        $params = array('http' => array());
        if (is_null($reqbody)) {
            $params['http']['method'] = 'GET';
        } else {
            $params['http']['method'] = 'POST';
            $params['http']['content'] = $reqbody;
            $reqheads['content-length'] = strlen($reqbody);
        }
        if (!is_null($pxhost) && !is_null($pxport)) {
            $params['http']['proxy'] = sprintf('tcp://%s:%d', $pxhost, $pxport);
        }
        $reqheads['user-agent'] = sprintf('Services_HyperEstraier/%s (PHP %s)',
                                          SERVICES_HYPERESTRAIER_VERSION,
                                          PHP_VERSION);
        if ($auth) {
            $url = $matches[1] . $auth. '@' . $matches[2];
        }
        $params['http']['header'] = '';
        foreach ($reqheads as $key => $value) {
            $params['http']['header'] .= sprintf("%s: %s\r\n", $key, $value);
        }
        $context = stream_context_create($params);

        try {
            // open a stream and send the request
            set_error_handler('services_hyperestraier_utility_open_url_error_handler', E_WARNING);
            $fp = fopen($url, 'r', false, $context);
            restore_error_handler();
            if ($outsec >= 0) {
                stream_set_timeout($fp, $outsec);
            }

            // get the response body
            $body = stream_get_contents($fp);

            // parse the response headers
            $meta_data = stream_get_meta_data($fp);
            if (!empty($meta_data['timed_out'])) {
                fclose($fp);
                throw new RuntimeException('Connection timed out.',
                                Services_HyperEstraier_Error::CONNECTION_TIMEDOUT);
            }
            if (strcasecmp($meta_data['wrapper_type'], 'cURL') == 0) {
                $raw_headers = $meta_data['wrapper_data']['headers'];
            } else {
                $raw_headers = $meta_data['wrapper_data'];
            }
            $http_status = array_shift($raw_headers);
            if (!preg_match('!^HTTP/(.+?) (\\d+) ?(.*)!', $http_status, $matches)) {
                fclose($fp);
                throw new RuntimeException('Malformed response.',
                                Services_HyperEstraier_Error::MALFORMED_RESPONSE);
            }
            $code = (int)$matches[2];
            $headers = array();
            foreach ($raw_headers as $header) {
                list($name, $value) = explode(':', $header, 2);
                $headers[strtolower($name)] = ltrim($value);
            }

            // close the stream
            fclose($fp);

            return new Services_HyperEstraier_HttpResponse($code, $headers, $body);

        } catch (RuntimeException $e) {

            restore_error_handler();
            if ($e->getCode() == Services_HyperEstraier_Error::HTTP_NOT_2XX) {
                return new Services_HyperEstraier_HttpResponse(
                                (int)$e->getMessage(), array(), '');
            }
            Services_HyperEstraier_Error::push($e->getCode(), $e->getMessage(),
                        'exception', $params, array('exception' => $e));
            return false;
        }
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

    // }}}
}

// }}}
// {{{ function services_hyperestraier_utility_open_url_error_handler

/**
 * Error handler for url fopen().
 *
 * @param   int     $errno      The level of the error raised.
 * @param   string  $errstr     The error message.
 * @param   string  $errfile    The filename that the error was raised in.
 * @param   int     $errline    The line number the error was raised at.
 * @param   array   $errcontext An array that points to the active symbol
 *                              table at the point the error occurred.
 *                              Must not modify error context.
 * @return  void
 * @throws  RuntimeException
 * @ignore
 * @see http://www.php.net/manual/en/function.set-error-handler.php
 */
function services_hyperestraier_utility_open_url_error_handler($errno, $errstr,
        $errfile = '', $errline = 0, $errcontext = null)
{
    if (preg_match('@HTTP/1\\.[01] ([1-5]\\d\\d)@', $errstr, $matches)) {
        $code = Services_HyperEstraier_Error::HTTP_NOT_2XX;
        $message = $matches[1];
    } else {
        $code = Services_HyperEstraier_Error::CONNECTION_FAILED;
        $message = $errstr;
    }
    throw new RuntimeException($message, $code);
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
