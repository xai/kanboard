<?php

namespace Controller;

use Pimple\Container;
use Core\Security;
use Core\Request;
use Core\Response;
use Core\Template;
use Core\Session;
use Model\LastLogin;
use Symfony\Component\EventDispatcher\Event;

/**
 * Base controller
 *
 * @package  controller
 * @author   Frederic Guillot
 */
abstract class Base extends \Core\Base
{
    /**
     * Request instance
     *
     * @accesss protected
     * @var \Core\Request
     */
    protected $request;

    /**
     * Response instance
     *
     * @accesss protected
     * @var \Core\Response
     */
    protected $response;

    /**
     * Constructor
     *
     * @access public
     * @param  \Pimple\Container   $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->request = new Request;
        $this->response = new Response;

        if (DEBUG) {
            $this->container['logger']->debug('START_REQUEST='.$_SERVER['REQUEST_URI']);
        }
    }

    /**
     * Destructor
     *
     * @access public
     */
    public function __destruct()
    {
        if (DEBUG) {

            foreach ($this->container['db']->getLogMessages() as $message) {
                $this->container['logger']->debug($message);
            }

            $this->container['logger']->debug('SQL_QUERIES={nb}', array('nb' => $this->container['db']->nbQueries));
            $this->container['logger']->debug('RENDERING={time}', array('time' => microtime(true) - @$_SERVER['REQUEST_TIME_FLOAT']));
            $this->container['logger']->debug('END_REQUEST='.$_SERVER['REQUEST_URI']);
        }
    }

    /**
     * Send HTTP headers
     *
     * @access private
     */
    private function sendHeaders($action)
    {
        // HTTP secure headers
        $this->response->csp(array('style-src' => "'self' 'unsafe-inline'", 'img-src' => '*'));
        $this->response->nosniff();
        $this->response->xss();

        // Allow the public board iframe inclusion
        if (ENABLE_XFRAME && $action !== 'readonly') {
            $this->response->xframe();
        }

        if (ENABLE_HSTS) {
            $this->response->hsts();
        }
    }

    /**
     * Method executed before each action
     *
     * @access public
     */
    public function beforeAction($controller, $action)
    {
        // Start the session
        $this->session->open(BASE_URL_DIRECTORY);
        $this->sendHeaders($action);
        $this->container['dispatcher']->dispatch('session.bootstrap', new Event);

        if (! $this->acl->isPublicAction($controller, $action)) {
            $this->handleAuthentication();
            $this->handle2FA($controller, $action);
            $this->handleAuthorization($controller, $action);

            $this->session['has_subtask_inprogress'] = $this->subtask->hasSubtaskInProgress($this->userSession->getId());
        }
    }

    /**
     * Check authentication
     *
     * @access public
     */
    public function handleAuthentication()
    {
        if (! $this->authentication->isAuthenticated()) {

            if ($this->request->isAjax()) {
                $this->response->text('Not Authorized', 401);
            }

            $this->response->redirect($this->helper->url->to('auth', 'login', array('redirect_query' => urlencode($this->request->getQueryString()))));
        }
    }

    /**
     * Check 2FA
     *
     * @access public
     */
    public function handle2FA($controller, $action)
    {
        $ignore = ($controller === 'twofactor' && in_array($action, array('code', 'check'))) || ($controller === 'auth' && $action === 'logout');

        if ($ignore === false && $this->userSession->has2FA() && ! $this->userSession->check2FA()) {

            if ($this->request->isAjax()) {
                $this->response->text('Not Authorized', 401);
            }

            $this->response->redirect($this->helper->url->to('twofactor', 'code'));
        }
    }

    /**
     * Check page access and authorization
     *
     * @access public
     */
    public function handleAuthorization($controller, $action)
    {
        $project_id = $this->request->getIntegerParam('project_id');
        $task_id = $this->request->getIntegerParam('task_id');

        // Allow urls without "project_id"
        if ($task_id > 0 && $project_id === 0) {
            $project_id = $this->taskFinder->getProjectId($task_id);
        }

        if (! $this->acl->isAllowed($controller, $action, $project_id)) {
            $this->forbidden();
        }
    }

    /**
     * Application not found page (404 error)
     *
     * @access public
     * @param  boolean   $no_layout   Display the layout or not
     */
    public function notfound($no_layout = false)
    {
        $this->response->html($this->template->layout('app/notfound', array(
            'title' => t('Page not found'),
            'no_layout' => $no_layout,
        )));
    }

