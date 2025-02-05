<?php
/**
 * Abstract record controller for Tine 2.0 applications
 *
 * @package     Tinebase
 * @subpackage  Controller
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Philipp Schüle <p.schuele@metaways.de>
 * @copyright   Copyright (c) 2007-2013 Metaways Infosystems GmbH (http://www.metaways.de)
 *
 * @todo        this should be splitted into smaller parts!
 */

/**
 * abstract record controller class for Tine 2.0 applications
 *
 * @package     Tinebase
 * @subpackage  Controller
 */
abstract class Tinebase_Controller_Record_Abstract
    extends Tinebase_Controller_Abstract
    implements Tinebase_Controller_Record_Interface, Tinebase_Controller_SearchInterface
{
   /**
     * application backend class
     *
     * @var Tinebase_Backend_Sql_Interface
     */
    protected $_backend;

    /**
     * Model name
     *
     * @var string
     *
     * @todo perhaps we can remove that and build model name from name of the class (replace 'Controller' with 'Model')
     */
    protected $_modelName;

    /**
     * check for container ACLs
     *
     * @var boolean
     *
     * @todo rename to containerACLChecks
     */
    protected $_doContainerACLChecks = TRUE;

    /**
     * do right checks - can be enabled/disabled by doRightChecks
     *
     * @var boolean
     */
    protected $_doRightChecks = TRUE;

    /**
     * use notes - can be enabled/disabled by useNotes
     *
     * @var boolean
     */
    protected $_setNotes = TRUE;

    /**
     * delete or just set is_delete=1 if record is going to be deleted
     * - legacy code -> remove that when all backends/applications are using the history logging
     *
     * @var boolean
     */
    protected $_purgeRecords = TRUE;

    /**
     * omit mod log for this records
     *
     * @var boolean
     */
    protected $_omitModLog = FALSE;

    /**
     * resolve customfields in search()
     *
     * @var boolean
     */
    protected $_resolveCustomFields = FALSE;

    /**
     * clear customfields cache on create / update
     * 
     * @var boolean
     */
    protected $_clearCustomFieldsCache = FALSE;
    
    /**
     * Do we update relation to this record
     * 
     * @var boolean
     */
    protected $_doRelationUpdate = TRUE;
    
    /**
     * Do we force sent modlog for this record
     * 
     * @var boolean
     */
    protected $_doForceModlogInfo = FALSE;

    /**
     * send notifications?
     *
     * @var boolean
     */
    protected $_sendNotifications = false;

    /**
     * if some of the relations should be deleted
     *
     * @var array
     */
    protected $_relatedObjectsToDelete = array();

    /**
     * set this to true to create/update related records
     * 
     * @var boolean
     */
    protected $_inspectRelatedRecords  = false;

    /**
     * set this to true to check (duplicate/freebusy/...) in create/update of related records
     *
     * @var boolean
     */
    protected $_doRelatedCreateUpdateCheck  = false;

    /**
     * record alarm field
     *
     * @var string
     */
    protected $_recordAlarmField = 'dtstart';

    /**
     * duplicate check fields / if this is NULL -> no duplicate check
     *
     * @var array
     */
    protected $_duplicateCheckFields = NULL;

    protected $_duplicateCheckConfig = array();
    
    /**
     * holds new relation on update multiple
     * @var array
     */
    protected $_newRelations = NULL;
    
    /**
     * holds relations to remove on update multiple
     * @var array
     */
    protected $_removeRelations = NULL;
    
    /**
     * result of updateMultiple function
     * 
     * @var array
     */
    protected $_updateMultipleResult = array();

    /**
     * should each record be validated in updateMultiple 
     * - FALSE: only the first record is validated with the incoming data
     *
     * @var boolean
     */
    protected $_updateMultipleValidateEachRecord = FALSE;

    /**
     * support record paths
     *
     * @var bool
     */
    protected $_useRecordPaths = false;

    /**
     * returns controller for records of given model
     *
     * @param string $_model
     * @return Tinebase_Controller|Tinebase_Controller_Abstract|Tinebase_Controller_Record_Abstract
     */
    public static function getController($_model)
    {
        list($appName, /*$i*/, $modelName) = explode('_', $_model);
        return Tinebase_Core::getApplicationInstance($appName, $modelName);
    }
    
    /**
     * returns backend for this controller
     * @return Tinebase_Backend_Sql_Interface
     */
    public function getBackend()
    {
        return $this->_backend;
    }

    /*********** get / search / count **************/

    /**
     * get list of records
     *
     * @param Tinebase_Model_Filter_FilterGroup $_filter
     * @param Tinebase_Model_Pagination $_pagination
     * @param boolean|array $_getRelations
     * @param boolean $_onlyIds
     * @param string $_action for right/acl check
     * @return Tinebase_Record_RecordSet|array
     */
    public function search(Tinebase_Model_Filter_FilterGroup $_filter = NULL, Tinebase_Model_Pagination $_pagination = NULL, $_getRelations = FALSE, $_onlyIds = FALSE, $_action = 'get')
    {
        $this->_checkRight($_action);
        $this->checkFilterACL($_filter, $_action);
        $this->_addDefaultFilter($_filter);
        
        $result = $this->_backend->search($_filter, $_pagination, $_onlyIds);
        
        if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__
            . ' Got ' . count($result) . ' search results');
        
        if (! $_onlyIds) {
            if ($_getRelations && count($result) > 0 && $result->getFirstRecord()->has('relations')) {
                // if getRelations is true, all relations should be fetched
                if ($_getRelations === true) {
                    $_getRelations = NULL;
                }
                /** @noinspection PhpUndefinedMethodInspection */
                $result->setByIndices('relations', Tinebase_Relations::getInstance()->getMultipleRelations($this->_modelName, $this->_getBackendType(), $result->getId(), NULL, array(), FALSE, $_getRelations));
            }
            if ($this->resolveCustomfields()) {
                Tinebase_CustomField::getInstance()->resolveMultipleCustomfields($result);
            }
        }
        
        return $result;
    }
    
    /**
     * you can define default filters here
     * 
     * @param Tinebase_Model_Filter_FilterGroup $_filter
     */
    protected function _addDefaultFilter(Tinebase_Model_Filter_FilterGroup $_filter = NULL)
    {
        
    }

    /**
     * Gets total count of search with $_filter
     *
     * @param Tinebase_Model_Filter_FilterGroup $_filter
     * @param string $_action for right/acl check
     * @return int|array
     */
    public function searchCount(Tinebase_Model_Filter_FilterGroup $_filter, $_action = 'get')
    {
        $this->checkFilterACL($_filter, $_action);
        
        $count = $this->_backend->searchCount($_filter);
        
        if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ 
            . ' Got ' . (is_array($count) ? print_r($count, 1) : $count) . ' search count');
        
        return $count;
    }

    /**
     * set/get the sendNotifications state
     *
     * @param  boolean $setTo
     * @return boolean
     */
    public function sendNotifications($setTo = NULL)
    {
        return $this->_setBooleanMemberVar('_sendNotifications', $setTo);
    }
    
    /**
     * set/get a boolean member var
     * 
     * @param string $name
     * @param boolean $value
     * @return boolean
     */
    protected function _setBooleanMemberVar($name, $value = NULL)
    {
        $currValue = $this->{$name};
        if ($value !== NULL) {
            if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__
                . ' Resetting ' . $name . ' to ' . (int) $value);
            $this->{$name} = (bool)$value;
        }
        
        return $currValue;
    }

    /**
     * setter for $relatedObjectsToDelete
     *
     * @param array $relatedObjectNames
     */
    public function setRelatedObjectsToDelete(array $relatedObjectNames)
    {
        $this->_relatedObjectsToDelete = $relatedObjectNames;
    }

    /**
     * set/get purging of record when deleting
     *
     * @param  boolean $setTo
     * @return boolean
     */
    public function purgeRecords($setTo = NULL)
    {
        return $this->_setBooleanMemberVar('_purgeRecords', $setTo);
    }

    /**
     * set/get checking ACL rights
     *
     * @param  boolean $setTo
     * @return boolean
     */
    public function doContainerACLChecks($setTo = NULL)
    {
        return $this->_setBooleanMemberVar('_doContainerACLChecks', $setTo);
    }
    
    /**
     * set/get resolving of customfields
     *
     * @param  boolean $setTo
     * @return boolean
     */
    public function resolveCustomfields($setTo = NULL)
    {
        $currentValue = ($this->_setBooleanMemberVar('_resolveCustomFields', $setTo)
            && Tinebase_CustomField::getInstance()->appHasCustomFields($this->_applicationName, $this->_modelName));
        return $currentValue;
    }

    /**
     * set/get modlog active
     *
     * @param  boolean $setTo
     * @return bool
     * @throws Tinebase_Exception_NotFound
     */
    public function modlogActive($setTo = NULL)
    {
        if (! $this->_backend) {
            throw new Tinebase_Exception_NotFound('Backend not defined');
        }

        $currValue = $this->_backend->getModlogActive();
        if (NULL !== $setTo) {
            $setTo = (bool)$setTo;
            if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__ . ' Resetting modlog active to ' . (int) $setTo);
            $this->_backend->setModlogActive($setTo);
            $this->_omitModLog = ! $setTo;
        }

        return $currValue;
    }

    /**
     * set/get relation update
     *
     * @param  boolean $setTo
     * @return boolean
     */
    public function doRelationUpdate($setTo = NULL)
    {
        return $this->_setBooleanMemberVar('_doRelationUpdate', $setTo);
    }
    
    /**
     * set/get force modlog info
     *
     * @param  boolean $setTo
     * @return boolean
     */
    public function doForceModlogInfo($setTo = NULL)
    {
        return $this->_setBooleanMemberVar('_doForceModlogInfo', $setTo);
    }
    
    /**
     * set/get _inspectRelatedRecords
     *
     * @param boolean $setTo
     * @return boolean
     */
    public function doInspectRelatedRecords($setTo = NULL)
    {
        return $this->_setBooleanMemberVar('_inspectRelatedRecords', $setTo);
    }
    
    /**
     * set/get duplicateCheckFields
     * 
     * @param array $setTo
     * @return array
     */
    public function duplicateCheckFields($setTo = NULL)
    {
        if (NULL !== $setTo) {
            $this->_duplicateCheckFields = $setTo;
        }
        
        return $this->_duplicateCheckFields;
    }
    
    /**
     * disable this to do not check any rights
     *
     * @param  boolean $setTo
     * @return boolean
     */
     public function doRightChecks($setTo = NULL)
     {
         return $this->_setBooleanMemberVar('_doRightChecks', $setTo);
     }

    /**
     * get by id
     *
     * @param string $_id
     * @param int $_containerId
     * @param bool         $_getRelatedData
     * @param bool $_getDeleted
     * @return Tinebase_Record_Interface
     * @throws Tinebase_Exception_AccessDenied
     */
    public function get($_id, $_containerId = NULL, $_getRelatedData = TRUE, $_getDeleted = FALSE)
    {
        $this->_checkRight('get');
        
        if (! $_id) { // yes, we mean 0, null, false, ''
            $record = new $this->_modelName(array(), true);
            
            if ($this->_doContainerACLChecks) {
                if ($_containerId === NULL) {
                    $containers = Tinebase_Container::getInstance()->getPersonalContainer(Tinebase_Core::getUser(), $this->_applicationName, Tinebase_Core::getUser(), Tinebase_Model_Grants::GRANT_ADD);
                    $record->container_id = $containers[0]->getId();
                } else {
                    $record->container_id = $_containerId;
                }
            }
            
        } else {
            $record = $this->_backend->get($_id, $_getDeleted);
            $this->_checkGrant($record, 'get');
            
            // get related data only on request (defaults to TRUE)
            if ($_getRelatedData) {
                $this->_getRelatedData($record);
                
                if ($record->has('notes')) {
                    $record->notes = Tinebase_Notes::getInstance()->getNotesOfRecord($this->_modelName, $record->getId());
                }
            }
        }
        
        return $record;
    }
    
    /**
     * check if record with given $id exists
     * 
     * @param string $id
     * @return boolean
     */
    public function exists($id)
    {
        $this->_checkRight('get');
        
        try {
            $record = $this->_backend->get($id);
            $result = $this->_checkGrant($record, 'get', FALSE);
        } catch (Tinebase_Exception_NotFound $tenf) {
            $result = FALSE;
        }
        
        return $result;
    }
    
    /**
     * add related data to record
     * 
     * @param Tinebase_Record_Interface $record
     */
    protected function _getRelatedData($record)
    {
        if ($record->has('tags')) {
            Tinebase_Tags::getInstance()->getTagsOfRecord($record);
        }
        if ($record->has('relations')) {
            $record->relations = Tinebase_Relations::getInstance()->getRelations(
                $this->_modelName,
                $this->_getBackendType(),
                $record->getId());
        }
        if ($record->has('alarms')) {
            $this->getAlarms($record);
        }
        if ($this->resolveCustomfields()) {
            $cfConfigs = Tinebase_CustomField::getInstance()->getCustomFieldsForApplication(
                Tinebase_Application::getInstance()->getApplicationByName($this->_applicationName));
            Tinebase_CustomField::getInstance()->resolveRecordCustomFields($record, null, $cfConfigs);
        }
        if ($record->has('attachments') && Tinebase_Core::isFilesystemAvailable()) {
            Tinebase_FileSystem_RecordAttachments::getInstance()->getRecordAttachments($record);
        }
    }

    /**
     * Returns a set of records identified by their id's
     *
     * @param   array $_ids       array of record identifiers
     * @param   bool  $_ignoreACL don't check acl grants
     * @return  Tinebase_Record_RecordSet of $this->_modelName
     */
    public function getMultiple($_ids, $_ignoreACL = FALSE)
    {
        $this->_checkRight('get');

        // get all allowed containers and add them to getMultiple query
        $containerIds = ($this->_doContainerACLChecks && $_ignoreACL !== TRUE)
           ? Tinebase_Container::getInstance()->getContainerByACL(
               Tinebase_Core::getUser(),
               $this->_applicationName,
               Tinebase_Model_Grants::GRANT_READ,
               TRUE)
           : NULL;
        $records = $this->_backend->getMultiple($_ids, $containerIds);

        if ($this->resolveCustomfields()) {
            Tinebase_CustomField::getInstance()->resolveMultipleCustomfields($records);
        }

        return $records;
    }

    /**
     * Gets all entries
     *
     * @param string $_orderBy Order result by
     * @param string $_orderDirection Order direction - allowed are ASC and DESC
     * @throws Tinebase_Exception_InvalidArgument
     * @return Tinebase_Record_RecordSet
     */
    public function getAll($_orderBy = 'id', $_orderDirection = 'ASC')
    {
        $this->_checkRight('get');

        $records = $this->_backend->getAll($_orderBy, $_orderDirection);

        if ($this->resolveCustomfields()) {
            Tinebase_CustomField::getInstance()->resolveMultipleCustomfields($records);
        }

        return $records;
    }

    /*************** add / update / delete / move *****************/

    /**
     * add one record
     *
     * @param   Tinebase_Record_Interface $_record
     * @param   boolean $_duplicateCheck
     * @return  Tinebase_Record_Interface
     * @throws  Tinebase_Exception_AccessDenied
     */
    public function create(Tinebase_Record_Interface $_record, $_duplicateCheck = true)
    {
        $this->_checkRight('create');

        if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . ' '
            . print_r($_record->toArray(),true));
        if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__
            . ' Create new ' . $this->_modelName);

        $db = (method_exists($this->_backend, 'getAdapter')) ? $this->_backend->getAdapter() : Tinebase_Core::getDb();
        
        try {
            $transactionId = Tinebase_TransactionManager::getInstance()->startTransaction($db);

            // add personal container id if container id is missing in record
            if ($_record->has('container_id') && empty($_record->container_id)) {
                $containers = Tinebase_Container::getInstance()->getPersonalContainer(Tinebase_Core::getUser(), $this->_applicationName, Tinebase_Core::getUser(), Tinebase_Model_Grants::GRANT_ADD);
                $_record->container_id = $containers[0]->getId();
            }

            $_record->isValid(TRUE);

            $this->_checkGrant($_record, 'create');

            // added _doForceModlogInfo behavior
            if ($_record->has('created_by')) {
                $origRecord = clone ($_record);
                Tinebase_Timemachine_ModificationLog::setRecordMetaData($_record, 'create');
                $this->_forceModlogInfo($_record, $origRecord, 'create');
            }

            $this->_inspectBeforeCreate($_record);
            if ($_duplicateCheck) {
                $this->_duplicateCheck($_record);
            }

            $this->_setAutoincrementValues($_record);

            $createdRecord = $this->_backend->create($_record);
            $this->_inspectAfterCreate($createdRecord, $_record);
            $this->_setRelatedData($createdRecord, $_record, null, TRUE);
            $this->_setNotes($createdRecord, $_record);
            
            if ($this->sendNotifications()) {
                $this->doSendNotifications($createdRecord, Tinebase_Core::getUser(), 'created');
            }
            
            $this->_increaseContainerContentSequence($createdRecord, Tinebase_Model_ContainerContent::ACTION_CREATE);
            
            Tinebase_TransactionManager::getInstance()->commitTransaction($transactionId);
        } catch (Exception $e) {
            $this->_handleRecordCreateOrUpdateException($e);
        }
        
        if ($this->_clearCustomFieldsCache) {
            Tinebase_Core::getCache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('customfields'));
        }

        /** @noinspection PhpUndefinedVariableInspection */
        return $this->get($createdRecord);
    }

    protected function _setAutoincrementValues(Tinebase_Record_Interface $_record, Tinebase_Record_Interface $_oldRecord = null)
    {
        $className = get_class($_record);
        $configuration = $_record->getConfiguration();
        if (null === $configuration) {
            return;
        }

        if (! method_exists($configuration, 'getAutoincrementFields')) {
            if (Tinebase_Core::isLogLevel(Zend_Log::NOTICE)) Tinebase_Core::getLogger()->notice(__METHOD__ . '::' . __LINE__
                . ' CLass has no getAutoincrementFields(): ' . get_class($configuration));
            return;
        }

        foreach ($configuration->getAutoincrementFields() as $fieldDef) {
            $createNewValue = false;
            $checkValue = false;
            $freeOldValue = false;
            $numberable = null;

            // if new record field is not set and if there is no old record, we assign a new value
            if (!isset($_record->{$fieldDef['fieldName']})) {
                if (null === $_oldRecord) {
                    $createNewValue = true;
                }

            } else {

                // if new record field is set to empty string, we assign a new value
                if (empty($_record->{$fieldDef['fieldName']})) {
                    $createNewValue = true;

                // if new record field is populated and it differs from the old record value, we need to check the value
                } elseif (null !== $_oldRecord) {
                    if ($_record->{$fieldDef['fieldName']} != $_oldRecord->{$fieldDef['fieldName']}) {
                        $checkValue = true;
                    }

                // if new record field is populated and there is no old record, we need to check the value
                } else {
                    $checkValue = true;
                }
            }

            if (true === $checkValue || true === $createNewValue) {
                $numberable = Tinebase_Numberable::getNumberable($className, $fieldDef['fieldName'], $fieldDef);
            }

            if (true === $checkValue) {
                if (false === $numberable->insert($_record->{$fieldDef['fieldName']})) {
                    // if the check failed and we have an old value, we keep on using the old value
                    if (null !== $_oldRecord && !empty($_oldRecord->{$fieldDef['fieldName']})) {
                        $_record->{$fieldDef['fieldName']} = $_oldRecord->{$fieldDef['fieldName']};
                    // else we create a new one
                    } else {
                        $createNewValue = true;
                    }
                } else {
                    $freeOldValue = true;
                }
            }

            if (true === $createNewValue) {
                $_record->{$fieldDef['fieldName']} = $numberable->getNext();
                if (null !== $_oldRecord && !empty($_oldRecord->{$fieldDef['fieldName']})) {
                    $freeOldValue = true;
                }
            }

            if (true === $freeOldValue) {
                $numberable->free($_oldRecord->{$fieldDef['fieldName']});
            }
        }
    }

    /**
     * handle record exception
     * 
     * @param Exception $e
     * @throws Exception
     * 
     * @todo invent hooking mechanism for database/backend independent exception handling (like lock timeouts)
     */
    protected function _handleRecordCreateOrUpdateException(Exception $e)
    {
        Tinebase_TransactionManager::getInstance()->rollBack();

        Tinebase_Exception::log($e);

        if ($e instanceof Zend_Db_Statement_Exception && preg_match('/Lock wait timeout exceeded/', $e->getMessage())) {
            throw new Tinebase_Exception_Backend_Database_LockTimeout($e->getMessage());
        }
        
        throw $e;
    }
    
    /**
     * inspect creation of one record (before create)
     *
     * @param   Tinebase_Record_Interface $_record
     * @return  void
     */
    protected function _inspectBeforeCreate(Tinebase_Record_Interface $_record)
    {

    }

    /**
     * do duplicate check (before create)
     *
     * @param   Tinebase_Record_Interface $_record
     * @return  void
     * @throws Tinebase_Exception_Duplicate
     */
    protected function _duplicateCheck(Tinebase_Record_Interface $_record)
    {
        $duplicateFilter = $this->_getDuplicateFilter($_record);

        if ($duplicateFilter === NULL) {
            return;
        }

        if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ .
            ' Doing duplicate check.');

        $duplicates = $this->search($duplicateFilter, new Tinebase_Model_Pagination(array('limit' => 5)), /* $_getRelations = */ true);

        if (count($duplicates) > 0) {
            if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ .
                ' Found ' . count($duplicates) . ' duplicate(s).');

            $ted = new Tinebase_Exception_Duplicate('Duplicate record(s) found');
            $ted->setModelName($this->_modelName);
            $ted->setData($duplicates);
            $ted->setClientRecord($_record);
            throw $ted;
        } else {
            if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ .
                ' No duplicates found.');
        }
    }

    /**
     * get duplicate filter
     *
     * @param Tinebase_Record_Interface $_record
     * @return Tinebase_Model_Filter_FilterGroup|NULL
     */
    protected function _getDuplicateFilter(Tinebase_Record_Interface $_record)
    {
        if (!is_array($this->_duplicateCheckFields) || count($this->_duplicateCheckFields) === 0) {
            return NULL;
        }
        
        if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__
            . ' Duplicate check fields: ' . print_r($this->_duplicateCheckFields, TRUE));
        
        $filters = array();
        foreach ($this->_duplicateCheckFields as $group) {
            $addFilter = array();
            if (! is_array($group)) {
                $group = array($group);
            }
            foreach ($group as $field) {
                if (! empty($_record->{$field})) {
                    if ($field === 'relations') {
                        $relationFilter = $this->_getRelationDuplicateFilter($_record);
                        if ($relationFilter) {
                            $addFilter[] = $relationFilter;
                        }
                    } else {
                        $addFilter[] = array('field' => $field, 'operator' => 'equals', 'value' => $_record->{$field});
                    }
                    
                } else if (isset($_record->customfields[$field])) {
                    $customFieldConfig = Tinebase_CustomField::getInstance()->getCustomFieldByNameAndApplication($this->_applicationName, $field, $this->_modelName);
                    if ($customFieldConfig) {
                        $addFilter[] = array('field' => 'customfield', 'operator' => 'equals', 'value' => array(
                            'value' => $_record->customfields[$field],
                            'cfId'  => $customFieldConfig->getId()
                        ));
                    }
                }
            }
            if (! empty($addFilter)) {
                $filters[] = array('condition' => 'AND', 'filters' => $addFilter);
            }
        }

        if (empty($filters)) {
            return NULL;
        }

        $filterClass = $this->_modelName . 'Filter';
        $filterData = (count($filters) > 1) ? array(array('condition' => 'OR', 'filters' => $filters)) : $filters;

        // exclude own record if it has an id
        $recordId = $_record->getId();
        if (! empty($recordId)) {
            $filterData[] = array('field' => 'id', 'operator' => 'notin', 'value' => array($recordId));
        }

        /** @var Tinebase_Model_Filter_FilterGroup $filter */
        $filter = new $filterClass($filterData);
        
        if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__ . ' '
            . print_r($filter->toArray(), TRUE));
        
        return $filter;
    }
    
    protected function _getRelationDuplicateFilter($record)
    {
        $filter = null;
        /** @var Tinebase_Record_RecordSet $relations */
        $relations = $record->relations;
        
        if (count($relations) === 0 || ! isset($this->_duplicateCheckConfig['relations']['filterField'])) {
            return $filter;
        }
        
        if (! $relations instanceof Tinebase_Record_RecordSet) {
            $relations = new Tinebase_Record_RecordSet('Tinebase_Model_Relation', $relations, /* $_bypassFilters = */ true);
        }
        
        // check for relation and add relation filter
        $type = isset($this->_duplicateCheckConfig['relations']['type']) ? $this->_duplicateCheckConfig['relations']['type'] : '';
        $relations = $relations->filter('type', $type);
        if (count($relations) > 0) {
            /** @var Tinebase_Model_Relation $duplicateRelation */
            $duplicateRelation = $relations->getFirstRecord();
            if ($duplicateRelation->related_id) {
                $filter = array(
                    'field' => $this->_duplicateCheckConfig['relations']['filterField'],
                    'operator' => 'AND',
                    'value' => array(array('field' => ':id', 'operator' => 'equals', 'value' => $duplicateRelation->related_id))
                );
            }
        }
        
        return $filter;
    }

    /**
     * inspect creation of one record (after create)
     *
     * @param   Tinebase_Record_Interface $_createdRecord
     * @param   Tinebase_Record_Interface $_record
     * @return  void
     */
    protected function _inspectAfterCreate($_createdRecord, Tinebase_Record_Interface $_record)
    {
    }

    /**
     * increase container content sequence
     * 
     * @param Tinebase_Record_Interface $record
     * @param string $action
     */
    protected function _increaseContainerContentSequence(Tinebase_Record_Interface $record, $action = NULL)
    {
        if ($record->has('container_id')) {
            Tinebase_Container::getInstance()->increaseContentSequence($record->container_id, $action, $record->getId());
        }
    }
    
    /**
     * Force modlog info if set
     *  
     * @param Tinebase_Record_Interface $_record
     * @param Tinebase_Record_Interface $_origRecord
     * @param string $_action
     * @return  void
     */
    protected function _forceModlogInfo(Tinebase_Record_Interface $_record, Tinebase_Record_Interface $_origRecord, $_action = NULL)
    {
        if ($this->_doForceModlogInfo && ! empty($_origRecord)) {
            // on create
            if ($_action == 'create') {
                if (! empty($_origRecord->created_by)) {
                    if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . ' Force modlog - created_by: ' . $_origRecord->created_by);
                    $_record->created_by = $_origRecord->created_by;
                }
                if (! empty($_origRecord->creation_time)) {
                    if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . ' Force modlog - creation_time: ' . $_origRecord->creation_time);
                    $_record->creation_time = $_origRecord->creation_time;
                }
                if (! empty($_origRecord->last_modified_by)) {
                    if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . ' Force modlog - last_modified_by: ' . $_origRecord->last_modified_by);
                    $_record->last_modified_by = $_origRecord->last_modified_by;
                }
                if (! empty($_origRecord->last_modified_time)) {
                    if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . ' Force modlog - last_modified_time: ' . $_origRecord->last_modified_time);
                    $_record->last_modified_time = $_origRecord->last_modified_time;
                }
            }
            
            // on update
            if ($_action == 'update') {
                if (! empty($_origRecord->last_modified_by)) {
                    if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . ' Force modlog - last_modified_by: ' . $_origRecord->last_modified_by);
                    $_record->last_modified_by = $_origRecord->last_modified_by;
                }
                if (! empty($_origRecord->last_modified_time)) {
                    if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . ' Force modlog - last_modified_time: ' . $_origRecord->last_modified_time);
                    $_record->last_modified_time = $_origRecord->last_modified_time;
                }
            }
        }   
    }
    
    /**
     * update one record
     *
     * @param   Tinebase_Record_Interface $_record
     * @param   boolean $_duplicateCheck
     * @return  Tinebase_Record_Interface
     * @throws  Tinebase_Exception_AccessDenied
     * 
     * @todo    fix duplicate check on update / merge needs to remove the changed record / ux discussion
     */
    public function update(Tinebase_Record_Interface $_record, $_duplicateCheck = TRUE)
    {
        if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__ . ' '
            . ' Record to update: ' . print_r($_record->toArray(), TRUE));
        if (Tinebase_Core::isLogLevel(Zend_Log::INFO)) Tinebase_Core::getLogger()->info(__METHOD__ . '::' . __LINE__
            . ' Update ' . $this->_modelName);

        $db = (method_exists($this->_backend, 'getAdapter')) ? $this->_backend->getAdapter() : Tinebase_Core::getDb();

        try {
            $transactionId = Tinebase_TransactionManager::getInstance()->startTransaction($db);

            $_record->isValid(TRUE);
            $currentRecord = $this->get($_record->getId());
            
            if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__
                . ' Current record: ' . print_r($currentRecord->toArray(), TRUE));
            
            // add _doForceModlogInfo behavior
            $origRecord = clone ($_record);
            $this->_updateACLCheck($_record, $currentRecord);
            $this->_concurrencyManagement($_record, $currentRecord);
            $this->_forceModlogInfo($_record, $origRecord, 'update');
            $this->_inspectBeforeUpdate($_record, $currentRecord);
            
            // NOTE removed the duplicate check because we can not remove the changed record yet
