<?php

namespace Cravler\ChatBundle\Storage;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class UserStorage
{
    /**
     * @var int
     */
    private $uniqid = 0;

    /**
     * @var array
     */
    private $users = array();

    /**
     * @param string $prefix
     * @return string
     */
    public function uniqid($prefix = '')
    {
        return $prefix . $this->uniqid++;
    }

    /**
     * @return array
     */
    public function getNames(array $except = array())
    {
        $resp = array();
        foreach ($this->users as $user) {
            if (!in_array($user, $except)) {
                $resp[] = $user;
            }
        }
        return $resp;
    }

    /**
     * @param $id
     * @param $name
     */
    public function add($id, $name)
    {
        $this->users[$id] = $name;
    }

    /**
     * @param $id
     */
    public function remove($id)
    {
        if(isset($this->users[$id])) {
            unset($this->users[$id]);
        }
    }
    
    /**
     * @param $name
     * @return null|string
     */
    public function getId($name)
    {
        if(($id = array_search($name, $this->users)) !== false) {
            return $id;
        }
        
        return null;
    }

    /**
     * @param $id
     * @return null|string
     */
    public function getName($id)
    {
        if (isset($this->users[$id])) {
            return $this->users[$id];
        }

        return null;
    }

    /**
     * @param $name
     * @return bool
     */
    public function exists($name)
    {
        if(in_array($name, $this->users)) {
            return true;
        }

        return false;
    }
}
