<?php
/**
 * Tine 2.0
 * 
 * @package     Tinebase
 * @subpackage  Model
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Philipp Schüle <p.schuele@metaways.de>
 * @copyright   Copyright (c) 2011 Metaways Infosystems GmbH (http://www.metaways.de)
 * 
 * @todo 0007376: Tinebase_FileSystem / Node model refactoring: move all container related functionality to Filemanager
 */

/**
 * class representing one node path
 * 
 * @package     Tinebase
 * @subpackage  Model
 * 
 * @property    string                      containerType
 * @property    string                      containerOwner
 * @property    string                      flatpath           "real" name path like /personal/user/containername
 * @property    string                      statpath           id path like /personal/USERID/CONTAINERID
 * @property    string                      realpath           path without app/type/container stuff 
 * @property    string                      streamwrapperpath
 * @property    Tinebase_Model_Application  application
 * @property    Tinebase_Model_Container    container
 * @property    Tinebase_Model_FullUser     user
 * @property    string                      name (last part of path)
 * @property    Tinebase_Model_Tree_Node_Path parentrecord
 * 
 * @todo rename this to Tinebase_Model_Tree_Node_FoldersPath ?
 * 
 * exploded flat path should look like this:
 * 
 * [0] => app id [required]
 * [1] => folders [required]
 * [2] => type [required] (personal|shared)
 * [3] => container name | accountLoginName
 * [4] => container name | directory
 * [5] => directory
 * [6] => directory
 * [...]
 */
class Tinebase_Model_Tree_Node_Path extends Tinebase_Record_Abstract
{
    /**
     * streamwrapper path prefix
     */
    const STREAMWRAPPERPREFIX = 'tine20://';
    
    /**
     * root type
     */
    const TYPE_ROOT = 'root';

    /**
     * folders path part
     */
    const FOLDERS_PART = 'folders';

    /**
     * key in $_validators/$_properties array for the field which 
     * represents the identifier
     * 
     * @var string
     */
    protected $_identifier = 'flatpath';
    
    /**
     * application the record belongs to
     *
     * @var string
     */
    protected $_application = 'Tinebase';
    
    /**
     * list of zend validator
     * 
     * this validators get used when validating user generated content with Zend_Input_Filter
     *
     * @var array
     */
    protected $_validators = array (
        'containerType'     => array(Zend_Filter_Input::ALLOW_EMPTY => true),
        'containerOwner'    => array(Zend_Filter_Input::ALLOW_EMPTY => true),
        'flatpath'          => array(Zend_Filter_Input::ALLOW_EMPTY => true),
        'statpath'          => array(Zend_Filter_Input::ALLOW_EMPTY => true),
        'realpath'          => array(Zend_Filter_Input::ALLOW_EMPTY => true),
        'streamwrapperpath' => array(Zend_Filter_Input::ALLOW_EMPTY => true),
        'application'       => array(Zend_Filter_Input::ALLOW_EMPTY => true),
        'container'         => array(Zend_Filter_Input::ALLOW_EMPTY => true),
        'user'              => array(Zend_Filter_Input::ALLOW_EMPTY => true),
        'name'              => array(Zend_Filter_Input::ALLOW_EMPTY => true),
        'parentrecord'      => array(Zend_Filter_Input::ALLOW_EMPTY => true),
    );
    
    /**
     * (non-PHPdoc)
     * @see Tinebase/Record/Tinebase_Record_Abstract::__toString()
     */
    public function __toString()
    {
        return $this->flatpath;
    }
    
    /**
     * create new path record from given path string
     * 
     * @param string|Tinebase_Model_Tree_Node_Path $_path
     * @return Tinebase_Model_Tree_Node_Path
     */
    public static function createFromPath($_path)
    {
        $pathRecord = ($_path instanceof Tinebase_Model_Tree_Node_Path) ? $_path : new Tinebase_Model_Tree_Node_Path(array(
            'flatpath'  => $_path
        ));
        
        return $pathRecord;
    }

