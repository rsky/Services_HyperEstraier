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

require_once 'Services/HyperEstraier/Utility.php';

// }}}
// {{{ class Services_HyperEstraier_ResultDocument

/**
 * Abstraction document in result set.
 *
 * @category    Web Services
 * @package     Services_HyperEstraier
 * @author      Ryusuke SEKIYAMA <rsky0711@gmail.com>
 * @version     Release: @package_version@
 */
class Services_HyperEstraier_ResultDocument
{
    // {{{ properties

    /**
     * The URI of the result document object
     *
     * @var string
     * @access  private
     */
    private $_uri;

    /**
     * A list of attribute names
     *
     * @var array
     * @access  private
     */
    private $_attrs;

    /**
     * Snippet of a result document object
     *
     * @var string
     * @access  private
     */
    private $_snippet;

    /**
     * The keyword vector
     *
     * @var string
     * @access  private
     */
    private $_keywords;

    // }}}
    // {{{ constructor

    /**
     * Create a result document object.
     *
     * @param   string  $uri        The URI of the result document object.
     * @param   array   $attrs      A list of attribute names.
     * @param   string  $snippet    The snippet of a result document object
     * @param   string  $keywords   Keywords of the result document object.
     * @access  public
     */
    public function __construct($uri, array $attrs, $snippet, $keywords)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($uri, 'string'), array($snippet, 'string'), array($keywords, 'string')
        );
        $this->_uri = $uri;
        $this->_attrs = $attrs;
        $this->_snippet = $snippet;
        $this->_keywords = $keywords;
    }

    // }}}
    // {{{ overloading methods

    /**
     * Allow to get private/protected property from outside of the instance.
     *
     * @param   string  $name   The name of a property.
     * @return  mixed   The value of the property.
     *                  If it does not exist, generates a user-level notice message
     *                  and returns `null'.
     * @access  private
     */
    private function __get($name)
    {
        $name = '_' . $name;
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        $errmsg = sprintf('Undefined property: %s::%s', __CLASS__, $name);
        trigger_error($errmsg, E_USER_NOTICE);
        return null;
    }

    // }}}
    // {{{ getter methods

    /**
     * Get the URI.
     *
     * @return  string  The URI of the result document object.
     * @access  public
     */
    public function getUri()
    {
        return $this->_uri;
    }

    /**
     * Get a list of attribute names.
     *
     * @return  array   A list of attribute names.
     * @access  public
     */
    public function getAttributeNames()
    {
        $names = array_keys($this->_attrs);
        sort($names);
        return $names;
    }

    /**
     * Get the value of an attribute.
     *
     * @param   string  $name   The name of an attribute.
     * @return  string  The value of the attribute. If it does not exist, returns `null'.
     * @access  public
     */
    public function getAttribute($name)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($name, 'string')
        );
        return (isset($this->_attrs[$name])) ? $this->_attrs[$name] : null;
    }

    /**
     * Get the snippet of a result document object.
     *
     * @return  string  The snippet of the result document object.
     *                  There are tab separated values.
     *                  Each line is a string to be shown.
     *                  Though most lines have only one field,
     *                  some lines have two fields.
     *                  If the second field exists, the first field isto be shown with
     *                  highlighted, and the second field means its normalized form.
     * @access  public
     */
    public function getSnippet()
    {
        return $this->_snippet;
    }

    /**
     * Get keywords.
     *
     * @return  string  Serialized keywords of the result document object.
     *                  There are tab separated values.
     *                  Keywords and their scores come alternately.
     * @access  public
     */
    public function getKeywords()
    {
        return $this->_keywords;
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
