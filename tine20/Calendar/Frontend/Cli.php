<?php
/**
 * Tine 2.0
 * @package     Calendar
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Cornelius Weiss <c.weiss@metaways.de>
 * @copyright   Copyright (c) 2009-2013 Metaways Infosystems GmbH (http://www.metaways.de)
 */

/**
 * Cli frontend for Calendar
 *
 * This class handles cli requests for the Calendar
 *
 * @package     Calendar
 */
class Calendar_Frontend_Cli extends Tinebase_Frontend_Cli_Abstract
{
    /**
     * the internal name of the application
     * 
     * @var string
     */
    protected $_applicationName = 'Calendar';
    
    /**
     * help array with function names and param descriptions
     * 
     * @return void
     */
    protected $_help = array(
        'importegw14' => array(
            'description'   => 'imports calendars/events from egw 1.4',
            'params'        => array(
                'host'     => 'dbhost',
                'username' => 'username',
                'password' => 'password',
                'dbname'   => 'dbname'
            )
        ),
    );
    
    /**
     * return anonymous methods
     * 
     * @return array
     */
    public static function getAnonymousMethods()
    {
        return array('Calendar.repairDanglingDisplaycontainerEvents');
    }
    
    /**
     * import events
     *
     * @param Zend_Console_Getopt $_opts
     */
    public function import($_opts)
    {
        parent::_import($_opts);
    }
    
    /**
     * delete duplicate events
     * 
     * @see 0008182: event with lots of exceptions breaks calendar sync
     * 
     * @todo allow user to set params
     */
    public function deleteDuplicateEvents()
    {
        $writer = new Zend_Log_Writer_Stream('php://output');
        $writer->addFilter(new Zend_Log_Filter_Priority(6));
        Tinebase_Core::getLogger()->addWriter($writer);
        
        $be = new Calendar_Backend_Sql();
        $filter = new Calendar_Model_EventFilter(array(array(
            'field'    => 'dtstart',
            'operator' => 'after',
            'value'    => Tinebase_DateTime::now(),
        ), array(
            'field'    => 'organizer',
            'operator' => 'equals',
            'value'    => 'contactid', // TODO: set correct contact_id or use container_id filter
        )));
        $dryrun = TRUE;
        $be->deleteDuplicateEvents($filter, $dryrun);
    }
    
    /**
     * repair dangling attendee records (no displaycontainer_id)
     * 
     * @see https://forge.tine20.org/mantisbt/view.php?id=8172
     */
    public function repairDanglingDisplaycontainerEvents()
    {
        $writer = new Zend_Log_Writer_Stream('php://output');
        $writer->addFilter(new Zend_Log_Filter_Priority(5));
        Tinebase_Core::getLogger()->addWriter($writer);
        
        $be = new Calendar_Backend_Sql();
        $be->repairDanglingDisplaycontainerEvents();
    }
}
