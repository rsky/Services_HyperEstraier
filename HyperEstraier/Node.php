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
require_once 'Services/HyperEstraier/Condition.php';
require_once 'Services/HyperEstraier/Document.php';
require_once 'Services/HyperEstraier/NodeResult.php';
require_once 'Services/HyperEstraier/ResultDocument.php';

// }}}
// {{{ class Services_HyperEstraier_Node

/**
 * Abstraction of connection to P2P node.
 *
 * @category    Web Services
 * @package     Services_HyperEstraier
 * @author      Ryusuke SEKIYAMA <rsky0711@gmail.com>
 * @version     Release: @package_version@
 * @uses        PEAR
 */
class Services_HyperEstraier_Node
{
    // {{{ constants

    /**
     * mode: delete the account
     */
    const USER_DELETE = 0;

    /**
     * mode: set the account as an administrator
     */
    const USER_ADMIN = 1;

    /**
     * mode: set the account as a guest
     */
    const USER_GUEST = 2;

    // }}}
    // {{{ properties

    /**
     * The URL of a node server
     *
     * @var string
     * @access  private
     */
    private $_url;

    /**
     * The host name of a proxy server
     *
     * @var string
     * @access  private
     */
    private $_pxhost;

    /**
     * The port number of the proxy server
     *
     * @var int
     * @access  private
     */
    private $_pxport;

    /**
     * Timeout of the connection in seconds
     *
     * @var int
     * @access  private
     */
    private $_timeout;

    /**
     * The authentication information
     *
     * @var string
     * @access  private
     */
    private $_auth;

    /**
     * The name of the node
     *
     * @var string
     * @access  private
     */
    private $_name;

    /**
     * The label of the node
     *
     * @var string
     * @access  private
     */
    private $_label;

    /**
     * The number of documents
     *
     * @var int
     * @access  private
     */
    private $_dnum;

    /**
     * The number of unique words
     *
     * @var int
     * @access  private
     */
    private $_wnum;

    /**
     * The size of the datbase
     *
     * @var float
     * @access  private
     */
    private $_size;

    /**
     * Names of administrators
     *
     * @var array
     * @access  private
     */
    private $_admins;

    /**
     * Names of users
     *
     * @var array
     * @access  private
     */
    private $_users;

    /**
     * Expressions of links.
     *
     * @var array
     * @access  private
     */
    private $_links;

    /**
     * Whole width of a snippet
     *
     * @var int
     * @access  private
     */
    private $_wwidth;

    /**
     * Width of strings picked up from the beginning of the text
     *
     * @var int
     * @access  private
     */
    private $_hwidth;

    /**
     * Width of strings picked up around each highlighted word
     *
     * @var int
     * @access  private
     */
    private $_awidth;

    /**
     * The status code of the response
     *
     * @var int
     * @access  private
     */
    private $_status;

    // }}}
    // {{{ constructor

