<?php
/**
 * tipy : Tiny PHP MVC framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package       tipy
 * @copyright     Copyright (c) 2008-2015 Serge Smetana <serge.smetana@gmail.com>
 * @copyright     Copyright (c) 2008-2015 Roman Zhbadynskyi <zhbadynskyi@gmail.com>
 * @link          https://github.com/smetana/tipy
 * @author        Serge Smetana <serge.smetana@gmail.com>
 * @author        Roman Zhbadynskyi <zhbadynskyi@gmail.com>
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

define('CLI_MODE', php_sapi_name() == 'cli');

// Require base classes
require_once('src/TipyErrorHandler.php');
require_once('src/TipyLogger.php');
require_once('src/TipyIOWrapper.php');
require_once('src/TipyConfig.php');
require_once('src/TipyRequest.php');
require_once('src/TipyEnv.php');
require_once('src/TipyCookie.php');
require_once('src/TipyInput.php');
require_once('src/TipyOutput.php');
require_once('src/TipySession.php');
require_once('src/TipyMailer.php');
require_once('src/TipyDAO.php');
require_once('src/TipyInflector.php');
require_once('src/TipyModel.php');
require_once('src/TipyView.php');
require_once('src/TipyFlash.php');
require_once('src/TipyController.php');
require_once('src/TipyApp.php');
if (CLI_MODE) {
    require_once('src/TipyCli.php');
    require_once('src/TipyCliSession.php');
}

// Convert PHP errors to exceptions
set_error_handler('tipyErrorHandler');

/**
 * Main class to run application
 *
 * <code>
 * // public/dispatcher.php
 * require('Tipy.php');
 * Tipy::run();
 * </code>
 *
 * ## Setup
 *
 * The most easiest way to setup new *tipy* project is to clone
 * {@link https://github.com/smetana/tipy-project} repository.
 * It is an empty *tipy* aplication skeleton
 *
 * ## Routing
 *
 * Tipy uses *.htaccess* .htaccess for routing.
 * .htaccess' RewriteRules rewrite all request urls to something like:
 * <code>
 * dispatcher.php?controller=my&method=my_action&... # => MyController::myAction()
 * </code>
 *
 * *All query string parameters are preserved on url rewite*.
 *
 * *controller* parameter represents controller class with:
 *
 * - snake_case converted to CamelCase (first letter in upper case)
 * - plural/singular form is preserved
 * - word "Controller" is added to the end
 * <code>
 * source_code => SourceCodeController
 * blog_posts  => BlogPostsController
 * </code>
 *
 * *action* parameter represents controller's method with:
 *
 * - snake_case converted to camelCase (first letter in lower case)
 * - plural/singular form is preserved
 *
 * <code>
 * open_source => openSource()
 * show_posts  => showPosts()
 * </code>
 *
 * ## Predefined Routes
 *
 * {@link https://github.com/smetana/tipy-project tipy-project} has a set of predefined rules so you don't need to
 * rewrite urls to dispatcher.php. It is enough to rewrite urls to one of the
 * following form:
 * <code>
 * /:controller              # /source_code               => SourceCodeController::index();
 * /:controller/:action      # /source_code/open_source   => SourceCodeController::openSource();
 * /:controller/:action/:id  # /source_code/line_number/3 => SourceCodeController::lineNumber($id = 3);
 * </code>
 */
class Tipy {

    public static function run() {
        CLI_MODE && die("TipyApp cannot be run in CLI mode\n");
        ob_start();
        $app = TipyApp::getInstance();
        $app->run();
        ob_end_flush();
    }
}