    /**
     * Application forbidden page
     *
     * @access public
     * @param  boolean   $no_layout   Display the layout or not
     */
    public function forbidden($no_layout = false)
    {
        $this->response->html($this->template->layout('app/forbidden', array(
            'title' => t('Access Forbidden'),
            'no_layout' => $no_layout,
        )));
    }

    /**
     * Check if the CSRF token from the URL is correct
     *
     * @access protected
     */
    protected function checkCSRFParam()
    {
        if (! Security::validateCSRFToken($this->request->getStringParam('csrf_token'))) {
            $this->forbidden();
        }
    }

    /**
     * Check webhook token
     *
     * @access protected
     */
    protected function checkWebhookToken()
    {
        if ($this->config->get('webhook_token') !== $this->request->getStringParam('token')) {
            $this->response->text('Not Authorized', 401);
        }
    }

    /**
     * Redirection when there is no project in the database
     *
     * @access protected
     */
    protected function redirectNoProject()
    {
        $this->session->flash(t('There is no active project, the first step is to create a new project.'));
        $this->response->redirect('?controller=project&action=create');
    }

    /**
     * Common layout for task views
     *
     * @access protected
     * @param  string $template Template name
     * @param  array $params Template parameters
     * @return string
     */
    protected function taskLayout($template, array $params)
    {
        $content = $this->template->render($template, $params);
        $params['task_content_for_layout'] = $content;
        $params['title'] = $params['task']['project_name'].' &gt; '.$params['task']['title'];
        $params['board_selector'] = $this->projectPermission->getAllowedProjects($this->userSession->getId());

        return $this->template->layout('task/layout', $params);
    }

    /**
     * Common layout for project views
     *
     * @access protected
     * @param  string    $template   Template name
     * @param  array     $params     Template parameters
     * @return string
     */
    protected function projectLayout($template, array $params, $sidebar_template = 'project/sidebar')
    {
        $content = $this->template->render($template, $params);
        $params['project_content_for_layout'] = $content;
        $params['title'] = $params['project']['name'] === $params['title'] ? $params['title'] : $params['project']['name'].' &gt; '.$params['title'];
        $params['board_selector'] = $this->projectPermission->getAllowedProjects($this->userSession->getId());
        $params['sidebar_template'] = $sidebar_template;

        return $this->template->layout('project/layout', $params);
    }

    /**
     * Common method to get a task for task views
     *
     * @access protected
     * @return array
     */
    protected function getTask()
    {
        $task = $this->taskFinder->getDetails($this->request->getIntegerParam('task_id'));

        if (empty($task)) {
            $this->notfound();
        }

        return $task;
    }

    /**
     * Common method to get a project
     *
     * @access protected
     * @param  integer      $project_id    Default project id
     * @return array
     */
    protected function getProject($project_id = 0)
    {
        $project_id = $this->request->getIntegerParam('project_id', $project_id);
        $project = $this->project->getById($project_id);

        if (empty($project)) {
            $this->session->flashError(t('Project not found.'));
            $this->response->redirect('?controller=project');
        }

        return $project;
    }

    /**
     * Common method to get the user
     *
     * @access protected
     * @return array
     */
    protected function getUser()
    {
        $user = $this->user->getById($this->request->getIntegerParam('user_id', $this->userSession->getId()));

        if (empty($user)) {
            $this->notfound();
        }

        if (! $this->userSession->isAdmin() && $this->userSession->getId() != $user['id']) {
            $this->forbidden();
        }

        return $user;
    }

    /**
     * Common method to get project filters
     *
     * @access protected
     */
    protected function getProjectFilters($controller, $action)
    {
        $project = $this->getProject();
        $search = $this->request->getStringParam('search', $this->userSession->getFilters($project['id']));
        $board_selector = $this->projectPermission->getAllowedProjects($this->userSession->getId());
        unset($board_selector[$project['id']]);

        $filters = array(
            'controller' => $controller,
            'action' => $action,
            'project_id' => $project['id'],
            'search' => $search,
        );

        $this->userSession->setFilters($project['id'], $search);

        return array(
            'project' => $project,
            'board_selector' => $board_selector,
            'filters' => $filters,
            'title' => $project['name'],
        );
    }
}