    /**
     * create new path record from given stat path (= path with ids) string
     *
     * @param string $statPath
     * @param string $appName
     * @return Tinebase_Model_Tree_Node_Path
     */
    public static function createFromStatPath($statPath, $appName = null)
    {
        $statPath = preg_replace('@^/@', '', $statPath);
        $pathParts = explode('/', $statPath);
        if ($appName !== null) {
            $app = Tinebase_Application::getInstance()->getApplicationByName($appName);
            array_unshift($pathParts, $app->getId(), self::FOLDERS_PART);
        }
        $newStatPath = '/' . implode('/', $pathParts);
        $container = null;

        if (count($pathParts) > 3) {
            // replace account id with login name
            $userId = $pathParts[3];
            try {
                $user = Tinebase_User::getInstance()->getUserByPropertyFromSqlBackend('accountId', $pathParts[3], 'Tinebase_Model_FullUser');
                $containerType = $user->getId() === Tinebase_Core::getUser()->getId()
                    ? Tinebase_Model_Container::TYPE_PERSONAL
                    : Tinebase_Model_Container::TYPE_OTHERUSERS;
                $pathParts[3] = $user->accountLoginName;
            } catch (Tinebase_Exception_NotFound $tenf) {
                // not a user -> shared
                $containerType = Tinebase_Model_Container::TYPE_SHARED;
            }

            // replace container name with id
            $containerPartIdx = ($containerType === Tinebase_Model_Container::TYPE_SHARED) ? 3 : 4;
            if (isset($pathParts[$containerPartIdx])) {
                $container = Tinebase_Container::getInstance()->getContainerById($pathParts[$containerPartIdx]);
                $pathParts[$containerPartIdx] = $container->name;
            }
        }

        $flatPath = '/' . implode('/', $pathParts);
        $pathRecord = new Tinebase_Model_Tree_Node_Path(array(
            'flatpath'      => $flatPath,
            'containerType' => $containerType,
            'statpath'      => $newStatPath,
            'container'     => $container,
        ));

        return $pathRecord;
    }

    /**
     * create new parent path record from given path string
     * 
     * @param string $_path
     * @return array with (Tinebase_Model_Tree_Node_Path, string)
     * 
     * @todo add child to model?
     */
    public static function getParentAndChild($_path)
    {
        $pathParts = $pathParts = explode('/', trim($_path, '/'));
        $child = array_pop($pathParts);
        
        $pathRecord = Tinebase_Model_Tree_Node_Path::createFromPath('/' . implode('/', $pathParts));
        
        return array(
            $pathRecord,
            $child
        );
    }
    
    /**
     * removes app id (and /folders namespace) from a path
     * 
     * @param string $_flatpath
     * @param Tinebase_Model_Application|string $_application
     * @return string
     */
    public static function removeAppIdFromPath($_flatpath, $_application)
    {
        $appId = (is_string($_application)) ? Tinebase_Application::getInstance()->getApplicationById($_application)->getId() : $_application->getId();
        return preg_replace('@^/' . $appId . '/folders@', '', $_flatpath);
    }
    
    /**
     * get parent path of this record
     * 
     * @return Tinebase_Model_Tree_Node_Path
     */
    public function getParent()
    {
        if (! $this->parentrecord) {
            list($this->parentrecord, $unused) = self::getParentAndChild($this->flatpath);
        }
        return $this->parentrecord;
    }
    
    /**
     * sets the record related properties from user generated input.
     * 
     * if flatpath is set, parse it and set the fields accordingly
     *
     * @param array $_data            the new data to set
     */
    public function setFromArray(array $_data)
    {
        parent::setFromArray($_data);
        
        if ((isset($_data['flatpath']) || array_key_exists('flatpath', $_data))) {
            $this->_parsePath($_data['flatpath']);
        }
    }
    
    /**
     * parse given path: check validity, set container type, do replacements
     * 
     * @param string $_path
     */
    protected function _parsePath($_path = NULL)
    {
        if ($_path === NULL) {
            $_path = $this->flatpath;
        }
        
        if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ 
            . ' Parsing path: ' . $_path);
        
        $pathParts = $this->_getPathParts($_path);
        
