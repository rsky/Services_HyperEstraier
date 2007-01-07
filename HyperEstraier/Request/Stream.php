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
require_once 'Services/HyperEstraier/Response/Stream.php';

// }}}
// {{{ class Services_HyperEstraier_Request_Stream

/**
 * Implementation of HTTP request.
 *
 * @category    Web Services
 * @package     Services_HyperEstraier
 * @author      Ryusuke SEKIYAMA <rsky0711@gmail.com>
 * @version     Release: @package_version@
 * @since       Class available since Release 0.6.0
 * @ignore
 */
class Services_HyperEstraier_Request_Stream implements Services_HyperEstraier_Request
{
    // {{{ public methods (Services_HyperEstraier_Request implementation)

    /**
     * Perform an interaction of a URL by using stream functions.
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
     * @return  object  Services_HyperEstraier_Response_Stream
     *                  An object into which headers and the entity body
     *                  of response are stored. On error, returns PEAR_Error.
     * @access  public
     * @static
     * @uses    PEAR
     */
    public static function perform($url, $pxhost, $pxport, $outsec, $reqheads, $reqbody)
    {
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
        $reqheads['user-agent'] = Services_HyperEstraier_Utility::getUserAgent('stream');
        $params['http']['header'] = '';
        foreach ($reqheads as $key => $value) {
            $params['http']['header'] .= sprintf("%s: %s\r\n", $key, $value);
        }
        $context = stream_context_create($params);

        // open a stream and send the request
        $fp = fopen($url, 'r', false, $context);
        if (!$fp) {
            $err = PEAR::raiseError(sprintf('Cannot connect to %s.', $url));
            self::push_error($err);
            return $err;
        }
        if ($outsec >= 0) {
            stream_set_timeout($fp, $outsec);
        }

        // get the response body
        $body = stream_get_contents($fp);

        // parse the response headers
        $meta_data = stream_get_meta_data($fp);
        if (!empty($meta_data['timed_out'])) {
            fclose($fp);
            $err = PEAR::raiseError('Connection timed out.');
            Services_HyperEstraier::pushError($err);
            return $err;
        }
        if (strcasecmp($meta_data['wrapper_type'], 'cURL') == 0) {
            $raw_headers = $meta_data['wrapper_data']['headers'];
        } else {
            $raw_headers = $meta_data['wrapper_data'];
        }
        $http_status = array_shift($raw_headers);
        if (!preg_match('!^HTTP/(.+?) (\\d+) ?(.*)!', $http_status, $matches)) {
            fclose($fp);
            $err = PEAR::raiseError('Malformed response.');
            Services_HyperEstraier::pushError($err);
            return $err;
        }
        $code = (int)$matches[2];
        $headers = array();
        foreach ($raw_headers as $header) {
            list($name, $value) = explode(':', $header, 2);
            $headers[strtolower($name)] = ltrim($value);
        }

        // close the stream
        fclose($fp);

        return new Services_HyperEstraier_Response_Stream($code, $headers, $body);
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
