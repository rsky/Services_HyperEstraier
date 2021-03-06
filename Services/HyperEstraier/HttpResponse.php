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

// {{{ class Services_HyperEstraier_HttpResponse

/**
 * Class for HTTP response.
 *
 * @category    Web Services
 * @package     Services_HyperEstraier
 * @author      Ryusuke SEKIYAMA <rsky0711@gmail.com>
 * @version     Release: @package_version@
 * @since       Class available since Release 0.6.0
 * @ignore
 */
class Services_HyperEstraier_HttpResponse
{
    // {{{ private properties

    /**
     * The status code
     *
     * @var int
     * @access  private
     */
    private $_code;

    /**
     * Headers of response
     *
     * @var array
     * @access  private
     */
    private $_headers;

    /**
     * The entity body of response
     *
     * @var string
     * @access  private
     */
    private $_body;


    // }}}
    // {{{ constructor

    /**
     * Create a response object.
     *
     * @param   int     $code       The status code.
     * @param   array   $headers    The hash of the headers.
     * @param   string  $body       The entity body of response,
     * @access  public
     */
    public function __construct($code, array $headers, $body)
    {
        $this->_code = $code;
        $this->_headers = $headers;
        $this->_body = $body;
    }

    // }}}
    // {{{ getter methods

    /**
     * Determine success.
     *
     * @return  bool    True if the status code is 200, else false.
     * @access  public
     */
    public function isSuccess()
    {
        return ($this->_code == 200);
    }

    /**
     * Determine error.
     *
     * @return  bool    True if the status code is not 200, else false.
     * @access  public
     */
    public function isError()
    {
        return ($this->_code != 200);
    }

    /**
     * Get the status code.
     *
     * @return  int     The status code of the response.
     * @access  public
     */
    public function getResponseCode()
    {
        return $this->_code;
    }

    /**
     * Get the value of a header.
     *
     * @param   string  $name  The name of a header.
     * @return  string  The value of the header.
     *                  If it does not exist, returns `false'.
     * @access  public
     */
    public function getResponseHeader($name)
    {
        $name = strtolower($name);
        if (isset($this->_headers[$name])) {
            return $this->_headers[$name];
        }
        return false;
    }

    /**
     * Get a hash of headers.
     *
     * @return  array   All response headers.
     * @access  public
     */
    public function getResponseHeaders()
    {
        return $this->_headers;
    }

    /**
     * Get the entity body of response,
     *
     * @return  string  The entity body of response.
     * @access  public
     */
    public function getResponseBody()
    {
        return $this->_body;
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
