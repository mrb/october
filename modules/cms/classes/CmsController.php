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

        if (strpos($handler, '::')) {
            list($widgetName, $handlerName) = explode('::', $handler);

            /*
             * Execute the page action so widgets are initialized
             */
            $this->pageAction();

            if ($this->fatalError) {
                throw new SystemException($this->fatalError);
            }

            if (!isset($this->widget->{$widgetName})) {
                throw new SystemException(Lang::get('backend::lang.widget.not_bound', ['name'=>$widgetName]));
            }

            if (($widget = $this->widget->{$widgetName}) && method_exists($widget, $handlerName)) {
                $result = call_user_func_array([$widget, $handlerName], $this->params);
                return ($result) ?: true;
            }
        }
        else {
            /*
             * Process page specific handler (index_onSomething)
             */
            $pageHandler = $this->action . '_' . $handler;

            if ($this->methodExists($pageHandler)) {
                $result = call_user_func_array([$this, $pageHandler], $this->params);
                return ($result) ?: true;
            }

            /*
             * Process page global handler (onSomething)
             */
            if ($this->methodExists($handler)) {
                $result = call_user_func_array([$this, $handler], $this->params);
                return ($result) ?: true;
            }

            /*
             * Cycle each widget to locate a usable handler (widget::onSomething)
             */
            $this->suppressView = true;
            $this->execPageAction($this->action, $this->params);

            foreach ((array) $this->widget as $widget) {
                if (method_exists($widget, $handler)) {
                    $result = call_user_func_array([$widget, $handler], $this->params);
                    return ($result) ?: true;
                }
            }
        }
    }
}
