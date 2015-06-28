<?php

namespace PCC\Core;

//Imports
use RWF\Core\RWF;
use RWF\Settings\Settings;
use RWF\Style\StyleEditor;
use RWF\User\User;

/**
 * Kernklasse (initialisiert das SHC)
 *
 * @author     Oliver Kleditzsch
 * @copyright  Copyright (c) 2014, Oliver Kleditzsch
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @since      2.2.0-0
 * @version    2.2.0-0
 */
class PCC extends RWF {

    /**
     * Version
     *
     * @var String
     */
    const VERSION = '2.2.2';

    /**
     * Style
     *
     * @var \RWF\Style\Style
     */
    protected static $style = null;

    public function __construct() {

        //pruefen ob APP installiert ist
        if(!file_exists(PATH_PCC .'app.json')) {

            throw new \Exception('Die App "PCC" ist nicht installiert', 1013);
        }

        //XML Initialisieren
        $this->initXml();

        //Basisklasse initalisieren
        parent::__construct();

        //SHC Initialisieren
        if (ACCESS_METHOD_HTTP) {

            //Template Ordner anmelden
            self::$template->addTemplateDir(PATH_PCC . 'data/templates');
            $this->redirection();
            $this->initStyle();
        }
    }

    /**
     * initialisiert die Einstellungen
     */
    protected function initSettings() {

        parent::initSettings();
        $settings = self::$settings;

        //PCC Einstellungen hinzufuegen
        //Allgemein
        $settings->addSetting('pcc.ui.redirectActive', Settings::TYPE_BOOL, true);
        $settings->addSetting('pcc.ui.redirectPcTo', Settings::TYPE_INT, 1);
        $settings->addSetting('pcc.ui.redirectTabletTo', Settings::TYPE_INT, 3);
        $settings->addSetting('pcc.ui.redirectSmartphoneTo', Settings::TYPE_INT, 3);
        $settings->addSetting('pcc.ui.index.showUsersAtHome', Settings::TYPE_BOOL, true);
        $settings->addSetting('pcc.title', Settings::TYPE_STRING, 'PCC 2.2');
        $settings->addSetting('pcc.defaultStyle', Settings::TYPE_STRING, 'redmond');
        $settings->addSetting('pcc.defaultMobileStyle', Settings::TYPE_STRING, 'default');

        //Fritz!Box Einstellungen
        $settings->addSetting('pcc.fritzBox.showState', Settings::TYPE_BOOL, true);
        $settings->addSetting('pcc.fritzBox.showSmartHomeDevices', Settings::TYPE_BOOL, true);
        $settings->addSetting('pcc.fritzBox.showCallList', Settings::TYPE_BOOL, true);
        $settings->addSetting('pcc.fritzBox.callListMax', Settings::TYPE_INT, 25);
        $settings->addSetting('pcc.fritzBox.callListDays', Settings::TYPE_INT, 999);
        $settings->addSetting('pcc.fritzBox.dslConnected', Settings::TYPE_BOOL, true);
    }

    /**
     * XML Verwaltung initialisieren
     */
    protected function initXml() {


    }

    /**
     * initialisiert den Style
     */
    protected function initStyle() {

        if(defined('RWF_DEVICE') && (RWF_DEVICE == 'smartphone' || RWF_DEVICE == 'tablet')) {

            //Mobilen Style laden
            if (self::$visitor instanceof User && self::$visitor->getMobileStyle() != '') {

                $mobileStyle = self::$visitor->getMobileStyle();
            } else {

                $mobileStyle = self::getSetting('pcc.defaultMobileStyle');
            }
            self::$style = StyleEditor::getInstance()->getMobileStyle($mobileStyle);
        } elseif(defined('RWF_DEVICE') && RWF_DEVICE == 'web') {

            //Webstyle laden
            if (self::$visitor instanceof User && self::$visitor->getWebStyle() != '') {

                $webStyle = self::$visitor->getWebStyle();
            } else {

                $webStyle = self::getSetting('pcc.defaultStyle');
            }
            self::$style = StyleEditor::getInstance()->getWebStyle($webStyle);
        }
    }

    /**
     * prueft ob die Auto Umleitung aktiv ist und leitet bei einer neuen Session den Benutzer automatisch um
     */
    protected function redirection() {

        //Mobil Detect einbinden
        require_once(PATH_RWF_CLASSES . 'external/mobile_detect/Mobile_Detect.php');

        $mobilDetect = new \Mobile_Detect();

        //Umleitung fuer PC/Tablet/Smartphone

        /**
         * Einstellung der Umleitung
         *
         * 1 => auf PC oberflaeche leiten
         * 2 => auf Tablet Oberflache leiten
         * 3 => auf Smartphone oberflaeche leiten
         */
        //Geraet feststellen und Umleiten nach den jeweiligen Einstellungen
        $location = 'index.php?app=' . APP_NAME;
        if ($mobilDetect->isTablet()) {

            define('PCC_DETECTED_DEVICE', 'tablet');

            //Tablet
            if (self::$session->isNewSession() && self::$settings->getValue('pcc.ui.redirectActive')) {
                switch (self::$settings->getValue('pcc.ui.redirectTabletTo')) {

                    case 1:
                        //auf PC Oberflaeche
                        //es muss nicht umgeleitet werden
                        break;
                    case 3:
                        //auf Smartphone Oberflaeche
                        $location .= '&m';
                        break;
                    default :
                        //auf Tablet Oberflaeche
                        $location .= '&t';
                }
            }
        } elseif ($mobilDetect->isMobile()) {

            define('PCC_DETECTED_DEVICE', 'smartphone');

            //Smartphone
            if (self::$session->isNewSession() && self::$settings->getValue('pcc.ui.redirectActive')) {
                switch (self::$settings->getValue('pcc.ui.redirectSmartphoneTo')) {

                    case 1:
                        //auf PC Oberflaeche
                        //es muss nicht umgeleitet werden
                        break;
                    case 2:
                        //Auf Tablet Oberflaeche
                        $location .= '&t';
                        break;
                    default :
                        //auf Smartphone Oberflaeche
                        $location .= '&m';
                }
            }
        } else {

            define('PCC_DETECTED_DEVICE', 'pc');

            //PC und alles andere
            if (self::$session->isNewSession() && self::$settings->getValue('pcc.ui.redirectActive')) {
                switch (self::$settings->getValue('pcc.ui.redirectPcTo')) {

                    case 2:
                        //auf Tablet Oberflaeche
                        $location .= '&t';
                        break;
                    case 3:
                        //Auf Smartphone Oberflaeche
                        $location .= '&m';
                        break;
                    default :
                        //auf PC Oberflaeche
                        //es muss nicht umgeleitet werden
                }
            }
        }

        //Header setzen, senden und beenden
        if (self::$session->isNewSession() && self::$settings->getValue('pcc.ui.redirectActive') && $location != 'index.php?app=' . APP_NAME) {
            self::$response->addLocationHeader($location);
            self::$response->setBody('');
            self::$response->flush();
            $this->finalize();
            exit(0);
        }
    }

    /**
     * gibt den Style zurueck
     *
     * @return \RWF\Style\Style
     */
    public static function getStyle() {

        return self::$style;
    }
}