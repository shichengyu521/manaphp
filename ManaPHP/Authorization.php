<?php
namespace ManaPHP;

/**
 * Class Authorization
 * @package ManaPHP
 *
 * @property \ManaPHP\Mvc\DispatcherInterface $dispatcher
 * @property \ManaPHP\IdentityInterface       $identity
 */
abstract class Authorization extends Component implements AuthorizationInterface
{
    /**
     * @var array
     */
    protected $_acl;

    /**
     * @param array  $acl
     * @param string $action
     *
     * @return string|null
     */
    public function getActionAllowedRoles($acl, $action)
    {
        if (isset($acl[$action])) {
            $roles = $acl[$action];
            if ($roles[0] === '@') {
                $original_action = substr($roles, 1);
                if (isset($acl[$original_action])) {
                    $roles = $acl[$original_action];
                } else {
                    $roles = null;
                }
            }
        } else {
            $roles = null;
        }

        if ($roles === null && isset($acl['*'])) {
            $roles = $acl['*'];
        }

        return $roles;
    }

    /**
     * @param string $roles
     * @param string $role
     *
     * @return bool
     */
    public function isRoleAllowed($roles, $role)
    {
        if ($roles === 'guest') {
            return true;
        } elseif ($roles === 'user') {
            return $role !== 'guest';
        } elseif ($roles === 'admin') {
            return $role === 'admin';
        } elseif ($roles === $role) {
            return true;
        } elseif ($role === 'guest') {
            return false;
        } elseif ($role === 'admin') {
            return true;
        } else {
            return preg_match("#\b$role\b", $roles) === 1;
        }
    }

    /**
     * Check whether a user is allowed to access a permission
     *
     * @param string $permission
     * @param string $role
     *
     * @return bool
     */
    public function isAllowed($permission, $role = null)
    {
        $controllerName = '';
        $actionName = '';
        if (isset($this->_acl[$controllerName])) {
            $acl = $this->_acl[$controllerName];
        } else {
            /** @var \ManaPHP\Controller $controllerInstance */
            $controllerInstance = new $controllerName;
            $acl = $this->_acl[$controllerName] = $controllerInstance->getAcl();
        }

        if (!$allowedRoles = $this->getActionAllowedRoles($acl, $actionName)) {
            return false;
        }

        $role = $role ?: $this->identity->getRole();

        if (strpos($role, ',') === false) {
            return $this->isRoleAllowed($allowedRoles, $role);
        } else {
            foreach (explode($role, ',') as $r) {
                if ($this->isRoleAllowed($allowedRoles, $r)) {
                    return true;
                }
            }
            return false;
        }
    }
}