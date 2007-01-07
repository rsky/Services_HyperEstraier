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

require_once 'Zend/Http/Client.php';
require_once 'Services/HyperEstraier.php';
require_once 'Services/HyperEstraier/Response/Zend.php';

// }}}
// {{{ class Services_HyperEstraier_Request_Zend

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
class Services_HyperEstraier_Request_Zend implements Services_HyperEstraier_Request
{
    // {{{ public methods (Services_HyperEstraier_Request implementation)

    /**
     * Perform an interaction of a URL by using Zend_Http_Client.
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
     * @return  object  Services_HyperEstraier_Response_ZEND
     *                  An object into which headers and the entity body
     *                  of response are stored. On error, returns PEAR_Error.
     * @access  public
     * @static
     * @uses    PEAR
     * @uses    Zend_Http_Client
     */
    public static function perform($url, $pxhost, $pxport, $outsec, $reqheads, $reqbody)
    {
        // Zend Framework (Preview 0.2.0) does not support HTTP proxy.
        if (!is_null($pxhost) || !is_null($pxport)) {
            $err = PEAR::raiseError("HTTP proxy is not supported.");
            Services_HyperEstraier::pushError($err);
            return $err;
        }

        // reformat headers
        $reqheads['user-agent'] = Services_HyperEstraier_Utility::getUserAgent('Zend_Http_Client');
        $headers = array();
        foreach ($reqheads as $key => $value) {
            $headers[] = sprintf('%s: %s', $key, $value);
        }

        try {
            // create an instance of Zend_Http_Client
            $req = new Zend_Http_Client($url, $headers);

            // set request timeout
            if ($outsec >= 0) {
                $req->setTimeout($outsec);
            }

            // send the request
            if (is_null($reqbody)) {
                $res = $req->get();
            } else {
                $res = $req->post($reqbody);
            }
        } catch (Zend_Http_Client_Exception $ex) {
            $err = PEAR::raiseError($ex->getMessage());
            Services_HyperEstraier::pushError($err);
            return $err;
        }

        return new Services_HyperEstraier_Response_Zend($res);
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
