<?php namespace Cms\Classes;

use App;
use Illuminate\Routing\Controller as ControllerBase;
use Closure;

/**
 * The CMS controller class.
 * The base controller services front end pages.
 *
 * @package october\cms
 * @author Alexey Bobkov, Samuel Georges
 */
class CmsController extends ControllerBase
{
    use \October\Rain\Extension\ExtendableTrait;

    /**
     * @var array Behaviors implemented by this controller.
     */
    public $implement;

    /**
     * Instantiate a new CmsController instance.
     */
    public function __construct()
    {
        $this->extendableConstruct();
        self::$action = $action = isset($params[2]) ? $this->parseAction($params[2]) : 'index';
    }

    /**
     * Extend this object properties upon construction.
     */
    public static function extend(Closure $callback)
    {
        self::extendableExtendCallback($callback);
    }

    /**
     * Finds and serves the request using the primary controller.
     * @param string $url Specifies the requested page URL.
     * If the parameter is omitted, the current URL used.
     * @return string Returns the processed page content.
     */
    public function run($url = '/')
    {
        return App::make('Cms\Classes\Controller')->run($url);
    }

    /**
     * Checks the request data / headers for a valid CSRF token.
     * Returns false if a valid token is not found. Override this
     * method to disable the check.
     * @return bool
     */
    protected function verifyCsrfToken()
    {
        if (!Config::get('cms.enableCsrfProtection')) {
            return true;
        }

        if (in_array(Request::method(), ['HEAD', 'GET', 'OPTIONS'])) {
            return true;
        }

        $token = Request::input('_token') ?: Request::header('X-CSRF-TOKEN');

        return \Symfony\Component\Security\Core\Util\StringUtils::equals(
            Session::getToken(),
            $token
        );
    }
}
