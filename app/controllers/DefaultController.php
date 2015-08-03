<?
// ==================================================================
// Default controller class
//
// ==================================================================


class DefaultController extends TipyController {

    protected $USER = null;

    // --------------------------------------------------------------
    // Constructor
    // --------------------------------------------------------------
    public function __construct() {
        parent::__construct();

    }


    // --------------------------------------------------------------
    // Just needed to override to save method name
    // --------------------------------------------------------------
    public function execute($method) {
        $this->out->set('methodName', $method);
        $this->executeBefore();
        $this->$method();
    }


    // --------------------------------------------------------------
    // Default action to execute before any controller method    
    // --------------------------------------------------------------
    public function executeBefore() {
        $this->out('description', "description");
        $this->out('keywords', "keywords");
    }

    // --------------------------------------------------------------
    // Default actions to execute before view rendering
    // --------------------------------------------------------------
    public function executeAfter() {
        $this->out->set('USER', $this->USER);
        $this->out->set('flashMessage', $this->flash->get());
    }

}

?>
