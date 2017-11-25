<?php

namespace EventEspresso\core\services\bootstrap;

use EE_Dependency_Map;
use EE_Request;
use EventEspresso\core\services\loaders\LoaderInterface;
use EventEspresso\core\services\request\LegacyRequestInterface;
use EventEspresso\core\services\request\Request;
use EventEspresso\core\services\request\RequestInterface;
use EventEspresso\core\services\request\Response;
use EventEspresso\core\services\request\ResponseInterface;



defined('EVENT_ESPRESSO_VERSION') || exit;



/**
 * Class BootstrapRequestResponseObjects
 * Sets up the Request and Response objects
 * as well as backwards compatibility for the Legacy EE_Request object
 *
 * @package EventEspresso\core\services\bootstrap
 * @author  Brent Christensen
 * @since   $VID:$
 */
class BootstrapRequestResponseObjects
{

    /**
     * @type LegacyRequestInterface $legacy_request
     */
    protected $legacy_request;

    /**
     * @type LoaderInterface $loader
     */
    protected $loader;

    /**
     * @var RequestInterface $request
     */
    protected $request;

    /**
     * @var ResponseInterface $response
     */
    protected $response;


    /**
     * BootstrapRequestResponseObjects constructor.
     *
     * @param LoaderInterface $loader
     */
    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }


    public function buildRequestResponse()
    {
        // load our Request and Response objects
        $this->request  = new Request($_GET, $_POST, $_COOKIE, $_SERVER);
        $this->response = new Response();
    }


    public function shareRequestResponse()
    {
        $this->loader->share('EventEspresso\core\services\request\Request', $this->request);
        $this->loader->share('EventEspresso\core\services\request\Response', $this->response);
        EE_Dependency_Map::instance()->setRequest($this->request);
        EE_Dependency_Map::instance()->setResponse($this->response);
    }



    public function setupLegacyRequest()
    {
        espresso_load_required(
            'EE_Request',
            EE_CORE . 'request_stack' . DS . 'EE_Request.core.php'
        );
        $this->legacy_request = new EE_Request($_GET, $_POST, $_COOKIE, $_SERVER);
        $this->legacy_request->setRequest($this->request);
        $this->legacy_request->admin = $this->request->isAdmin();
        $this->legacy_request->ajax = $this->request->isAjax();
        $this->legacy_request->front_ajax = $this->request->isFrontAjax();
        EE_Dependency_Map::instance()->setLegacyRequest($this->legacy_request);
        $this->loader->share('EE_Request', $this->legacy_request);
        $this->loader->share('EventEspresso\core\services\request\LegacyRequestInterface', $this->legacy_request);
    }

}
// Location: BootstrapRequestResponseObjects.php