<?php

// Make sure no one attempts to run this script "directly"
if (!defined('FORUM')) {
    exit;
}

abstract class FancyStopSpamPlugin
{
    protected $language;
    protected $config;
    protected $db;
    protected $logger;

    public function __construct($language, $config, $db, $logger)
    {
        $this->language = $language;
        $this->config   = $config;
        $this->db       = $db;
        $this->logger   = $logger;
    }

    abstract public function getName();
    abstract public function getVersion();
    abstract public function isEnabled();

    public function renderOptionsBlock(array $forum_page)
    {
        return $forum_page;
    }

    public function renderMainOptionsBlock(array $forum_page)
    {
        return $forum_page;
    }

    protected function renderMainOptionsBlockHelper(array $forum_page, $pluginId)
    {
        $inputName = 'form[fancy_stop_spam_plugin_enabled_' . $pluginId . ']';
        $label = $this->language['Enable plugin ' . $pluginId];
        ?>
                <div class="mf-item">
                    <span class="fld-input">
                        <input type="checkbox"
                               id="fld<?php echo ++$forum_page['fld_count'] ?>"
                               name="<?php echo $inputName ?>"
                               value="1"
                               <?php if ($this->config['o_fancy_stop_spam_plugin_enabled_' . $pluginId] == '1') echo ' checked="checked"'; ?>
                        />
                    </span>
                    <label for="fld<?php echo $forum_page['fld_count'] ?>"><?php echo $label ?></label>
                </div>
        <?php
            return $forum_page;
    }

    public function saveOptions(array $form)
    {
        return $form;
    }

    public function eventPostFormSubmited(array $data) {}
    public function eventEditFormValidation(array $data)
    {
        $requiredElements = array('user', 'message');
        $this->checkEventData($data, 'RegisterFormValidation', $requiredElements);
    }

    public function eventPostFormValidation(array $data)
    {
        $requiredElements = array('user', 'message');
        $this->checkEventData($data, 'RegisterFormValidation', $requiredElements);
    }

    public function eventRegisterFormSubmited(array $data)
    {
        $requiredElements = array('ip');
        $this->checkEventData($data, 'RegisterFormSubmited', $requiredElements);
    }

    public function eventRegisterFormValidation(array $data)
    {
        $requiredElements = array('username', 'email', 'ip');
        $this->checkEventData($data, 'RegisterFormValidation', $requiredElements);
    }

    public function eventUserProfile(array $data)
    {
        $requiredElements = array('user');
        $this->checkEventData($data, 'UserProfile', $requiredElements);
    }

    protected function addValidationError($error)
    {
        if (isset($GLOBALS['errors']) && is_array($GLOBALS['errors'])) {
            $GLOBALS['errors'][] = $error;
        } else {
            message($error);
        }
    }

    public function saveBooleanFormOptions(array $form, $optionsName) {
        $form[$optionsName] = (isset($form[$optionsName]) && $form[$optionsName] == '1')
                            ? '1'
                            : '0';
        return $form;
    }

    protected function renderOptionsBlockHeader(&$forum_page, $optionsBlockName) {
        $forum_page['group_count'] = $forum_page['item_count'] = 0;
        ?>
        <div class="content-head" id="">
            <h2 class="hn">
                <span><?php echo sprintf($this->language['Settings Name'], $optionsBlockName) ?></span>
            </h2>
        </div>
        <fieldset class="frm-group group<?php echo ++$forum_page['group_count'] ?>">
            <legend class="group-legend"><span><?php echo $this->language['Name'] ?></span></legend>
        <?php
    }

    protected function renderOptionsBlockFooter() {
        ?>
        </fieldset>
        <?php
    }

    protected function pluginEnabled($pluginId) {
        return ($this->config['o_fancy_stop_spam_plugin_enabled_' . $pluginId] == '1');
    }

    private function checkEventData(array $data, $eventName, array $requiredElements = array())
    {
        foreach ($requiredElements as $element) {
            if (!isset($data[$element])) {
                error(sprintf(
                    $this->language['Error event bad data'],
                    forum_htmlencode($element),
                    forum_htmlencode('PostFormValidation')
                ));
            }
        }
    }
}