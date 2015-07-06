<?php

namespace Model;

use Core\Translator;

/**
 * User Session
 *
 * @package  model
 * @author   Frederic Guillot
 */
class UserSession extends Base
{
    /**
     * Update user session information
     *
     * @access public
     * @param  array  $user  User data
     */
    public function refresh(array $user = array())
    {
        if (empty($user)) {
            $user = $this->user->getById($this->userSession->getId());
        }

        if (isset($user['password'])) {
            unset($user['password']);
        }

        if (isset($user['twofactor_secret'])) {
            unset($user['twofactor_secret']);
        }

        $user['id'] = (int) $user['id'];
        $user['is_admin'] = (bool) $user['is_admin'];
        $user['is_ldap_user'] = (bool) $user['is_ldap_user'];
        $user['twofactor_activated'] = (bool) $user['twofactor_activated'];

        $this->session['user'] = $user;
    }

    /**
     * Return true if the user has validated the 2FA key
     *
     * @access public
     * @return bool
     */
    public function check2FA()
    {
        return isset($this->session['2fa_validated']) && $this->session['2fa_validated'] === true;
    }

    /**
     * Return true if the user has 2FA enabled
     *
     * @access public
     * @return bool
     */
    public function has2FA()
    {
        return isset($this->session['user']['twofactor_activated']) && $this->session['user']['twofactor_activated'] === true;
    }

    /**
     * Return true if the logged user is admin
     *
     * @access public
     * @return bool
     */
    public function isAdmin()
    {
        return isset($this->session['user']['is_admin']) && $this->session['user']['is_admin'] === true;
    }

    /**
     * Get the connected user id
     *
     * @access public
     * @return integer
     */
    public function getId()
    {
        return isset($this->session['user']['id']) ? (int) $this->session['user']['id'] : 0;
    }

    /**
     * Check is the user is connected
     *
     * @access public
     * @return bool
     */
    public function isLogged()
    {
        return ! empty($this->session['user']);
    }

    /**
     * Get project filters from the session
     *
     * @access public
     * @param  integer  $project_id
     * @return string
     */
    public function getFilters($project_id)
    {
        return ! empty($_SESSION['filters'][$project_id]) ? $_SESSION['filters'][$project_id] : 'status:open';
    }

    /**
     * Save project filters in the session
     *
     * @access public
     * @param  integer  $project_id
     * @param  string   $filters
     */
    public function setFilters($project_id, $filters)
    {
        $_SESSION['filters'][$project_id] = $filters;
    }
}
