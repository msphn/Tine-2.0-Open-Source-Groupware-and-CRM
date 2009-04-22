<?php
/**
 * Tine 2.0
 *
 * @package     Felamimail
 * @subpackage  Controller
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Lars Kneschke <l.kneschke@metaways.de>
 * @copyright   Copyright (c) 2009 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     $Id$
 * 
 * @todo        add support for multiple backends
 * @todo        add support for caching backend(s)
 */

/**
 * message controller for Felamimail
 *
 * @package     Felamimail
 * @subpackage  Controller
 */
class Felamimail_Controller_Message extends Felamimail_Controller_Abstract
{
    /**
     * holdes the instance of the singleton
     *
     * @var Felamimail_Controller_Message
     */
    private static $_instance = NULL;
    
    /**
     * the singleton pattern
     *
     * @param $_config imap config data
     * @return Felamimail_Controller_Message
     */
    public static function getInstance() 
    {
        if (self::$_instance === NULL) {            
            self::$_instance = new Felamimail_Controller_Message();
        }
        
        return self::$_instance;
    }
    
    /**
     * send one message through smtp
     *
     * @todo use userspecific settings
     */
    public function sendMessage(Zend_Mail $_mail)
    {
        $config = array(
            'ssl' => 'tls',
            'port' => 25
        );
        $transport = new Zend_Mail_Transport_Smtp('localhost', $config);
        
        Tinebase_Smtp::getInstance()->sendMessage($_mail, $transport);
        
        $this->_getBackend()->appendMessage($_mail, 'Sent');
    }
    
    /**
     * fetch message from folder
     *
     * @param string $_globalName the complete folder name
     * @param string $_messageId the message id
     * @return Zend_Mail_Message
     */
    public function getMessage($_globalName, $_messageId)
    {        
        if($this->_getBackend()->getCurrentFolder() != $_globalName) {
            $this->_getBackend()->selectFolder($_globalName);
        }
        
        $message = $this->_getBackend()->getMessage($_messageId);
        
        return $message;
    }
    
    /**
     * fetch message from folder
     *
     * @param string $_globalName the complete folder name
     * @param string $_messageId the message id
     * @return void
     */
    public function deleteMessage($_serverId, $_globalName, $_messageId)
    {        
        if($this->_getBackend()->getCurrentFolder() != $_globalName) {
            $this->_getBackend()->selectFolder($_globalName);
        }
        
        $message = $this->_getBackend()->removeMessage($_messageId);
    }
    
    /**
     * Enter description here...
     *
     * @param unknown_type $_globalName
     * @param unknown_type $_messageId
     * @param unknown_type $from
     * @param unknown_type $to
     * @return array
     */
    public function getUid($_globalName, $from, $to = null)
    {
        if($this->_getBackend()->getCurrentFolder() != $_globalName) {
            $this->_getBackend()->selectFolder($_globalName);
        }
        
        $foundEntries = $this->_getBackend()->getUid($from, $to);
        
        return $foundEntries;
    }
    
    public function addFlags($_serverId, $_globalName, $_id, $_flags)
    {
        if($this->_getBackend()->getCurrentFolder() != $_globalName) {
            $this->_getBackend()->selectFolder($_globalName);
        }
        
        $this->_getBackend()->addFlags($_id, $_flags);
    }
    
    public function clearFlags($_serverId, $_globalName, $_id, $_flags)
    {
        if($this->_getBackend()->getCurrentFolder() != $_globalName) {
            $this->_getBackend()->selectFolder($_globalName);
        }
        
        $this->_getBackend()->clearFlags($_id, $_flags);
    }
}
