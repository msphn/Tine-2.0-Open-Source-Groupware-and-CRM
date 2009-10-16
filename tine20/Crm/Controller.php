<?php
/**
 * MAIN controller for CRM application
 * 
 * the main logic of the CRM application
 *
 * @package     Crm
 * @subpackage  Controller
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Philipp Schuele <p.schuele@metaways.de>
 * @copyright   Copyright (c) 2007-2008 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     $Id$
 *
 */

/**
 * leads controller class for CRM application
 * 
 * @package     Crm
 * @subpackage  Controller
 */
class Crm_Controller extends Tinebase_Controller_Abstract implements Tinebase_Event_Interface, Tinebase_Container_Interface
{
    /**
     * default settings
     * 
     * @var array
     */
    protected $_defaultsSettings = array(
        'leadstate_id'  => 1,
        'leadtype_id'   => 1,
        'leadsource_id' => 1,
    );
    
    /**
     * the constructor
     *
     * don't use the constructor. use the singleton 
     */
    private function __construct() 
    {
        $this->_currentAccount = Tinebase_Core::getUser();        
        $this->_applicationName = 'Crm';
    }

    /**
     * don't clone. Use the singleton.
     *
     */
    private function __clone() 
    {        
    }

    /**
     * holds the instance of the singleton
     *
     * @var Crm_Controller
     */
    private static $_instance = NULL;
    
    /**
     * the singleton pattern
     *
     * @return Crm_Controller
     */
    public static function getInstance() 
    {
        if (self::$_instance === NULL) {
            self::$_instance = new Crm_Controller;
        }
        
        return self::$_instance;
    }    
        
    /********************* event handler and personal folder ***************************/
    
    /**
     * event handler function
     * 
     * all events get routed through this function
     *
     * @param Tinebase_Event_Abstract $_eventObject the eventObject
     */
    public function handleEvents(Tinebase_Event_Abstract $_eventObject)
    {
        Tinebase_Core::getLogger()->debug(__METHOD__ . ' (' . __LINE__ . ') handle event of type ' . get_class($_eventObject));
        
        switch(get_class($_eventObject)) {
            case 'Admin_Event_AddAccount':
                $this->createPersonalFolder($_eventObject->account);
                break;
            case 'Admin_Event_DeleteAccount':
                $this->deletePersonalFolder($_eventObject->account);
                break;
        }
    }
    
    /**
     * creates the initial folder for new accounts
     *
     * @param mixed[int|Tinebase_Model_User] $_account   the accountd object
     * @return Tinebase_Record_RecordSet                            of subtype Tinebase_Model_Container
     */
    public function createPersonalFolder($_accountId)
    {
        $translation = Tinebase_Translation::getTranslation('Crm');
        
        $accountId = Tinebase_Model_User::convertUserIdToInt($_accountId);
        $account = Tinebase_User::getInstance()->getUserById($accountId);
        $newContainer = new Tinebase_Model_Container(array(
            'name'              => sprintf($translation->_("%s's personal leads"), $account->accountFullName),
            'type'              => Tinebase_Model_Container::TYPE_PERSONAL,
            'backend'           => 'Sql',
            'application_id'    => Tinebase_Application::getInstance()->getApplicationByName('Crm')->getId() 
        ));
        
        $personalContainer = Tinebase_Container::getInstance()->addContainer($newContainer, NULL, FALSE, $accountId);
        $personalContainer->account_grants = Tinebase_Model_Container::GRANT_ANY;
        
        $container = new Tinebase_Record_RecordSet('Tinebase_Model_Container', array($personalContainer));
        
        return $container;
    }

    /**
     * delets the personal folder for deleted accounts
     *
     * @param mixed[int|Tinebase_Model_User] $_account   the accountd object
     * @return void
     * 
     * @todo    implement
     */
    public function deletePersonalFolder($_accountId)
    {
        $accountId = Tinebase_Model_User::convertUserIdToInt($_accountId);
        
        // delete personal folder here
    }
    
    /**
     * Returns settings for crm app
     * - result is cached
     *
     * @return  Crm_Model_Config
     * 
     * @todo check 'endslead' values
     * @todo generalize this
     */
    public function getSettings()
    {
        $cache = Tinebase_Core::get('cache');
        $cacheId = convertCacheId('getCrmSettings');
        $result = $cache->load($cacheId);
        
        if (! $result) {
        
            $translate = Tinebase_Translation::getTranslation('Crm');
            
            $result = new Crm_Model_Config(array(
                'defaults' => parent::getSettings()
            ));
            
            $others = array(
                Crm_Model_Config::LEADTYPES => array(
                    array('id' => 1, 'leadtype' => $translate->_('Customer')),
                    array('id' => 2, 'leadtype' => $translate->_('Partner')),
                    array('id' => 3, 'leadtype' => $translate->_('Reseller')),
                ), 
                Crm_Model_Config::LEADSTATES => array(
                    array('id' => 1, 'leadstate' => $translate->_('open'),                  'probability' => 0,     'endslead' => 0),
                    array('id' => 2, 'leadstate' => $translate->_('contacted'),             'probability' => 10,    'endslead' => 0),
                    array('id' => 3, 'leadstate' => $translate->_('waiting for feedback'),  'probability' => 30,    'endslead' => 0),
                    array('id' => 4, 'leadstate' => $translate->_('quote sent'),            'probability' => 50,    'endslead' => 0),
                    array('id' => 5, 'leadstate' => $translate->_('accepted'),              'probability' => 100,   'endslead' => 1),
                    array('id' => 6, 'leadstate' => $translate->_('lost'),                  'probability' => 0,     'endslead' => 1),
                ), 
                Crm_Model_Config::LEADSOURCES => array(
                    array('id' => 1, 'leadsource' => $translate->_('Market')),
                    array('id' => 2, 'leadsource' => $translate->_('Email')),
                    array('id' => 3, 'leadsource' => $translate->_('Telephone')),
                    array('id' => 4, 'leadsource' => $translate->_('Website')),
                )
            );
            foreach ($others as $setting => $defaults) {
                $result->$setting = Tinebase_Config::getInstance()->getConfigAsArray($setting, $this->_applicationName, $defaults);
            }
            
            // save result and tag it with 'settings'
            $cache->save($result, $cacheId, array('settings'));
        }
        
        return $result;
    }
    
    /**
     * save crm settings
     * 
     * @param Crm_Model_Config $_settings
     * @return Crm_Model_Config
     * 
     * @todo generalize this
     */
    public function saveSettings($_settings)
    {
        Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . ' Updating Crm Settings: ' . print_r($_settings->toArray(), TRUE));
        
        foreach ($_settings->toArray() as $field => $value) {
            if ($field == 'id') {
                continue;
            } else if ($field == 'defaults') {
                parent::saveSettings($value);
            } else {
                Tinebase_Config::getInstance()->setConfigForApplication($field, Zend_Json::encode($value), $this->_applicationName);
            }
        }
        
        // invalidate cache
        Tinebase_Core::get('cache')->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('settings'));
        
        return $this->getSettings();
    }
}