        $this->name                 = $pathParts[count($pathParts) - 1];
        $this->containerType        = isset($this->containerType) && in_array($this->containerType, array(
            Tinebase_Model_Container::TYPE_PERSONAL,
            Tinebase_Model_Container::TYPE_SHARED,
            Tinebase_Model_Container::TYPE_OTHERUSERS,
        )) ? $this->containerType : $this->_getContainerType($pathParts);
        $this->containerOwner       = $this->_getContainerOwner($pathParts);
        $this->application          = $this->_getApplication($pathParts);
        $this->container            = $this->container instanceof Tinebase_Model_Container ? $this->container : $this->_getContainer($pathParts);
        $this->statpath             = isset($this->statpath) ? $this->statpath : $this->_getStatPath($pathParts);
        $this->realpath             = $this->_getRealPath($pathParts);
        $this->streamwrapperpath    = self::STREAMWRAPPERPREFIX . $this->statpath;
    }
    
    /**
     * get path parts
     * 
     * @param string $_path
     * @return array
     * @throws Tinebase_Exception_InvalidArgument
     */
    protected function _getPathParts($_path = NULL)
    {
        if ($_path === NULL) {
            $_path = $this->flatpath;
        }
        if (! is_string($_path)) {
            throw new Tinebase_Exception_InvalidArgument('Path needs to be a string!');
        }
        $pathParts = explode('/', trim($_path, '/'));
        if (count($pathParts) < 1) {
            throw new Tinebase_Exception_InvalidArgument('Invalid path: ' . $_path);
        }
        
        return $pathParts;
    }
    
    /**
     * get container type from path
     * 
     * @param array $_pathParts
     * @return string
     * @throws Tinebase_Exception_InvalidArgument
     */
    protected function _getContainerType($_pathParts)
    {
        $containerType = (isset($_pathParts[2])) ? $_pathParts[2] : self::TYPE_ROOT;
        
        if (! in_array($containerType, array(
            Tinebase_Model_Container::TYPE_PERSONAL,
            Tinebase_Model_Container::TYPE_SHARED,
            self::TYPE_ROOT
        ))) {
            throw new Tinebase_Exception_InvalidArgument('Invalid type: ' . $containerType);
        }
        
        return $containerType;
    }
    
    /**
     * get container owner from path
     * 
     * @param array $_pathParts
     * @return string
     */
    protected function _getContainerOwner($_pathParts)
    {
        $containerOwner = ($this->containerType !== Tinebase_Model_Container::TYPE_SHARED && isset($_pathParts[3])) ? $_pathParts[3] : NULL;
        
        return $containerOwner;
    }
    
    /**
     * get application from path
     * 
     * @param array $_pathParts
     * @return string
     * @throws Tinebase_Exception_AccessDenied
     */
    protected function _getApplication($_pathParts)
    {
        $application = Tinebase_Application::getInstance()->getApplicationById($_pathParts[0]);
        
        return $application;
    }
    
    /**
     * get container from path
     * 
     * @param array $_pathParts
     * @return Tinebase_Model_Container
     */
    protected function _getContainer($_pathParts)
    {
        if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__ . 
            ' PATH PARTS: ' . print_r($_pathParts, true));
        
        $container = null;
        $containerName = null;

        try {
            switch ($this->containerType) {
                case Tinebase_Model_Container::TYPE_SHARED:
                    if (!empty($_pathParts[3])) {
                        $containerName = $_pathParts[3];
                        $container = Tinebase_Container::getInstance()->getContainerByName(
                            $this->application->name, $containerName, Tinebase_Model_Container::TYPE_SHARED);
                    }
                    break;
                    
                case Tinebase_Model_Container::TYPE_PERSONAL:
                    if (count($_pathParts) > 4) {
                        $subPathParts = explode('/', $_pathParts[4], 2);
                        $owner = null;
                        $containerName = $subPathParts[0];
                        if ($this->containerOwner) {
                            try {
                                $owner = Tinebase_User::getInstance()->getUserByPropertyFromSqlBackend('accountLoginName', $this->containerOwner, 'Tinebase_Model_FullUser');
                            } catch (Tinebase_Exception_NotFound $tenf) {
                                // TODO should be fixed! always assure correct path with names .. we seem to have a statpath here
                                // find owner by login name not found, try with id
                                try {
                                    $owner = Tinebase_User::getInstance()->getUserByPropertyFromSqlBackend('accountId', $this->containerOwner, 'Tinebase_Model_FullUser');
                                } catch (Tinebase_Exception_NotFound $tenf) {
                                }
                            }
                        }
                        if (! $owner) {
                            $owner = Tinebase_Core::getUser();
                        }
                        $container = Tinebase_Container::getInstance()->getContainerByName(
                            $this->application->name, $containerName, Tinebase_Model_Container::TYPE_PERSONAL, $owner->getId());
                    }
                    break;
            }
        } catch (Tinebase_Exception_NotFound $tenf) {
            if (Tinebase_Core::isLogLevel(Zend_Log::NOTICE)) Tinebase_Core::getLogger()->notice(__METHOD__ . '::' . __LINE__
                . ' Not found: ' . $tenf->getMessage());
        }

        if (! $container && $containerName && (int) $containerName !== 0) {
            // TODO should be fixed! always assure correct path with names .. we seem to have a statpath here
            try {
                $container = Tinebase_Container::getInstance()->getContainerById($containerName);
            } catch (Exception $e) {
                if (Tinebase_Core::isLogLevel(Zend_Log::NOTICE)) Tinebase_Core::getLogger()->notice(__METHOD__ . '::' . __LINE__
                    . ' Could not get container by id: ' . $e->getMessage());
            }
        }
        
        return $container;
    }
    
    /**
     * do path replacements (container name => container id, account name => account id)
     * 
     * @param array $pathParts
     * @return string
     */
    protected function _getStatPath($pathParts = NULL)
    {
        if ($pathParts === NULL) {
            $pathParts = array(
                $this->application->getId(),
                'folders',
                $this->containerType,
            );
            
            if ($this->containerOwner) {
                $pathParts[] = $this->containerOwner;
            }
            
            if ($this->container) {
                $pathParts[] = $this->container->name;
            }
            
            if ($this->realpath) {
                $pathParts += explode('/', $this->realpath);
            }
            $this->flatpath = '/' . implode('/', $pathParts);
        }
        $result = $this->_createStatPathFromParts($pathParts);
        
        if (Tinebase_Core::isLogLevel(Zend_Log::DEBUG)) Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ 
            . ' Path to stat: ' . $result);
        
        return $result;
    }
    
    /**
     * create stat path from path parts
     * 
     * @param array $pathParts
     * @return string
     */
    protected function _createStatPathFromParts($pathParts)
    {
        if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__ . ' ' . print_r($pathParts, TRUE));
        
        if (count($pathParts) > 3) {
            // replace account login name with id
            if ($this->containerOwner) {
                try {
                    $pathParts[3] = Tinebase_User::getInstance()->getUserByPropertyFromSqlBackend('accountLoginName', $this->containerOwner, 'Tinebase_Model_FullUser')->getId();
                } catch (Tinebase_Exception_NotFound $tenf) {
                    // try again with id
                    $accountId = is_object($this->containerOwner) ? $this->containerOwner->getId() : $this->containerOwner;
                    $user = Tinebase_User::getInstance()->getUserByPropertyFromSqlBackend('accountId', $accountId, 'Tinebase_Model_FullUser');
                    $pathParts[3] = $user->getId();
                    $this->containerOwner = $user->accountLoginName;
                }
            }
        
            // replace container name with id
            $containerPartIdx = ($this->containerType === Tinebase_Model_Container::TYPE_SHARED) ? 3 : 4;
            if (isset($pathParts[$containerPartIdx]) && $this->container && $pathParts[$containerPartIdx] === $this->container->name) {
                $pathParts[$containerPartIdx] = $this->container->getId();
            }
        }
        
        $result = '/' . implode('/', $pathParts);
        return $result;
    }
    
    /**
     * get real path
     * 
     * @param array $pathParts
     * @return NULL|string
     */
    protected function _getRealPath($pathParts)
    {
        $result = NULL;
        $firstRealPartIdx = ($this->containerType === Tinebase_Model_Container::TYPE_SHARED) ? 4 : 5;
        if (isset($pathParts[$firstRealPartIdx])) {
            $result = implode('/', array_slice($pathParts, $firstRealPartIdx));
        }
        
        return $result;
    }
    
    /**
     * check if this path has a matching container (toplevel path) 
     * 
     * @return boolean
     */
    public function isToplevelPath()
    {
        return (! $this->getParent()->container instanceof Tinebase_Model_Container);
    }
    
    /**
     * set new container / statpath has to be reset
     * 
     * @param Tinebase_Model_Container $container
     */
    public function setContainer($container)
    {
        $this->container            = $container;
        $this->containerType        = $container->type;
        $ownerAccountId             = Tinebase_Container::getInstance()->getContainerOwner($container);
        if ($ownerAccountId) {
            $this->containerOwner = Tinebase_User::getInstance()->getUserByPropertyFromSqlBackend('accountId', $ownerAccountId, 'Tinebase_Model_FullUser')->accountLoginName;
        } else if ($this->containerType === Tinebase_Model_Container::TYPE_PERSONAL) {
            throw new Tinebase_Exception_InvalidArgument('Personal container needs an owner!');
        } else {
            $this->containerOwner = NULL;
        }
        
        $this->statpath             = $this->_getStatPath();
        $this->streamwrapperpath    = self::STREAMWRAPPERPREFIX . $this->statpath;
    }

    /**
     * validate node/container existance
     * 
     * @throws Tinebase_Exception_NotFound
     */
    public function validateExistance()
    {
        if (! $this->containerType || ! $this->statpath) {
            $this->_parsePath();
        }
        
        if (Tinebase_Core::isLogLevel(Zend_Log::TRACE)) Tinebase_Core::getLogger()->trace(__METHOD__ . '::' . __LINE__
            . ' Validate statpath: ' . $this->statpath);
        
        $pathParts = $this->_getPathParts();
        if (! $this->container) {
            $containerPart = ($this->containerType === Tinebase_Model_Container::TYPE_PERSONAL) ? 5 : 4;
            if (count($pathParts) >= $containerPart) {
                throw new Tinebase_Exception_NotFound('Container not found');
            }
        } else if (! Tinebase_FileSystem::getInstance()->fileExists($this->statpath)) {
             throw new Tinebase_Exception_NotFound('Node not found');
        }
    }
}