//             if ($_duplicateCheck) {
//                 $this->_duplicateCheck($_record);
//             }

            $this->_setAutoincrementValues($_record, $currentRecord);
            
            $updatedRecord = $this->_backend->update($_record);
            if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__
                . ' Updated record: ' . print_r($updatedRecord->toArray(), TRUE));
            
            $this->_inspectAfterUpdate($updatedRecord, $_record, $currentRecord);
            $updatedRecordWithRelatedData = $this->_setRelatedData($updatedRecord, $_record, $currentRecord, TRUE);
            if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__
                . ' Updated record with related data: ' . print_r($updatedRecordWithRelatedData->toArray(), TRUE));
            
            $currentMods = $this->_writeModLog($updatedRecordWithRelatedData, $currentRecord);
            $this->_setNotes($updatedRecordWithRelatedData, $_record, Tinebase_Model_Note::SYSTEM_NOTE_NAME_CHANGED, $currentMods);
            
            if ($this->_sendNotifications && count($currentMods) > 0) {
                $this->doSendNotifications($updatedRecordWithRelatedData, Tinebase_Core::getUser(), 'changed', $currentRecord);
            }
            
            if ($_record->has('container_id') && $currentRecord->container_id !== $updatedRecord->container_id) {
                $this->_increaseContainerContentSequence($currentRecord, Tinebase_Model_ContainerContent::ACTION_DELETE);
                $this->_increaseContainerContentSequence($updatedRecord, Tinebase_Model_ContainerContent::ACTION_CREATE);
            } else {
                $this->_increaseContainerContentSequence($updatedRecord, Tinebase_Model_ContainerContent::ACTION_UPDATE);
            }

            Tinebase_TransactionManager::getInstance()->commitTransaction($transactionId);

        } catch (Exception $e) {
            $this->_handleRecordCreateOrUpdateException($e);
        }

        if ($this->_clearCustomFieldsCache) {
            Tinebase_Core::getCache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('customfields'));
        }

        /** @noinspection PhpUndefinedVariableInspection */
        return $this->get($updatedRecord->getId());
    }
    
    /**
     * do ACL check for update record
     * 
     * @param Tinebase_Record_Interface $_record
     * @param Tinebase_Record_Interface $_currentRecord
     */
    protected function _updateACLCheck($_record, $_currentRecord)
    {
        if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__
            . ' Doing ACL check ...');
        
        if ($_currentRecord->has('container_id') && $_currentRecord->container_id != $_record->container_id) {
            $this->_checkGrant($_record, 'create');
            $this->_checkRight('create');
            // NOTE: It's not yet clear if we have to demand delete grants here or also edit grants would be fine
            $this->_checkGrant($_currentRecord, 'delete');
            $this->_checkRight('delete');
        } else {
            $this->_checkGrant($_record, 'update', TRUE, 'No permission to update record.', $_currentRecord);
            $this->_checkRight('update');
        }
    }

    /**
     * concurrency management & history log
     *
     * @param Tinebase_Record_Interface $_record
     * @param Tinebase_Record_Interface $_currentRecord
     */
    protected function _concurrencyManagement($_record, $_currentRecord)
    {
        if (! $_record->has('created_by')) {
            return;
        }
        
        if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__
            . ' Doing concurrency check ...');

        $modLog = Tinebase_Timemachine_ModificationLog::getInstance();
        $modLog->manageConcurrentUpdates(
            Tinebase_Application::getInstance()->getApplicationByName($this->_applicationName)->getId(),
            $_record, $_currentRecord);
        $modLog->setRecordMetaData($_record, 'update', $_currentRecord);
    }
    
    /**
     * get backend type
     * 
     * @return string
     */
    protected function _getBackendType()
    {
        $type = (method_exists( $this->_backend, 'getType')) ? $this->_backend->getType() : 'Sql';
        return $type;
    }

    /**
     * write modlog
     * 
     * @param Tinebase_Record_Interface $_newRecord
     * @param Tinebase_Record_Interface $_oldRecord
     * @return NULL|Tinebase_Record_RecordSet
     * @throws Tinebase_Exception_InvalidArgument
     */
    protected function _writeModLog($_newRecord, $_oldRecord)
    {
        if (! is_object($_newRecord)) {
            throw new Tinebase_Exception_InvalidArgument('record object expected');
        }
        
        if (! $_newRecord->has('created_by') || $this->_omitModLog === TRUE) {
            return NULL;
        }

        if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__
            . ' Writing modlog for ' . get_class($_newRecord));
    
        $currentMods = Tinebase_Timemachine_ModificationLog::getInstance()->writeModLog($_newRecord, $_oldRecord, $this->_modelName,
                                                                                        $this->_getBackendType(), $_newRecord->getId());
        
        return $currentMods;
    }
    
    /**
     * set relations / tags / alarms
     * 
     * @param   Tinebase_Record_Interface $updatedRecord   the just updated record
     * @param   Tinebase_Record_Interface $record          the update record
     * @param   Tinebase_Record_Interface $currentRecord   the original record if one exists
     * @param   boolean $returnUpdatedRelatedData
     * @return  Tinebase_Record_Interface
     */
    protected function _setRelatedData(Tinebase_Record_Interface $updatedRecord, Tinebase_Record_Interface $record, Tinebase_Record_Interface $currentRecord = null, $returnUpdatedRelatedData = FALSE)
    {
        if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__
            . ' Update record: ' . print_r($record->toArray(), true));

        $pathPartChanged = false;
        $relationsTouched = false;

        // relations won't be touched if the property is set to NULL
        // an empty array on the relations property will remove all relations
        if ($record->has('relations') && isset($record->relations) && is_array($record->relations)) {
            $type = $this->_getBackendType();
            Tinebase_Relations::getInstance()->setRelations(
                $this->_modelName,
                $type,
                $updatedRecord->getId(),
                $record->relations,
                FALSE,
                $this->_inspectRelatedRecords,
                $this->_doRelatedCreateUpdateCheck);

            $relationsTouched = true;

        } elseif (null === $currentRecord || $this->_getPathPart($currentRecord) !== $this->_getPathPart($updatedRecord)) {
            $pathPartChanged = true;
        }

        // rebuild paths if relations where set or if pathPart changed
        if (true === $this->_useRecordPaths && (true === $pathPartChanged || true === $relationsTouched)) {
            $this->_rebuildRelationPaths($updatedRecord, $record, $currentRecord, $relationsTouched);
        }

        if ($record->has('tags') && isset($record->tags) && (is_array($record->tags) || $record->tags instanceof Tinebase_Record_RecordSet)) {
            $updatedRecord->tags = $record->tags;
            Tinebase_Tags::getInstance()->setTagsOfRecord($updatedRecord);
        }
        if ($record->has('alarms') && isset($record->alarms)) {
            $this->_saveAlarms($record);
        }
        if ($record->has('attachments') && isset($record->attachments) && Tinebase_Core::isFilesystemAvailable()) {
            $updatedRecord->attachments = $record->attachments;
            Tinebase_FileSystem_RecordAttachments::getInstance()->setRecordAttachments($updatedRecord);
        }
        
        if ($returnUpdatedRelatedData) {
            $this->_getRelatedData($updatedRecord);
        }

        return $updatedRecord;
    }

    /**
     * @param Tinebase_Record_Interface $updatedRecord
     * @param Tinebase_Record_Interface $record
     * @param Tinebase_Record_Interface $currentRecord
     * @param boolean $relationsTouched
     */
    protected function _rebuildRelationPaths($updatedRecord, $record, $currentRecord, $relationsTouched)
    {
        // rebuild own paths
        $this->buildPath($updatedRecord);

        // rebuild paths of children that have been added or removed
        if ($relationsTouched) {
            //we need to do this to reload the relations in the next line...
            $record->relations = null;
            $newChildRelations = Tinebase_Relations::getInstance()->getRelationsOfRecordByDegree($updatedRecord, Tinebase_Model_Relation::DEGREE_CHILD);
            if (null === $currentRecord) {
                $oldChildRelations = new Tinebase_Record_RecordSet('Tinebase_Model_Relation');
            } else {
                $oldChildRelations = Tinebase_Relations::getInstance()->getRelationsOfRecordByDegree($currentRecord, Tinebase_Model_Relation::DEGREE_CHILD);
            }

            foreach ($newChildRelations as $relation) {
                $oldOffset = $oldChildRelations->getIndexById($relation->id);
                // new child
                if (false === $oldOffset) {
                    $this->buildPath($relation->related_record);
                } else {
                    $oldChildRelations->offsetUnset($oldOffset);
                }
            }

            //removed children
            foreach ($oldChildRelations as $relation) {
                $this->buildPath($relation->related_record);
            }
        }
    }

    /**
     * set notes
     * 
     * @param   Tinebase_Record_Interface $_updatedRecord   the just updated record
     * @param   Tinebase_Record_Interface $_record          the update record
     * @param   string $_systemNoteType
     * @param   Tinebase_Record_RecordSet $_currentMods
     */
    protected function _setNotes($_updatedRecord, $_record, $_systemNoteType = Tinebase_Model_Note::SYSTEM_NOTE_NAME_CREATED, $_currentMods = NULL)
    {
        if (! $_record->has('notes') || $this->_setNotes === false) {
            return;
        }

        if (isset($_record->notes) && is_array($_record->notes)) {
            $_updatedRecord->notes = $_record->notes;
            Tinebase_Notes::getInstance()->setNotesOfRecord($_updatedRecord);
        }
        Tinebase_Notes::getInstance()->addSystemNote($_updatedRecord, Tinebase_Core::getUser(), $_systemNoteType, $_currentMods);
    }
    
    /**
     * inspect update of one record (before update)
     *
     * @param   Tinebase_Record_Interface $_record      the update record
     * @param   Tinebase_Record_Interface $_oldRecord   the current persistent record
     * @return  void
     */
    protected function _inspectBeforeUpdate($_record, $_oldRecord)
    {
    }

    /**
     * inspect update of one record (after update)
     *
     * @param   Tinebase_Record_Interface $updatedRecord   the just updated record
     * @param   Tinebase_Record_Interface $record          the update record
     * @param   Tinebase_Record_Interface $currentRecord   the current record (before update)
     * @return  void
     */
    protected function _inspectAfterUpdate($updatedRecord, $record, $currentRecord)
    {
    }

    /**
     * update modlog / metadata / add systemnote for multiple records defined by filter
     * 
     * NOTE: this should be done in a transaction because of the concurrency handling as
     *  we want the same seq in the record and in the modlog
     * 
     * @param Tinebase_Model_Filter_FilterGroup|array $_filterOrIds
     * @param array $_oldData
     * @param array $_newData
     */
    public function concurrencyManagementAndModlogMultiple($_filterOrIds, $_oldData, $_newData)
    {
        $ids = ($_filterOrIds instanceof Tinebase_Model_Filter_FilterGroup) ? $this->search($_filterOrIds, NULL, FALSE, TRUE, 'update') : $_filterOrIds;
        if (! is_array($ids) || count($ids) === 0) {
            return;
        }
        
        if ($this->_omitModLog !== TRUE) {
            $recordSeqs = $this->_backend->getPropertyByIds($ids, 'seq');
            
            list($currentAccountId, $currentTime) = Tinebase_Timemachine_ModificationLog::getCurrentAccountIdAndTime();
            $updateMetaData = array(
                'last_modified_by'   => $currentAccountId,
                'last_modified_time' => $currentTime,
                'seq'                => new Zend_Db_Expr('seq + 1'),
                'recordSeqs'         => $recordSeqs, // is not written to DB yet
            );
        } else {
            $updateMetaData = array();
        }
        
        $this->_backend->updateMultiple($ids, $updateMetaData);
        
        if ($this->_omitModLog !== TRUE && is_object(Tinebase_Core::getUser())) {
            if (Tinebase_Core::isLogLevel(Zend_Log::INFO)) Tinebase_Core::getLogger()->info(__METHOD__ . '::' . __LINE__
                . ' Writing modlog for ' . count($ids) . ' records ...');
            
            $currentMods = Tinebase_Timemachine_ModificationLog::getInstance()->writeModLogMultiple($ids, $_oldData, $_newData, $this->_modelName, $this->_getBackendType(), $updateMetaData);
            /** @noinspection PhpUndefinedVariableInspection */
            Tinebase_Notes::getInstance()->addMultipleModificationSystemNotes($currentMods, $currentAccountId, $this->_modelName);
        }
    }
    
    /**
     * handles relations on update multiple
     *
     * Syntax 1 (old): key: '%<type>-<related_model>', value: <related_id>
     * Syntax 2      : key: '%<add|remove|replace>', value: <relation json>
     * 
     * @param string $key
     * @param string $value
     * @throws Tinebase_Exception_Record_DefinitionFailure
     */
    protected function _handleRelationsOnUpdateMultiple($key, $value)
    {
        if (preg_match('/%(add|remove|replace)/', $key, $matches)) {
            $action = $matches[1];
            $rel = json_decode($value, true);
        } else if (preg_match('/%(.+)-((.+)_Model_(.+))/', $key, $a)) {
            $action = $value ? 'replace' : 'remove';
            $rel = array(
                'related_model' => $a[2],
                'type' => $a[1],
                'related_id' => $value,
            );
        } else {
            throw new Tinebase_Exception_Record_DefinitionFailure('The relation to delete/set is not configured properly!');
        }

        // find constraint config
        $constraintsConfig = array();
        $relConfig = Tinebase_Relations::getConstraintsConfigs(array($this->_modelName, $rel['related_model']));
        if ($relConfig) {
            foreach ($relConfig as $config) {
                if ($rel['related_model'] == "{$config['relatedApp']}_Model_{$config['relatedModel']}" && isset($config['config']) && is_array($config['config'])) {
                    foreach ($config['config'] as $constraint) {
                        if (isset($rel['type']) && isset($constraint['type']) && $constraint['type'] == $rel['type']) {
                            $constraintsConfig = $constraint;
                            break 2;
                        }
                    }
                }
            }
        }

        // apply defaults
        $rel = array_merge($rel, array(
            'own_model'         => $this->_modelName,
            'own_backend'       => 'Sql',
            'related_backend'   => 'Sql',
            'related_degree'    => isset($rel['related_degree']) ? $rel['related_degree'] :
                                    (isset($constraintsConfig['sibling']) ? isset($constraintsConfig['sibling']) : 'sibling'),
            'type'              => isset($rel['type']) ? $rel['type'] :
                                    (isset($constraintsConfig['type']) ? isset($constraintsConfig['type']) : ' '),
            'remark'            => isset($rel['remark']) ? $rel['remark'] :
                                    (isset($constraintsConfig['defaultRemark']) ? isset($constraintsConfig['defaultRemark']) : ' '),
        ));

        if (in_array($action, array('remove', 'replace'))) {
            $this->_removeRelations ?: array();
            $this->_removeRelations[] = $rel;
        }

        if (in_array($action, array('add', 'replace'))) {
            $this->_newRelations ?: array();
            $this->_newRelations[] = $rel;
        }

        if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) {
            Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . ' New relations: ' . print_r($this->_newRelations, true)
               . ' Remove relations: ' . print_r($this->_removeRelations, true));
        }
    }

    /**
     * update multiple records
     *
     * @param   Tinebase_Model_Filter_FilterGroup $_filter
     * @param   array $_data
     * @return  array $this->_updateMultipleResult
     * 
     * @todo add param $_returnFullResults (if false, do not return updated records in 'results')
     */
    public function updateMultiple($_filter, $_data)
    {
        $this->_checkRight('update');
        $this->checkFilterACL($_filter, 'update');
        $getRelations = false;
        
        $this->_newRelations = NULL;
        $this->_removeRelations = NULL;
        
        foreach ($_data as $key => $value) {
            if (stristr($key,'#')) {
                $_data['customfields'][substr($key,1)] = $value;
                unset($_data[$key]);
            }
            if (stristr($key, '%')) {
                $getRelations = true;
                $this->_handleRelationsOnUpdateMultiple($key, $value);
                unset($_data[$key]);
            }
        }

        $this->_updateMultipleResult = array(
            'results'           => new Tinebase_Record_RecordSet($this->_modelName),
            'exceptions'        => new Tinebase_Record_RecordSet('Tinebase_Model_UpdateMultipleException'),
            'totalcount'        => 0,
            'failcount'         => 0,
        );

        $iterator = new Tinebase_Record_Iterator(array(
            'iteratable' => $this,
            'controller' => $this,
            'filter'     => $_filter,
            'options'    => array('getRelations' => $getRelations),
            'function'   => 'processUpdateMultipleIteration',
        ));
        /*$result = */$iterator->iterate($_data);
    
        if (Tinebase_Core::isLogLevel(Zend_Log::INFO)) {
            Tinebase_Core::getLogger()->info(__METHOD__ . '::' . __LINE__ . ' Updated ' . $this->_updateMultipleResult['totalcount'] . ' records.');
        }
        
        if ($this->_clearCustomFieldsCache) {
            Tinebase_Core::getCache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('customfields'));
        }
        
        return $this->_updateMultipleResult;
    }
    
    /**
     * enable / disable notes
     *
     * @param boolean $setTo
     * @return boolean
     */
    public function useNotes($setTo = NULL)
    {
        return $this->_setBooleanMemberVar('_setNotes', $setTo);
    }
    
    /**
     * iterate relations
     * 
     * @param Tinebase_Record_Interface $currentRecord
     * @return array
     */
    protected function _iterateRelations($currentRecord)
    {
        if(! $currentRecord->relations || get_class($currentRecord->relations) != 'Tinebase_Record_RecordSet') {
            $currentRecord->relations = new Tinebase_Record_RecordSet('Tinebase_Model_Relation');
        }

        // handle relations to remove
        if($this->_removeRelations) {
            if($currentRecord->relations->count()) {
                foreach($this->_removeRelations as $remRelation) {
                    $removeRelations = $currentRecord->relations
                        ->filter('type', $remRelation['type'])
                        ->filter('related_model', $remRelation['related_model']);
                    
                    $currentRecord->relations->removeRecords($removeRelations);
                }
            }
        }

        // handle new relations
        if($this->_newRelations) {
            foreach($this->_newRelations as $newRelation) {
                // convert duplicate to update (remark / degree)
                $duplicate = $currentRecord->relations
                    ->filter('related_model', $newRelation['related_model'])
                    ->filter('related_id',    $newRelation['related_id'])
                    ->filter('type',          $newRelation['type'])
                    ->getFirstRecord();

                if ($duplicate) {
                    $currentRecord->relations->removeRecord($duplicate);
                }

                $newRelation['own_id'] = $currentRecord->getId();
                $rel = new Tinebase_Model_Relation();
                $rel->setFromArray($newRelation);
                $currentRecord->relations->addRecord($rel);
            }
        }
        
        return $currentRecord->relations->toArray();
    }

    /**
     * update multiple records in an iteration
     * @see Tinebase_Record_Iterator / self::updateMultiple()
     *
     * @param Tinebase_Record_RecordSet $_records
     * @param array $_data
     * @throws Exception
     * @throws Tinebase_Exception_Record_Validation
     */
    public function processUpdateMultipleIteration($_records, $_data)
    {
        if (count($_records) === 0) {
            return;
        }
        $bypassFilters = FALSE;
        /** @var Tinebase_Record_Interface $currentRecord */
        foreach ($_records as $currentRecord) {
            $oldRecordArray = $currentRecord->toArray();
            unset($oldRecordArray['relations']);
            
            $data = array_merge($oldRecordArray, $_data);
            
            if ($this->_newRelations || $this->_removeRelations) {
                $data['relations'] = $this->_iterateRelations($currentRecord);
            }
            try {
                $record = new $this->_modelName($data, $bypassFilters);
                $updatedRecord = $this->update($record, FALSE);

                /** @noinspection PhpUndefinedMethodInspection */
                $this->_updateMultipleResult['results']->addRecord($updatedRecord);
                $this->_updateMultipleResult['totalcount'] ++;
                
            } catch (Tinebase_Exception_Record_Validation $e) {
                if ($this->_updateMultipleValidateEachRecord === FALSE) {
                    throw $e;
                }
                /** @noinspection PhpUndefinedMethodInspection */
                $this->_updateMultipleResult['exceptions']->addRecord(new Tinebase_Model_UpdateMultipleException(array(
                    'id'         => $currentRecord->getId(),
                    'exception'  => $e,
                    'record'     => $currentRecord,
                    'code'       => $e->getCode(),
                    'message'    => $e->getMessage()
                )));
                $this->_updateMultipleResult['failcount'] ++;
            }
            if ($this->_updateMultipleValidateEachRecord === FALSE) {
                // only validate the first record
                $bypassFilters = TRUE;
            }
        }
    }

    /**
     * Deletes a set of records.
     *
     * If one of the records could not be deleted, no record is deleted
     *
     * @param  array|Tinebase_Record_Interface|Tinebase_Record_RecordSet $_ids array of record identifiers
     * @return Tinebase_Record_RecordSet
     * @throws Exception
     */
    public function delete($_ids)
    {
        if ($_ids instanceof $this->_modelName || $_ids instanceof Tinebase_Record_RecordSet) {
            /** @var Tinebase_Record_Interface $_ids */
            $_ids = (array)$_ids->getId();
        }
        /** @var array $_ids */

        $ids = $this->_inspectDelete((array) $_ids);

        $records = $this->_backend->getMultiple((array)$ids);
        
        if (count((array)$ids) != count($records)) {
            Tinebase_Core::getLogger()->notice(__METHOD__ . '::' . __LINE__ . ' Only ' . count($records) . ' of ' . count((array)$ids) . ' records exist.');
        }
        
        if (empty($records)) {
            return $records;
        }
        
        if (Tinebase_Core::isLogLevel(Zend_Log::INFO)) Tinebase_Core::getLogger()->info(__METHOD__ . '::' . __LINE__
            . ' Deleting ' . count($records) . ' of ' . $this->_modelName . ' records ...');

        try {
            $db = $this->_backend->getAdapter();
            $transactionId = Tinebase_TransactionManager::getInstance()->startTransaction($db);
            $this->_checkRight('delete');
            
            foreach ($records as $record) {
                if ($this->sendNotifications()) {
                    $this->_getRelatedData($record);
                }
                $this->_deleteRecord($record);
            }

            Tinebase_TransactionManager::getInstance()->commitTransaction($transactionId);

            // send notifications
            if ($this->sendNotifications()) {
                foreach ($records as $record) {
                    $this->doSendNotifications($record, Tinebase_Core::getUser(), 'deleted');
                }
            }

        } catch (Exception $e) {
            Tinebase_TransactionManager::getInstance()->rollBack();
            Tinebase_Core::getLogger()->err(__METHOD__ . '::' . __LINE__ . ' ' . print_r($e->getMessage(), true));
            throw $e;
        }
        
        if ($this->_clearCustomFieldsCache) {
             Tinebase_Core::getCache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('customfields'));
        }

        // returns deleted records
        return $records;
    }

    /**
     * delete records by filter
     *
     * @param Tinebase_Model_Filter_FilterGroup $_filter
     * @return  Tinebase_Record_RecordSet
     */
    public function deleteByFilter(Tinebase_Model_Filter_FilterGroup $_filter)
    {
        $oldMaxExcecutionTime = ini_get('max_execution_time');

        Tinebase_Core::setExecutionLifeTime(300); // 5 minutes

        $ids = $this->search($_filter, NULL, FALSE, TRUE);
        $deletedRecords = $this->delete($ids);
        
        // reset max execution time to old value
        Tinebase_Core::setExecutionLifeTime($oldMaxExcecutionTime);

        return $deletedRecords;
    }

    /**
     * inspects delete action
     *
     * @param array $_ids
     * @return array of ids to actually delete
     */
    protected function _inspectDelete(array $_ids)
    {
        return $_ids;
    }

    /**
     * move records to new container / folder / whatever
     *
     * @param mixed $_records (can be record set, filter, array, string)
     * @param mixed $_target (string, container record, ...)
     * @param string $_containerProperty
     * @return array
     * @throws Tinebase_Exception_AccessDenied
     * @throws Tinebase_Exception_NotFound
     */
    public function move($_records, $_target, $_containerProperty = 'container_id')
    {
        $records = $this->_convertToRecordSet($_records);
        $targetContainerId = ($_target instanceof Tinebase_Model_Container) ? $_target->getId() : $_target;

        if ($this->_doContainerACLChecks) {
            // check add grant in target container
            if (! Tinebase_Core::getUser()->hasGrant($targetContainerId, Tinebase_Model_Grants::GRANT_ADD)) {
                Tinebase_Core::getLogger()->notice(__METHOD__ . '::' . __LINE__ . ' Permission denied to add records to container.');
                throw new Tinebase_Exception_AccessDenied('You are not allowed to move records to this container');
            }
            
            // check delete grant in source container
            $containerIdsWithDeleteGrant = Tinebase_Container::getInstance()->getContainerByACL(Tinebase_Core::getUser(), $this->_applicationName, Tinebase_Model_Grants::GRANT_DELETE, TRUE);
            if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__
                . ' Containers with delete grant: ' . print_r($containerIdsWithDeleteGrant, true));
            foreach ($records as $index => $record) {
                if (! in_array($record->{$_containerProperty}, $containerIdsWithDeleteGrant)) {
                    Tinebase_Core::getLogger()->notice(__METHOD__ . '::' . __LINE__
                        . ' Permission denied to remove record ' . $record->getId() . ' from container ' . $record->{$_containerProperty}
                    );
                    unset($records[$index]);
                }
            }
        }
        
        if (Tinebase_Core::isLogLevel(Zend_Log::INFO)) Tinebase_Core::getLogger()->info(__METHOD__ . '::' . __LINE__
            . ' Moving ' . count($records) . ' ' . $this->_modelName . '(s) to container ' . $targetContainerId);
        
        // move (update container id)
        $idsToMove = $records->getArrayOfIds();
        $filterClass = $this->_modelName . 'Filter';
        if (! class_exists($filterClass)) {
            throw new Tinebase_Exception_NotFound('Filter class ' . $filterClass . ' not found!');
        }
        $filter = new $filterClass(array(
            array('field' => 'id', 'operator' => 'in', 'value' => $idsToMove)
        ));
        /*$updateResult = */$this->updateMultiple($filter, array(
            $_containerProperty => $targetContainerId
        ));
        
        return $idsToMove;
    }

    /*********** helper funcs **************/

    /**
     * delete one record
     *
     * @param Tinebase_Record_Interface $_record
     * @throws Tinebase_Exception_AccessDenied
     */
    protected function _deleteRecord(Tinebase_Record_Interface $_record)
    {
        $this->_checkGrant($_record, 'delete');

        $this->_deleteLinkedObjects($_record);

        if (! $this->_purgeRecords && $_record->has('created_by')) {
            Tinebase_Timemachine_ModificationLog::setRecordMetaData($_record, 'delete', $_record);
            $this->_backend->update($_record);
        } else {
            $this->_backend->delete($_record);
        }
        
        $this->_increaseContainerContentSequence($_record, Tinebase_Model_ContainerContent::ACTION_DELETE);
    }

    /**
     * delete linked objects (notes, relations, ...) of record
     *
     * @param Tinebase_Record_Interface $_record
     */
    protected function _deleteLinkedObjects(Tinebase_Record_Interface $_record)
    {
        // delete notes & relations
        if ($_record->has('notes')) {
            Tinebase_Notes::getInstance()->deleteNotesOfRecord($this->_modelName, $this->_getBackendType(), $_record->getId());
        }
        
        if ($_record->has('relations')) {
            // TODO check if this needs to be done, as we might already deleting this "from the other side"
            $this->deleteLinkedRelations($_record);
        }

        if ($_record->has('attachments') && Tinebase_Core::isFilesystemAvailable()) {
            Tinebase_FileSystem_RecordAttachments::getInstance()->deleteRecordAttachments($_record);
        }
    }
    
    /**
     * delete linked relations
     * 
     * @param Tinebase_Record_Interface $record
     * @param array $modelsToDelete
     * @param array $typesToDelete
     */
    public function deleteLinkedRelations(Tinebase_Record_Interface $record, $modelsToDelete = array(), $typesToDelete = array())
    {
        $relations = isset($record->relations) && $record->relations instanceof Tinebase_Record_RecordSet
            ? $record->relations
            : Tinebase_Relations::getInstance()->getRelations($this->_modelName, $this->_getBackendType(), $record->getId());

        if (count($relations) === 0) {
            return;
        }

        // unset record relations
        Tinebase_Relations::getInstance()->setRelations($this->_modelName, $this->_getBackendType(), $record->getId(), array());

        if (empty($modelsToDelete)) {
            $modelsToDelete = $this->_relatedObjectsToDelete;
        }
        if (empty($modelsToDelete) && empty($typesToDelete)) {
            return;
        }
        
        // remove related objects
        Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . ' Deleting all '
            . implode(',', $modelsToDelete) . ' relations.');

        foreach ($relations as $relation) {
            if (in_array($relation->related_model, $modelsToDelete) || in_array($relation->type, $typesToDelete)) {
                list($appName, /*$i*/, $itemName) = explode('_', $relation->related_model);
                $appController = Tinebase_Core::getApplicationInstance($appName, $itemName);

                try {
                    $appController->delete($relation->related_id);
                } catch (Exception $e) {
                    Tinebase_Core::getLogger()->warn(__METHOD__ . '::' . __LINE__ . ' Error deleting: ' . $e->getMessage());
                }
            }
        }
    }


    /**
     * check grant for action (CRUD)
     *
     * @param Tinebase_Record_Interface $_record
     * @param string $_action
     * @param boolean $_throw
     * @param string $_errorMessage
     * @param Tinebase_Record_Interface $_oldRecord
     * @return boolean
     * @throws Tinebase_Exception_AccessDenied
     *
     * @todo use this function in other create + update functions
     * @todo invent concept for simple adding of grants (plugins?)
     *
     */
    protected function _checkGrant($_record, $_action, $_throw = TRUE, $_errorMessage = 'No Permission.',
        /** @noinspection PhpUnusedParameterInspection */ $_oldRecord = NULL)
    {
        if (   ! $this->_doContainerACLChecks
            || ! $_record->has('container_id')) {
            return TRUE;
        }
        
        if (! is_object(Tinebase_Core::getUser())) {
            throw new Tinebase_Exception_AccessDenied('User object required to check grants');
        }
        
        // admin grant includes all others
        if (Tinebase_Core::getUser()->hasGrant($_record->container_id, Tinebase_Model_Grants::GRANT_ADMIN)) {
            return TRUE;
        }
        
        $hasGrant = FALSE;
        
        switch ($_action) {
            case 'get':
                $hasGrant = Tinebase_Core::getUser()->hasGrant($_record->container_id, Tinebase_Model_Grants::GRANT_READ);
                break;
            case 'create':
                $hasGrant = Tinebase_Core::getUser()->hasGrant($_record->container_id, Tinebase_Model_Grants::GRANT_ADD);
                break;
            case 'update':
                $hasGrant = Tinebase_Core::getUser()->hasGrant($_record->container_id, Tinebase_Model_Grants::GRANT_EDIT);
                break;
            case 'delete':
                $hasGrant = Tinebase_Core::getUser()->hasGrant($_record->container_id, Tinebase_Model_Grants::GRANT_DELETE);
                break;
        }

        if (! $hasGrant) {
            Tinebase_Core::getLogger()->notice(__METHOD__ . '::' . __LINE__ . ' No permissions to ' . $_action . ' in container ' . $_record->container_id);
            if ($_throw) {
                throw new Tinebase_Exception_AccessDenied($_errorMessage);
            }
        }
        
        return $hasGrant;
    }

    /**
     * overwrite this function to check rights
     *
     * @param string $_action {get|create|update|delete}
     * @throws Tinebase_Exception_AccessDenied
     */
    protected function _checkRight(/** @noinspection PhpUnusedParameterInspection */
                                    $_action)
    {
        return;
    }

    /**
     * Removes containers where current user has no access to
     *
     * @param Tinebase_Model_Filter_FilterGroup $_filter
     * @param string $_action get|update
     */
    public function checkFilterACL(Tinebase_Model_Filter_FilterGroup $_filter, $_action = 'get')
    {
        if (! $this->_doContainerACLChecks) {
            if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__ 
                . ' Container ACL disabled for ' . $_filter->getModelName() . '.');
            return;
        }

        $aclFilters = $_filter->getAclFilters();

        if (! $aclFilters) {
            if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__
                . ' Force a standard containerFilter (specialNode = all) as ACL filter.');
            
            $containerFilter = $_filter->createFilter('container_id', 'specialNode', 'all', array('applicationName' => $_filter->getApplicationName()));
            $_filter->addFilter($containerFilter);
        }

        if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__
            . ' Setting filter grants for action ' . $_action);
        switch ($_action) {
            case 'get':
                $_filter->setRequiredGrants(array(
                    Tinebase_Model_Grants::GRANT_READ,
                    Tinebase_Model_Grants::GRANT_ADMIN,
                ));
                break;
            case 'update':
                $_filter->setRequiredGrants(array(
                    Tinebase_Model_Grants::GRANT_EDIT,
                    Tinebase_Model_Grants::GRANT_ADMIN,
                ));
                break;
            case 'export':
                $_filter->setRequiredGrants(array(
                    Tinebase_Model_Grants::GRANT_EXPORT,
                    Tinebase_Model_Grants::GRANT_ADMIN,
                ));
                break;
            case 'sync':
                $_filter->setRequiredGrants(array(
                    Tinebase_Model_Grants::GRANT_SYNC,
                    Tinebase_Model_Grants::GRANT_ADMIN,
                ));
                break;
            default:
                throw new Tinebase_Exception_UnexpectedValue('Unknown action: ' . $_action);
        }
    }

    /**
     * saves alarms of given record
     *
     * @param Tinebase_Record_Interface $_record
     * @return void
     */
    protected function _saveAlarms(Tinebase_Record_Interface $_record)
    {
        if (! $_record->alarms instanceof Tinebase_Record_RecordSet) {
            $_record->alarms = new Tinebase_Record_RecordSet('Tinebase_Model_Alarm');
        }
        $alarms = new Tinebase_Record_RecordSet('Tinebase_Model_Alarm');

        // create / update alarms
        foreach ($_record->alarms as $alarm) {
            try {
                $this->_inspectAlarmSet($_record, $alarm);
                $alarms->addRecord($alarm);
            } catch (Tinebase_Exception_InvalidArgument $teia) {
                Tinebase_Core::getLogger()->warn(__METHOD__ . '::' . __LINE__ . ' ' . $teia->getMessage());
            }
        }

        Tinebase_Core::getLogger()->info(__METHOD__ . '::' . __LINE__
            . " About to save " . count($alarms) . " alarms for {$_record->getId()} ");
        $_record->alarms = $alarms;

        Tinebase_Alarm::getInstance()->setAlarmsOfRecord($_record);
    }

    /**
     * inspect alarm and set time
     *
     * @param Tinebase_Record_Interface $_record
     * @param Tinebase_Model_Alarm $_alarm
     * @return void
     * @throws Tinebase_Exception_InvalidArgument
     */
    protected function _inspectAlarmSet(Tinebase_Record_Interface $_record, Tinebase_Model_Alarm $_alarm)
    {
        if (! $_record->{$this->_recordAlarmField} instanceof DateTime) {
            throw new Tinebase_Exception_InvalidArgument('alarm reference time is not set');
        }

        $_alarm->setTime($_record->{$this->_recordAlarmField});
    }

    /**
     * get and resolve all alarms of given record(s)
     *
     * @param  Tinebase_Record_Interface|Tinebase_Record_RecordSet $_record
     */
    public function getAlarms($_record)
    {
        if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . " Resolving alarms and add them to record set.");
        
        $records = $_record instanceof Tinebase_Record_RecordSet ? $_record : new Tinebase_Record_RecordSet($this->_modelName, array($_record));

        $alarms = Tinebase_Alarm::getInstance()->getAlarmsOfRecord($this->_modelName, $records);
        
        foreach ($alarms as $alarm) {
            $record = $records->getById($alarm->record_id);
            
            if (!isset($record->alarms)) {
                $record->alarms = new Tinebase_Record_RecordSet('Tinebase_Model_Alarm');
            }
            
            if (!$record->alarms->getById($alarm->getId())) {
                $record->alarms->addRecord($alarm);
            }
        }
        
        foreach ($records as $record) {
            if (!isset($record->alarms)) {
                $record->alarms = new Tinebase_Record_RecordSet('Tinebase_Model_Alarm');
            } else {
                // calc minutes_before
                if ($record->has($this->_recordAlarmField) && $record->{$this->_recordAlarmField} instanceof DateTime) {
                    $this->_inspectAlarmGet($record);
                }
            }
        }
    }

    /**
     * inspect alarms of record (all alarms minutes_before fields are set here by default)
     *
     * @param Tinebase_Record_Interface $_record
     * @return void
     */
    protected function _inspectAlarmGet(Tinebase_Record_Interface $_record)
    {
        $_record->alarms->setMinutesBefore($_record->{$this->_recordAlarmField});
    }

    /**
     * delete alarms for records
     *
     * @param array $_recordIds
     */
    protected function _deleteAlarmsForIds($_recordIds)
    {
        if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__
            . " Deleting alarms for records " . print_r($_recordIds, TRUE)
        );

        Tinebase_Alarm::getInstance()->deleteAlarmsOfRecord($this->_modelName, $_recordIds);
    }

    /**
     * convert input to recordset
     *
     * input can have the following datatypes:
     * - Tinebase_Model_Filter_FilterGroup
     * - Tinebase_Record_RecordSet
     * - Tinebase_Record_Abstract
     * - string (single id)
     * - array (multiple ids)
     *
     * @param mixed $_mixed
     * @param boolean $_refresh if this is TRUE, refresh the recordset by calling getMultiple
     * @param Tinebase_Model_Pagination $_pagination (only valid if $_mixed instanceof Tinebase_Model_Filter_FilterGroup)
     * @return Tinebase_Record_RecordSet
     */
    protected function _convertToRecordSet($_mixed, $_refresh = FALSE, Tinebase_Model_Pagination $_pagination = NULL)
    {
        if ($_mixed instanceof Tinebase_Model_Filter_FilterGroup) {
            // FILTER (Tinebase_Model_Filter_FilterGroup)
            $result = $this->search($_mixed, $_pagination);
        } elseif ($_mixed instanceof Tinebase_Record_RecordSet) {
            // RECORDSET (Tinebase_Record_RecordSet)
            $result = ($_refresh) ? $this->_backend->getMultiple($_mixed->getArrayOfIds()) : $_mixed;
        } elseif ($_mixed instanceof Tinebase_Record_Abstract) {
            // RECORD (Tinebase_Record_Abstract)
            if ($_refresh) {
                $result = $this->_backend->getMultiple($_mixed->getId());
            } else {
                $result = new Tinebase_Record_RecordSet(get_class($_mixed), array($_mixed));
            }
        } elseif (is_string($_mixed) || is_array($_mixed)) {
            // SINGLE ID or ARRAY OF IDS
            $result = $this->_backend->getMultiple($_mixed);
        } else {
            if (Tinebase_Core::isLogLevel(Zend_Log::INFO)) Tinebase_Core::getLogger()->info(__METHOD__ . '::' . __LINE__
                . ' Could not convert input param to RecordSet: Unsupported type: ' . gettype($_mixed));
            $result = new Tinebase_Record_RecordSet($this->_modelName);
        }

        return $result;
    }
    

    /**
     * creates dependent records after creating the parent record
     *
     * @param Tinebase_Record_Interface $_createdRecord
     * @param Tinebase_Record_Interface $_record
     * @param string $_property
     * @param array $_fieldConfig
     */
    protected function _createDependentRecords(Tinebase_Record_Interface $_createdRecord, Tinebase_Record_Interface $_record, $_property, $_fieldConfig)
    {
        if (! (isset($_fieldConfig['dependentRecords']) || array_key_exists('dependentRecords', $_fieldConfig)) || ! $_fieldConfig['dependentRecords']) {
            return;
        }
        
        if ($_record->has($_property) && $_record->{$_property}) {
            $recordClassName = $_fieldConfig['recordClassName'];
            $new = new Tinebase_Record_RecordSet($recordClassName);
            /** @var Tinebase_Controller_Interface $ccn */
            $ccn = $_fieldConfig['controllerClassName'];
            /** @var Tinebase_Controller_Record_Interface $controller */
            $controller = $ccn::getInstance();

            /** @var Tinebase_Record_Interface $rec */
            // legacy - should be already done in frontend json - remove if all record properties are record sets before getting to controller
            if (is_array($_record->{$_property})) {
                $rs = new Tinebase_Record_RecordSet($recordClassName);
                foreach ($_record->{$_property} as $recordArray) {
                    /** @var Tinebase_Record_Interface $rec */
                    $rec = new $recordClassName(array(),true);
                    $rec->setFromJsonInUsersTimezone($recordArray);
                    
                    if (strlen($rec->getId()) < 40) {
                        $rec->{$rec->getIdProperty()} = Tinebase_Record_Abstract::generateUID();
                    }
                    
                    $rs->addRecord($rec);
                }
                $_record->{$_property} = $rs;
            } else {
                foreach ($_record->{$_property} as $rec) {
                    if (strlen($rec->getId()) < 40) {
                        $rec->{$rec->getIdProperty()} = Tinebase_Record_Abstract::generateUID();
                    }
                }
            }
            // legacy end

            if (Tinebase_Core::isLogLevel(Zend_Log::INFO)) {
                Tinebase_Core::getLogger()->info(__METHOD__ . '::' . __LINE__. ' Creating ' . $_record->{$_property}->count() . ' dependent records on property ' . $_property . ' for ' . $this->_applicationName . ' ' . $this->_modelName);
            }
            
            foreach ($_record->{$_property} as $record) {
                $record->{$_fieldConfig['refIdField']} = $_createdRecord->getId();
                if (! $record->getId() || strlen($record->getId()) != 40) {
                    $record->{$record->getIdProperty()} = NULL;
                }
                $new->addRecord($controller->create($record));
            }
    
            $_createdRecord->{$_property} = $new->toArray();
        }
    }

    /**
     * updates dependent records on update the parent record
     *
     * @param Tinebase_Record_Interface $_record
     * @param Tinebase_Record_Interface $_oldRecord
     * @param string $_property
     * @param array $_fieldConfig
     * @throws Tinebase_Exception_Record_DefinitionFailure
     * @throws Tinebase_Exception_Record_NotAllowed
     */
    protected function _updateDependentRecords(Tinebase_Record_Interface $_record, /** @noinspection PhpUnusedParameterInspection */
                                               Tinebase_Record_Interface $_oldRecord, $_property, $_fieldConfig)
    {
        if (! (isset($_fieldConfig['dependentRecords']) || array_key_exists('dependentRecords', $_fieldConfig)) || ! $_fieldConfig['dependentRecords']) {
            return;
        }
        
        if (! isset ($_fieldConfig['refIdField'])) {
            throw new Tinebase_Exception_Record_DefinitionFailure('If a record is dependent, a refIdField has to be defined!');
        }
        
        // don't handle dependent records on property if it is set to null or doesn't exist.
        if (($_record->{$_property} === NULL) || (! $_record->has($_property))) {
            if (Tinebase_Core::isLogLevel(Zend_Log::INFO)) {
                Tinebase_Core::getLogger()->info(__METHOD__ . '::' . __LINE__. ' Skip updating dependent records (got NULL) on property ' . $_property . ' for ' . $this->_applicationName . ' ' . $this->_modelName . ' with id = "' . $_record->getId() . '"');
            }
            return;
        }

        /** @var Tinebase_Controller_Interface $ccn */
        $ccn = $_fieldConfig['controllerClassName'];
        /** @var Tinebase_Controller_Record_Interface|Tinebase_Controller_SearchInterface $controller */
        $controller = $ccn::getInstance();
        $recordClassName = $_fieldConfig['recordClassName'];
        $filterClassName = $_fieldConfig['filterClassName'];
        /** @var Tinebase_Record_RecordSet|Tinebase_Record_Interface $existing */
        $existing = new Tinebase_Record_RecordSet($recordClassName);
        
        if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__
            . ' ' . print_r($_record->{$_property}, TRUE));
        
        if (! empty($_record->{$_property}) && $_record->{$_property}) {
            
            // legacy - should be already done in frontend json - remove if all record properties are record sets before getting to controller
            if (is_array($_record->{$_property})) {
                $rs = new Tinebase_Record_RecordSet($recordClassName);
                foreach ($_record->{$_property} as $recordArray) {
                    /** @var Tinebase_Record_Interface $rec */
                    $rec = new $recordClassName(array(),true);
                    $rec->setFromJsonInUsersTimezone($recordArray);
                    $rs->addRecord($rec);
                }
                $_record->{$_property} = $rs;
            }
            
            $idProperty = $_record->{$_property}->getFirstRecord()->getIdProperty();
            
            // legacy end
            $oldFilter = new $filterClassName(array(array('field' => $idProperty, 'operator' => 'in', 'value' => $_record->{$_property}->getId())));
            $oldRecords = $controller->search($oldFilter);
            
            foreach ($_record->{$_property} as $record) {
                
                $record->{$_fieldConfig['refIdField']} = $_record->getId();
                
                // update record if ID exists and has a length of 40 (it has a length of 10 if it is a timestamp)
                if ($record->getId() && strlen($record->getId()) == 40) {
                    
                    // do not try to update if the record hasn't changed
                    $oldRecord = $oldRecords->getById($record->getId());
                    
                    if ($oldRecord && ! empty($oldRecord->diff($record)->diff)) {
                        if (Tinebase_Core::isLogLevel(Zend_Log::INFO)) {
                            Tinebase_Core::getLogger()->info(__METHOD__ . '::' . __LINE__. ' Updating dependent record with id = "' . $record->getId() . '" on property ' . $_property . ' for ' . $this->_applicationName . ' ' . $this->_modelName);
                        }
                        $existing->addRecord($controller->update($record));
                    } else {
                        $existing->addRecord($record);
                    }
                    // create if is not existing already
                } else {
                    // try to find if it already exists (with corrupted id)
                    if ($record->getId() == NULL) {
                        $crc = $controller->create($record);
                        $existing->addRecord($crc);
                        
                        if (Tinebase_Core::isLogLevel(Zend_Log::INFO)) {
                            Tinebase_Core::getLogger()->info(__METHOD__ . '::' . __LINE__. ' Creating dependent record with id = "' . $crc->getId() . '" on property ' . $_property . ' for ' . $this->_applicationName . ' ' . $this->_modelName);
                        }
                    } else {
                        
                        try {
                            
                            $prevRecord = $controller->get($record->getId());
    
                            if (! empty($prevRecord->diff($record)->diff)) {
                                $existing->addRecord($controller->update($record));
                            } else {
                                $existing->addRecord($record);
                            }
                            
                        } catch (Tinebase_Exception_NotFound $e) {
                            $record->id = NULL;
                            $crc = $controller->create($record);
                            $existing->addRecord($crc);
                            
                            if (Tinebase_Core::isLogLevel(Zend_Log::INFO)) {
                                Tinebase_Core::getLogger()->info(__METHOD__ . '::' . __LINE__. ' Creating dependent record with id = "' . $crc->getId() . '" on property ' . $_property . ' for ' . $this->_applicationName . ' ' . $this->_modelName);
                            }
                        }
                    }
                }
            }
        }

        /** @var Tinebase_Model_Filter_FilterGroup $filter */
        $filter = new $filterClassName(isset($_fieldConfig['addFilters']) ? $_fieldConfig['addFilters'] : array(), 'AND');
        $filter->addFilter(new Tinebase_Model_Filter_Text($_fieldConfig['refIdField'], 'equals', $_record->getId()));
        
        // an empty array will remove all records on this property
        if (! empty($_record->{$_property})) {
            $filter->addFilter(new Tinebase_Model_Filter_Id('id', 'notin', $existing->getId()));
        }

        $deleteIds = $controller->search($filter, NULL, FALSE, TRUE);
        
        if (! empty($deleteIds)) {
            if (Tinebase_Core::isLogLevel(Zend_Log::INFO)) {
                Tinebase_Core::getLogger()->info(__METHOD__ . '::' . __LINE__. ' Deleting dependent records with id = "' . print_r($deleteIds, 1) . '" on property ' . $_property . ' for ' . $this->_applicationName . ' ' . $this->_modelName);
            }
            $controller->delete($deleteIds);
        }
        $_record->{$_property} = $existing->toArray();
    }
    
    protected function _createDependentRecord($record, $_record, $_fieldConfig, $controller, $recordSet)
    {
        
    }

    /**
     * returns path of record
     *
     * @param Tinebase_Record_Interface     $record
     * @param boolean|int                   $depth
     * @return Tinebase_Record_RecordSet
     * @throws Tinebase_Exception_Record_NotAllowed
     * @throws Tinebase_Exception
     */
    protected function _getPathsOfRecord(Tinebase_Record_Interface $record, $depth = false)
    {
        if (false !== $depth && $depth > 8) {
            throw new Tinebase_Exception('too many recursions while calculating record path');
        }

        $result = new Tinebase_Record_RecordSet('Tinebase_Model_Path');

        $parentRelations = Tinebase_Relations::getInstance()->getRelationsOfRecordByDegree($record, Tinebase_Model_Relation::DEGREE_PARENT);
        foreach ($parentRelations as $parent) {

            if (!is_object($parent->related_record)) {
                $parent->related_record = Tinebase_Core::getApplicationInstance($parent->related_model)->get($parent->related_id);
            }

            if (false === $depth) {
                // we do not need to generate the parents paths, they should be in DB
                $parentPaths = Tinebase_Record_Path::getInstance()->getPathsForRecords($parent->related_record);
            } else {
                // we have to regenerate parents paths
                $parentPaths = $this->_getPathsOfRecord($parent->related_record, $depth === true ? 1 : $depth + 1);
            }

            if (count($parentPaths) === 0) {
                $path = new Tinebase_Model_Path(array(
                    'path'          => $this->_getPathPart($parent->related_record) . $this->_getPathPart($record, $parent),
                    'shadow_path'   => '/' . $parent->related_id . $this->_getShadowPathPart($record, $parent),
                    'record_id'     => $record->getId(),
                    'creation_time' => Tinebase_DateTime::now(),
                ));
                $result->addRecord($path);
            } else {
                // merge paths
                foreach ($parentPaths as $path) {
                    $newPath = new Tinebase_Model_Path(array(
                        'path'          => $path->path . $this->_getPathPart($record, $parent),
                        'shadow_path'   => $path->shadow_path . $this->_getShadowPathPart($record, $parent),
                        'record_id'     => $record->getId(),
                        'creation_time' => Tinebase_DateTime::now(),
                    ));

                    $result->addRecord($newPath);
                }
            }
        }

        return $result;
    }

    /**
     * @param Tinebase_Record_Interface $record
     * @param $relation
     * @return string
     *
     * TODO use decorators
     */
    protected function _getPathPart(Tinebase_Record_Interface $record, $relation = null)
    {
        $type = $this->_getTypeForPathPart($relation);

        return $type . '/' . mb_substr(str_replace('/', '', trim($record->getTitle())), 0, 32);
    }

    protected function _getTypeForPathPart($relation)
    {
        return ($relation && ! empty($relation->type)) ? '{' . $relation->type . '}' : '';
    }

    /**
     * @param Tinebase_Record_Interface $record
     * @param $relation
     * @return string
     *
     * TODO use decorators
     */
    protected function _getShadowPathPart(Tinebase_Record_Interface $record, $relation = null)
    {
        $type = $this->_getTypeForPathPart($relation);

        return $type . '/' . $record->getId();
    }

    /**
     * shortcut to Tinebase_Record_Path::generatePathForRecord
     *
     * @param $record
     * @param $rebuildRecursively
     */
    public function buildPath($record, $rebuildRecursively = false)
    {
        if ($this->_useRecordPaths) {
            Tinebase_Record_Path::getInstance()->generatePathForRecord($record, $rebuildRecursively);
        }
    }

    // we dont want to mention the throw there, or it would be reflected everywhere
    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * send notifications
     *
     * @param Tinebase_Record_Interface  $_record
     * @param Tinebase_Model_FullUser    $_updater
     * @param String                     $_action
     * @param Tinebase_Record_Interface  $_oldRecord
     * @param Array                      $_additionalData
     */
    public function doSendNotifications(/** @noinspection PhpUnusedParameterInspection */
        Tinebase_Record_Interface $_record, Tinebase_Model_FullUser $_updater, $_action, Tinebase_Record_Interface $_oldRecord = NULL, array $_additionalData = array())
    {
        throw new Tinebase_Exception_NotImplemented(__METHOD__ . ' is not implemented');
    }

    /**
     * @param Tinebase_Model_Container $_container
     * @param bool $_ignoreAcl
     * @param null $_filter
     */
    public function deleteContainerContents(Tinebase_Model_Container $_container, $_ignoreAcl = FALSE, $_filter = null)
    {
        $model = $_container->model;
        $filterName = $model . 'Filter';

        // workaround to fix Filemanager/MailFiler as we don't want to delete container contents when moving folders
        // TODO find a better solution here - needs Filemanager refactoring
        if (! in_array($model, array('Filemanager_Model_Node', 'MailFiler_Model_Node')) &&
            method_exists($this->_backend, 'search') && ($_filter !== null || class_exists($filterName))) {
            Tinebase_Core::getLogger()->info(__METHOD__ . '::' . __LINE__
                . ' Delete ' . $model . ' records in container ' . $_container->getId());

            if (null === $_filter) {
                /** @var Tinebase_Model_Filter_FilterGroup $_filter */
                $_filter = new $filterName(array(), Tinebase_Model_Filter_FilterGroup::CONDITION_AND, array('ignoreAcl' => $_ignoreAcl));
                // we add the container_id filter like this because Calendar Filters have special behaviour that we want to avoid
                // alternatively the calender event controller would have to overwrite this method and deal with this application
                // specifics itself. But for the time being, this seems like a good generic solution
                $_filter->addFilter(new Tinebase_Model_Filter_Id('container_id', 'equals', $_container->id));
            }

            if ($_ignoreAcl) {
                $idsToDelete = $this->_backend->search($_filter, null, /* $_onlyIds */true);
                $this->delete($idsToDelete);
            } else {
                $this->deleteByFilter($_filter);
            }
        }
    }
}