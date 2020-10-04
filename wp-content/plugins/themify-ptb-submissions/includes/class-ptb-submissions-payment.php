<?php

class PTB_Submission_Payment {

    private static $item = false;
    private static $classname = false;
    protected $plugin_name;
    protected $version;

    public function __construct($classname, $pluginname, $version) {
        if ($classname) {
            self::$classname = 'PTB_Submission_' . $classname;
            $this->plugin_name = $pluginname;
            $this->version = $version;
        }
    }

    public function get() {
        if (is_subclass_of(self::$classname, __CLASS__)) {
            try {
                self::$item = new self::$classname(FALSE, $this->plugin_name, $this->version);
                self::$item->plugin_name = $this->plugin_name;
                self::$item->version = $this->version;
            } catch (Exception $e) {
                if (!class_exists(self::$classname)) {
                    return sprintf(__("Class %s doesn't exists", 'ptb-submission'), self::$classname);
                } else {
                    return $e->getMessage();
                }
            }
        } else {
            return sprintf(__("Class %s must be child of class PTB_Submission_Payment", 'ptb-submission'), self::$classname);
        }
        return self::$item;
    }

}