    /**
     * Create a node connection object.
     *
     * @access  public
     */
    public function __construct()
    {
        $this->_url = null;
        $this->_pxhost = null;
        $this->_pxport = -1;
        $this->_timeout = -1;
        $this->_auth = null;
        $this->_name = null;
        $this->_label = null;
        $this->_dnum = -1;
        $this->_wnum = -1;
        $this->_size = -1.0;
        $this->_admins = null;
        $this->_users = null;
        $this->_links = null;
        $this->_wwidth = 480;
        $this->_hwidth = 96;
        $this->_awidth = 96;
        $this->_status = -1;
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
    // {{{ setter methods

    /**
     * Set the URL of a node server.
     *
     * @param   string  $url    The URL of a node.
     * @return  void
     * @access  public
     */
    public function setUrl($url)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($url, 'string')
        );
        $this->_url = $url;
    }

    /**
     * Set the URL of a node server.
     *
     * @param   string  $host   The host name of a proxy server.
     * @param   int     $port   The port number of the proxy server.
     * @return  void
     * @access  public
     */
    public function setProxy($host, $port)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($host, 'string'), array($port, 'integer')
        );
        $this->_pxhost = $host;
        $this->_pxport = $port;
    }

    /**
     * Set timeout of a connection.
     *
     * @param   int     $sec    Timeout of the connection in seconds.
     * @return  void
     * @access  public
     */
    public function setTimeout($sec)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($sec, 'integer')
        );
        $this->_timeout = $sec;
    }

    /**
     * Set the authentication information.
     *
     * @param   string  $name       The name of authentication.
     * @param   string  $password   The password of the authentication.
     * @return  void
     * @access  public
     */
    public function setAuth($name, $password)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($name, 'string'), array($password, 'string')
        );
        $this->_auth = $name . ':' . $password;
    }

    /**
     * Set width of snippet in the result.
     *
     * @param   int     $wwidth  Whole width of a snippet.
     *                           By default, it is 480.
     *                           If it is 0, no snippet is sent.
     *                           If it is negative, whole body text is sent instead of snippet.
     * @param   int     $hwidth  Width of strings picked up from the beginning of the text.
     *                           By default, it is 96.
     *                           If it is negative 0, the current setting is not changed.
     * @param   int     $awidth  Width of strings picked up around each highlighted word.
     *                           By default, it is 96.
     *                           If it is negative, the current setting is not changed.
     * @return  void
     * @access  public
     */
    public function setSnippetWidth($wwidth, $hwidth, $awidth)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($wwidth, 'integer'), array($hwidth, 'integer'),
            array($awidth, 'integer')
        );
        $this->_wwidth = $wwidth;
        if ($hwidth >= 0) {
            $this->_hwidth = $hwidth;
        }
        if ($awidth >= 0) {
            $this->_awidth = $awidth;
        }
    }

    // }}}
    // {{{ getter methods

    /**
     * Get the name.
     *
     * @return  string  The name.
     *                  On error, returns `null'.
     * @access  public
     */
    public function getName()
    {
        if (is_null($this->_name)) {
            $this->_setInfo();
        }
        return $this->_name;
    }

    /**
     * Get the label.
     *
     * @return  string  The label.
     *                  On error, returns `null'.
     * @access  public
     */
    public function getLabel()
    {
        if (is_null($this->_label)) {
            $this->_setInfo();
        }
        return $this->_label;
    }

    /**
     * Get the number of documents.
     *
     * @return  int     The number of documents.
     *                  On error, returns -1.
     * @access  public
     */
    public function docNum()
    {
        if ($this->_dnum < 0) {
            $this->_setInfo();
        }
        return $this->_dnum;
    }

    /**
     * Get the number of unique words.
     *
     * @return  int     The number of unique words.
     *                  On error, returns -1.
     * @access  public
     */
    public function wordNum()
    {
        if ($this->_wnum < 0) {
            $this->_setInfo();
        }
        return $this->_wnum;
    }

    /**
     * Get the size of the datbase.
     *
     * @return  float   The size of the datbase.
     *                  On error, returns -1.0.
     * @access  public
     */
    public function getSize()
    {
        if ($this->_size < 0.0) {
            $this->_setInfo();
        }
        return $this->_size;
    }

    /**
     * Get an array of names of administrators.
     *
     * @return  array   Names of administrators.
     *                  On error, returns `null'.
     * @access  public
     */
    public function getAdmins()
    {
        if (is_null($this->_admins)) {
            $this->_setInfo();
        }
        return $this->_admins;
    }

    /**
     * Get an array of names of users.
     *
     * @return  array   Names of users.
     *                  On error, returns `null'.
     * @access  public
     */
    public function getUsers()
    {
        if (is_null($this->_users)) {
            $this->_setInfo();
        }
        return $this->_users;
    }

    /**
     * Get an array of expressions of links.
     *
     * @return  array   Expressions of links.
     *                  Each element is a TSV string and has three fields of
     *                  the URL,the label, and the score.
     *                  On error, returns `null'.
     * @access  public
     */
    public function getLinks()
    {
        if (is_null($this->_links)) {
            $this->_setInfo();
        }
        return $this->_links;
    }

    /**
     * Get the status code of the last request.
     *
     * @return  int     The status code of the last request.
     *                  -1 means failure of connection.
     * @access  public
     */
    public function getStatus()
    {
        return $this->_status;
    }

    // }}}
    // {{{ document manipulation methods

    /**
     * Add a document.
     *
     * @param   object  $doc    Services_HyperEstraier_Document
     *                          which is a document object.
     *                          The document object should have the URI attribute.
     * @return  bool    True if success, else false.
     * @access  public
     */
    public function putDocument(Services_HyperEstraier_Document $doc)
    {
        $this->_status = -1;
        if (!$this->_url) {
            return false;
        }
        $turl = $this->_url . '/put_doc';
        $reqheads = array('content-type' => 'text/x-estraier-draft');
        if ($this->_auth) {
            $reqheads['authorization'] = 'Basic ' . base64_encode($this->_auth);
        }
        $reqbody = $doc->dumpDraft();
        $res = Services_HyperEstraier_Utility::shuttleUrl($turl,
            $this->_pxhost, $this->_pxport, $this->_timeout, $reqheads, $reqbody);
        if (!$res) {
            return false;
        }
        $this->_status = $res->getResponseCode();
        return $res->isSuccess();
    }

    /**
     * Remove a document.
     *
     * @param   int     $id     The ID number of a registered document.
     * @return  bool    True if success, else false.
     * @access  public
     */
    public function outDocument($id)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($id, 'integer')
        );
        $this->_status = -1;
        if (!$this->_url) {
            return false;
        }
        $turl = $this->_url . '/out_doc';
        $reqheads = array('content-type' => 'application/x-www-form-urlencoded');
        if ($this->_auth) {
            $reqheads['authorization'] = 'Basic ' . base64_encode($this->_auth);
        }
        $reqbody = 'id=' . $id;
        $res = Services_HyperEstraier_Utility::shuttleUrl($turl,
            $this->_pxhost, $this->_pxport, $this->_timeout, $reqheads, $reqbody);
        if (!$res) {
            return false;
        }
        $this->_status = $res->getResponseCode();
        return $res->isSuccess();
    }

    /**
     * Remove a document specified by URI.
     *
     * @param   string  $uri    The URI of a registered document.
     * @return  bool    True if success, else false.
     * @access  public
     */
    public function outDocumentByUri($uri)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($uri, 'string')
        );
        $this->_status = -1;
        if (!$this->_url) {
            return false;
        }
        $turl = $this->_url . '/out_doc';
        $reqheads = array('content-type' => 'application/x-www-form-urlencoded');
        if ($this->_auth) {
            $reqheads['authorization'] = 'Basic ' . base64_encode($this->_auth);
        }
        $reqbody = 'uri=' . urlencode($uri);
        $res = Services_HyperEstraier_Utility::shuttleUrl($turl,
            $this->_pxhost, $this->_pxport, $this->_timeout, $reqheads, $reqbody);
        if (!$res) {
            return false;
        }
        $this->_status = $res->getResponseCode();
        return $res->isSuccess();
    }

    /**
     * Edit attributes of a document.
     *
     * @param   object  $doc    Services_HyperEstraier_Document
     *                          which is a document object.
     * @return  bool    True if success, else false.
     * @access  public
     */
    public function editDocument(Services_HyperEstraier_Document $doc)
    {
        $this->_status = -1;
        if (!$this->_url) {
            return false;
        }
        $turl = $this->_url . '/edit_doc';
        $reqheads = array('content-type' => 'text/x-estraier-draft');
        if ($this->_auth) {
            $reqheads['authorization'] = 'Basic ' . base64_encode($this->_auth);
        }
        $reqbody = $doc->dumpDraft();
        $res = Services_HyperEstraier_Utility::shuttleUrl($turl,
            $this->_pxhost, $this->_pxport, $this->_timeout, $reqheads, $reqbody);
        if (!$res) {
            return false;
        }
        $this->_status = $res->getResponseCode();
        return $res->isSuccess();
    }

    /**
     * Retrieve a document.
     *
     * @param   int     $id     The ID number of a registered document.
     * @return  object  Services_HyperEstraier_Document
     *                  A document object.
     *                  On error, returns `null'.
     * @access  public
     */
    public function getDocument($id)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($id, 'integer')
        );
        $this->_status = -1;
        if (!$this->_url) {
            return null;
        }
        $turl = $this->_url . '/get_doc';
        $reqheads = array('content-type' => 'application/x-www-form-urlencoded');
        if ($this->_auth) {
            $reqheads['authorization'] = 'Basic ' . base64_encode($this->_auth);
        }
        $reqbody = 'id=' . $id;
        $res = Services_HyperEstraier_Utility::shuttleUrl($turl,
            $this->_pxhost, $this->_pxport, $this->_timeout, $reqheads, $reqbody);
        if (!$res) {
            return null;
        }
        $this->_status = $res->getResponseCode();
        if ($res->isError()) {
            return null;
        }
        return new Services_HyperEstraier_Document($res->getResponseBody());
    }

    /**
     * Remove a document specified by URI.
     *
     * @param   string  $uri    The URI of a registered document.
     * @return  object  Services_HyperEstraier_Document
     *                  A document object.
     *                  On error, returns `null'.
     * @access  public
     */
    public function getDocumentByUri($uri)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($uri, 'string')
        );
        $this->_status = -1;
        if (!$this->_url) {
            return null;
        }
        $turl = $this->_url . '/get_doc';
        $reqheads = array('content-type' => 'application/x-www-form-urlencoded');
        if ($this->_auth) {
            $reqheads['authorization'] = 'Basic ' . base64_encode($this->_auth);
        }
        $reqbody = 'uri=' . urlencode($uri);
        $res = Services_HyperEstraier_Utility::shuttleUrl($turl,
            $this->_pxhost, $this->_pxport, $this->_timeout, $reqheads, $reqbody);
        if (!$res) {
            return null;
        }
        $this->_status = $res->getResponseCode();
        if ($res->isError()) {
            return null;
        }
        return new Services_HyperEstraier_Document($res->getResponseBody());
    }

    /**
     * Retrieve the value of an attribute of a document.
     *
     * @param   int     $id     The ID number of a registered document.
     * @param   string  $name   The name of an attribute.
     * @return  string  The value of the attribute. If it does not exist, returns `null'.
     * @access  public
     */
    public function getDocumentAttribute($id, $name)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($id, 'integer'), array($name, 'string')
        );
        $this->_status = -1;
        if (!$this->_url) {
            return null;
        }
        $turl = $this->_url . '/get_doc_attr';
        $reqheads = array('content-type' => 'application/x-www-form-urlencoded');
        if ($this->_auth) {
            $reqheads['authorization'] = 'Basic ' . base64_encode($this->_auth);
        }
        $reqbody = 'id=' . $id . '&attr=' . urlencode($name);
        $res = Services_HyperEstraier_Utility::shuttleUrl($turl,
            $this->_pxhost, $this->_pxport, $this->_timeout, $reqheads, $reqbody);
        if (!$res) {
            return null;
        }
        $this->_status = $res->getResponseCode();
        if ($res->isError()) {
            return null;
        }
        return rtrim($res->getResponseBody(), "\n");
    }

    /**
     * Retrieve the value of an attribute of a document specified by URI.
     *
     * @param   string  $uri    The URI of a registered document.
     * @param   string  $name   The name of an attribute.
     * @return  string  The value of the attribute. If it does not exist, returns `null'.
     * @access  public
     */
    public function getDocumentAttributeByUri($uri, $name)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($uri, 'string'), array($name, 'string')
        );
        $this->_status = -1;
        if (!$this->_url) {
            return null;
        }
        $turl = $this->_url . '/get_doc_attr';
        $reqheads = array('content-type' => 'application/x-www-form-urlencoded');
        if ($this->_auth) {
            $reqheads['authorization'] = 'Basic ' . base64_encode($this->_auth);
        }
        $reqbody = 'uri=' . urlencode($uri) . '&attr=' . urlencode($name);
        $res = Services_HyperEstraier_Utility::shuttleUrl($turl,
            $this->_pxhost, $this->_pxport, $this->_timeout, $reqheads, $reqbody);
        if (!$res) {
            return null;
        }
        $this->_status = $res->getResponseCode();
        if ($res->isError()) {
            return null;
        }
        return rtrim($res->getResponseBody(), "\n");
    }

    /**
     * Extract keywords of a document.
     *
     * @param   int     $id     The ID number of a registered document.
     * @return  array   Pairs of keywords and their scores in decimal string.
     *                  On error, returns `null'.
     * @access  public
     */
    public function etchDocument($id)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($id, 'integer')
        );
        $this->_status = -1;
        if (!$this->_url) {
            return null;
        }
        $turl = $this->_url . '/etch_doc';
        $reqheads = array('content-type' => 'application/x-www-form-urlencoded');
        if ($this->_auth) {
            $reqheads['authorization'] = 'Basic ' . base64_encode($this->_auth);
        }
        $reqbody = 'id=' . $id;
        $res = Services_HyperEstraier_Utility::shuttleUrl($turl,
            $this->_pxhost, $this->_pxport, $this->_timeout, $reqheads, $reqbody);
        if (!$res) {
            return null;
        }
        $this->_status = $res->getResponseCode();
        if ($res->isError()) {
            return null;
        }
        $kwords = array();
        $lines = explode("\n", $res->getResponseBody());
        foreach ($lines as $line) {
            if (strpos($line, "\t")) {
                $pair = explode("\t", $line);
                $kwords[$pair[0]] = $pair[1];
            }
        }
        return $kwords;
    }

    /**
     * Extract keywords of a document specified by URI.
     *
     * @param   string  $uri    The URI of a registered document.
     * @return  array   Pairs of keywords and their scores in decimal string.
     *                  On error, returns `null'.
     * @access  public
     */
    public function etchDocumentByUri($uri)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($uri, 'string')
        );
        $this->_status = -1;
        if (!$this->_url) {
            return null;
        }
        $turl = $this->_url . '/etch_doc';
        $reqheads = array('content-type' => 'application/x-www-form-urlencoded');
        if ($this->_auth) {
            $reqheads['authorization'] = 'Basic ' . base64_encode($this->_auth);
        }
        $reqbody = 'uri=' . urlencode($uri);
        $res = Services_HyperEstraier_Utility::shuttleUrl($turl,
            $this->_pxhost, $this->_pxport, $this->_timeout, $reqheads, $reqbody);
        if (!$res) {
            return null;
        }
        $this->_status = $res->getResponseCode();
        if ($res->isError()) {
            return null;
        }
        $kwords = array();
        $lines = explode("\n", $res->getResponseBody());
        foreach ($lines as $line) {
            if (strpos($line, "\t")) {
                $pair = explode("\t", $line);
                $kwords[$pair[0]] = $pair[1];
            }
        }
        return $kwords;
    }

    // }}}
    // {{{ node management methods

    /**
     * Manage a user account of a node.
     *
     * @param   string  $name   The name of a user.
     * @param   int     $mode   The operation mode.
     * - `Services_HyperEstraier_Node::USER_DELETE' means to delete the account.
     * - `Services_HyperEstraier_Node::USER_ADMIN' means to set the account as an administrator.
     * - `Services_HyperEstraier_Node::USER_GUEST' means to set the account as a guest.
     * @return  bool    True if success, else false.
     * @access  public
     */
    public function setUser($name, $mode)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($name, 'string'), array($mode, 'integer')
        );
        $this->_status = -1;
        if (!$this->_url) {
            return false;
        }
        $turl = $this->_url . '/_set_user';
        $reqheads = array('content-type' => 'application/x-www-form-urlencoded');
        if ($this->_auth) {
            $reqheads['authorization'] = 'Basic ' . base64_encode($this->_auth);
        }
        $reqbody = 'name=' . urlencode($name) . '&mode=' . $mode;
        $res = Services_HyperEstraier_Utility::shuttleUrl($turl,
            $this->_pxhost, $this->_pxport, $this->_timeout, $reqheads, $reqbody);
        if (!$res) {
            return false;
        }
        $this->_status = $res->getResponseCode();
        return $res->isSuccess();
    }

    /**
     * Manage a link of a node.
     *
     * @param   string  $url    The URL of the target node of a link.
     * @param   string  $label  The label of the link.
     * @param   int     $credit  The credit of the link.
     *                           If it is negative, the link is removed.
     * @return  bool    True if success, else false.
     * @access  public
     */
    public function setLink($url, $label, $credit)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($url, 'string'), array($label, 'string'),
            array($credit, 'integer')
        );
        $this->_status = -1;
        if (!$this->_url) {
            return false;
        }
        $turl = $this->_url . '/_set_link';
        $reqheads = array('content-type' => 'application/x-www-form-urlencoded');
        if ($this->_auth) {
            $reqheads['authorization'] = 'Basic ' . base64_encode($this->_auth);
        }
        $reqbody = 'url=' . urlencode($url) . '&label=' . $label;
        if ($credit >= 0) {
            $reqbody .= '&credit=' . $credit;
        }
        $res = Services_HyperEstraier_Utility::shuttleUrl($turl,
            $this->_pxhost, $this->_pxport, $this->_timeout, $reqheads, $reqbody);
        if (!$res) {
            return false;
        }
        $this->_status = $res->getResponseCode();
        return $res->isSuccess();
    }

    // }}}
    // {{{ database management methods

    /**
     * Synchronize updating contents of the database.
     *
     * @return  bool    True if success, else false.
     * @access  public
     */
    public function sync()
    {
        $this->_status = -1;
        if (!$this->_url) {
            return false;
        }
        $turl = $this->_url . '/sync';
        $reqheads = array('content-type' => 'application/x-www-form-urlencoded');
        if ($this->_auth) {
            $reqheads['authorization'] = 'Basic ' . base64_encode($this->_auth);
        }
        $res = Services_HyperEstraier_Utility::shuttleUrl($turl,
            $this->_pxhost, $this->_pxport, $this->_timeout, $reqheads);
        if (!$res) {
            return false;
        }
        $this->_status = $res->getResponseCode();
        return $res->isSuccess();
    }

    /**
     * Optimize the database.
     *
     * @return  bool    True if success, else false.
     * @access  public
     */
    public function optimize()
    {
        $this->_status = -1;
        if (!$this->_url) {
            return false;
        }
        $turl = $this->_url . '/optimize';
        $reqheads = array('content-type' => 'application/x-www-form-urlencoded');
        if ($this->_auth) {
            $reqheads['authorization'] = 'Basic ' . base64_encode($this->_auth);
        }
        $res = Services_HyperEstraier_Utility::shuttleUrl($turl,
            $this->_pxhost, $this->_pxport, $this->_timeout, $reqheads);
        if (!$res) {
            return false;
        }
        $this->_status = $res->getResponseCode();
        return $res->isSuccess();
    }

    // }}}
    // {{{ other public methods

    /**
     * Get the ID of a document specified by URI.
     *
     * @param   string  $uri    The URI of a registered document.
     * @return  int     The ID of the document.
     *                  On error, returns -1.
     * @access  public
     */
    public function uriToId($uri)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($uri, 'string')
        );
        $this->_status = -1;
        if (!$this->_url) {
            return -1;
        }
        $turl = $this->_url . '/uri_to_id';
        $reqheads = array('content-type' => 'application/x-www-form-urlencoded');
        if ($this->_auth) {
            $reqheads['authorization'] = 'Basic ' . base64_encode($this->_auth);
        }
        $reqbody = 'url=' . urlencode($url) . '&label=' . $label;
        if ($credit >= 0) {
            $reqbody .= '&credit=' . $credit;
        }
        $res = Services_HyperEstraier_Utility::shuttleUrl($turl,
            $this->_pxhost, $this->_pxport, $this->_timeout, $reqheads, $reqbody);
        if (!$res) {
            return -1;
        }
        $this->_status = $res->getResponseCode();
        if ($res->isError()) {
            return -1;
        }
        return intval(rtrim($res->getResponseBody(), "\n"));
    }

    /**
     * Get the usage ratio of the cache.
     *
     * @return  float   The usage ratio of the cache.
     *                  On error, -1.0 is returned.
     * @access  public
     */
    public function cacheUsage()
    {
        $this->_status = -1;
        if (!$this->_url) {
            return -1.0;
        }
        $turl = $this->_url . '/cacheusage';
        $reqheads = array();
        if ($this->_auth) {
            $reqheads['authorization'] = 'Basic ' . base64_encode($this->_auth);
        }
        $res = Services_HyperEstraier_Utility::shuttleUrl($turl,
            $this->_pxhost, $this->_pxport, $this->_timeout, $reqheads, null, $res);
        if (!$res) {
            return -1.0;
        }
        $this->_status = $res->getResponseCode();
        if ($res->isError()) {
            return -1.0;
        }
        return floatval(trim($res->getResponseBody()));
    }

    /**
     * Search for documents corresponding a condition.
     *
     * @param   object  $cond   Services_HyperEstraier_Condition
     *                          which is a condition object.
     * @param   int     $depth  The depth of meta search.
     * @return  object  Services_HyperEstraier_NodeResult
     *                  A node result object.
     *                  On error, returns `null'.
     * @access  public
     */
    public function search(Services_HyperEstraier_Condition $cond, $depth)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($depth, 'integer')
        );
        $this->_status = -1;
        if (!$this->_url) {
            return null;
        }
        $turl = $this->_url . '/search';
        $reqheads = array('content-type' => 'application/x-www-form-urlencoded');
        if ($this->_auth) {
            $reqheads['authorization'] = 'Basic ' . base64_encode($this->_auth);
        }
        $reqbody = Services_HyperEstraier_Utility::conditionToQuery(
            $cond, $depth, $this->_wwidth, $this->_hwidth, $this->_awidth
        );
        $res = Services_HyperEstraier_Utility::shuttleUrl($turl,
            $this->_pxhost, $this->_pxport, $this->_timeout, $reqheads, $reqbody);
        if (!$res) {
            return null;
        }
        $this->_status = $res->getResponseCode();
        if ($res->isError()) {
            return null;
        }
        $lines = explode("\n", $res->getResponseBody());
        if (count($lines) == 0) {
            return null;
        }
        $docs = array();
        $hints = array();
        $border = $lines[0];
        $isend = false;
        $lnum = 1;
        $llen = count($lines);
        $blen = strlen($border);
        while ($lnum < $llen) {
            $line = $lines[$lnum];
            $lnum++;
            if (strlen($line) >= $blen && strpos($line, $border) === 0) {
                if (substr($line, $blen) == ':END') {
                    $isend = true;
                }
                break;
            }
            if (strpos($line, "\t")) {
                list($key, $value) = explode("\t", $line, 2);
                $hints[$key] = $value;
            }
        }
        $snum = $lnum;
        while (!$isend && $lnum < $llen) {
            $line = $lines[$lnum];
            $lnum++;
            if (strlen($line) >= $blen && strpos($line, $border) === 0) {
                if ($lnum > $snum) {
                    $rdattrs = array();
                    $sb = '';
                    $rdvector = '';
                    $rlnum = $snum;
                    while ($rlnum < $lnum - 1) {
                        $rdline = trim($lines[$rlnum]);
                        $rlnum++;
                        if (strlen($rdline) == 0) {
                            break;
                        }
                        if (substr($rdline, 0, 1) == '%') {
                            $lidx = strpos($rdline, "\t");
                            if (strpos($rdline, '%VECTOR') === 0 && $lidx) {
                                $rdvector = substr($rdline, $lidx + 1);
                            }
                        } else {
                            if (strpos($rdline, '=')) {
                                list($key, $value) = explode('=', $rdline, 2);
                                $rdattrs[$key] = $value;
                            }
                        }
                    }
                    while ($rlnum < $lnum - 1) {
                        $rdline = $lines[$rlnum];
                        $rlnum++;
                        $sb .= $rdline . "\n";
                    }
                    $rduri = $rdattrs['@uri'];
                    $rdsnippet = $sb;
                    if ($rduri) {
                        $rdoc = new Services_HyperEstraier_ResultDocument(
                            $rduri, $rdattrs, $rdsnippet, $rdvector
                        );
                        $docs[] = $rdoc;
                    }
                }
                $snum = $lnum;
                if (substr($line, $blen) == ':END') {
                    $isend = true;
                }
            }
        }
        if (!$isend) {
            return null;
        }
        return new Services_HyperEstraier_NodeResult($docs, $hints);
    }

    // }}}
    // {{{ other private methods

    /**
     * Set information of the node.
     *
     * @return  void
     * @access  private
     */
    private function _setInfo()
    {
        $this->_status = -1;
        if (!$this->_url) {
            return;
        }
        $turl = $this->_url . '/inform';
        $reqheads = array();
        if ($this->_auth) {
            $reqheads['authorization'] = 'Basic ' . base64_encode($this->_auth);
        }
        $res = Services_HyperEstraier_Utility::shuttleUrl($turl,
            $this->_pxhost, $this->_pxport, $this->_timeout, $reqheads, null, $res);
        if (!$res) {
            return;
        }
        $this->_status = $res->getResponseCode();
        if ($res->isError()) {
            return;
        }
        $lines = explode("\n", $res->getResponseBody());
        if (count($lines) == 0) {
            return;
        }
        $elems = explode("\t", $lines[0]);
        if (count($elems) != 5) {
            return;
        }
        $this->_name = $elems[0];
        $this->_label = $elems[1];
        $this->_dnum = intval($elems[2]);
        $this->_wnum = intval($elems[3]);
        $this->_size = floatval($elems[4]);
        $llen = count($lines);
        if ($llen < 2) {
            return;
        }
        $lnum = 1;
        if ($lnum < $llen && strlen($lines[$lnum]) < 1) {
            $lnum++;
        }
        $this->_admins = array();
        while ($lnum < $llen) {
            $line = $lines[$lnum];
            if (strlen($line) < 1) {
                break;
            }
            $this->_admins[] = $line;
            $lnum++;
        }
        if ($lnum < $llen && strlen($lines[$lnum]) < 1) {
            $lnum++;
        }
        $this->_users = array();
        while ($lnum < $llen) {
            $line = $lines[$lnum];
            if (strlen($line) < 1) {
                break;
            }
            $this->_users[] = $line;
            $lnum++;
        }
        if ($lnum < $llen && strlen($lines[$lnum]) < 1) {
            $lnum++;
        }
        $this->_links = array();
        while ($lnum < $llen) {
            $line = $lines[$lnum];
            if (strlen($line) < 1) {
                break;
            }
            $links = explode($line);
            if (count($links) == 3) {
                $this->_links[] = $links;
            }
            $lnum++;
        }
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
