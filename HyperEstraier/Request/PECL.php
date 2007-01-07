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
require_once 'Services/HyperEstraier/Response/PEAR.php';

// }}}
// {{{ class Services_HyperEstraier_Request_PECL

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
class Services_HyperEstraier_Request_PECL implements Services_HyperEstraier_Request
{
    // {{{ public methods (Services_HyperEstraier_Request implementation)

    /**
     * Perform an interaction of a URL by using pecl_http.
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
     * @return  object  Services_HyperEstraier_Response_PECL
     *                  An object into which headers and the entity body
     *                  of response are stored. On error, returns PEAR_Error.
     * @access  public
     * @static
     * @uses    PEAR
     * @uses    HttpRequest
     */
    public static function perform($url, $pxhost, $pxport, $outsec, $reqheads, $reqbody)
    {
        try {
            // create an instance of HttpRequest
            $req = new HttpRequest($url);
            if (is_null($reqbody)) {
                $req->setMethod(HttpRequest::METH_GET);
            } else {
                $req->setMethod(HttpRequest::METH_POST);
                $req->setRawPostData($reqbody);
                if (isset($reqheads['content-type'])) {
                    $req->setContentType($reqheads['content-type']);
                    unset($reqheads['content-type']);
                }
            }

            // set request options
            if (isset($reqheads['authorization'])) {
                $req->setOptions(array(
                    'httpauth' => base64_decode(substr($reqheads['authorization'], 6)),
                    'httpauthtype' => HTTP_AUTH_BASIC,
                ));
                unset($reqheads['authorization']);
            }
            if ($outsec >= 0) {
                $req->setOptions(array(
                    'timeout' => $outsec,
                    'connecttimeout' => $outsec,
                ));
            }
            if (!is_null($pxhost) && !is_null($pxport)) {
                $req->setOptions(array(
                    'proxyhost' => $pxhost,
                    'proxyport' => $pxport,
                ));
            }
            $req->setOptions(array(
                'useragent' => Services_HyperEstraier_Utility::getUserAgent('pecl_http'),
                'headers' => $reqheads,
            ));

            // send the request
            $req->send();
        } catch (HttpException $ex) {
            $err = PEAR::raiseError($ex->getMessage());
            Services_HyperEstraier::pushError($err);
            return $err;
        }

        return new Services_HyperEstraier_Response_PECL($req);
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
