<?php 

namespace SHC\Switchable\Switchables;

//Imports
use SHC\Switchable\AbstractSwitchable;
use SHC\Command\CommandSheduler;
use SHC\Command\Commands\GpioOutputCommand;

/**
 * Raspberry Pi GPIO
 * 
 * @author     Oliver Kleditzsch
 * @copyright  Copyright (c) 2014, Oliver Kleditzsch
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @since      2.0.0-0
 * @version    2.0.0-0
 */
class RpiGpio extends AbstractSwitchable {
    
    /**
     * ID des Schaltservers
     * 
     * @var Integer 
     */
    protected $switchServer = 0;
    
    /**
     * Pin Nummer
     * 
     * @var Integer 
     */
    protected $pinNumber = 0;
    
    /**
     * setzt den Schaltserver
     * 
     * @param  Integer $switchServer ID des Schaltservers
     * @return \SHC\Switchable\Switchables\RpiGpio
     */
    public function setSwitchServer($switchServer) {
        
        $this->switchServer = $switchServer;
        return $this;
    }
    
    /**
     * gibt die ID des Schaltservers zurueck
     * 
     * @return Integer
     */
    public function getSwitchServer() {
        
        return $this->switchServer;
    }

    /**
     * setzt die Pin Nummer
     * 
     * @param  Integer $pinNumber Pin Nummer
     * @return \SHC\Switchable\Switchables\RpiGpio
     */
    public function setPinNumber($pinNumber) {
        
        $this->pinNumber = $pinNumber;
        return $this;
    }
    
    /**
     * gibt die Pin Nummer zurueck
     * 
     * @return Integer
     */
    public function getPinNumber() {
        
        return $this->pinNumber;
    }
    
    /**
     * schaltet das Objekt ein
     * 
     * @return Boolean
     */
    public function switchOn() {
        
        CommandSheduler::getInstance()->addCommand(new GpioOutputCommand($this->switchServer, $this->pinNumber, GpioOutputCommand::SWITCH_ON));
    }
    
    /**
     * schaltet das Objekt aus
     * 
     * @return Boolean
     */
    public function switchOff() {
        
        CommandSheduler::getInstance()->addCommand(new GpioOutputCommand($this->switchServer, $this->pinNumber, GpioOutputCommand::SWITCH_OFF));
    }

}