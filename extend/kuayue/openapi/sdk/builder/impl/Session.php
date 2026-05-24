<?php

class Session
{
    /**
     * 设置session
     * @param String $name session name
     * @param Mixed $data session data
     * @param Int $expire 超时时间(秒)
     */
    public function set($name, $data, $expire = 600)
    {
        $session_data           = array();
        $session_data['data']   = $data;
        $session_data['expire'] = time() + $expire;
        $_SESSION[$name]        = $session_data;
    }
    
    /**
     * 读取session
     * @param String $name session name
     * @return Mixed
     */
    public function get($name)
    {
        if (isset($_SESSION[$name])) {
            if ($_SESSION[$name]['expire'] > time()) {
                return $_SESSION[$name]['data'];
            } else {
                $this->clear($name);
            }
        }
        return false;
    }
    
    /**
     * 清除session
     * @param String $name session name
     */
    public function clear($name)
    {
        unset($_SESSION[$name]);
    }
    
}
