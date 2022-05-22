<?php

namespace RedKuri;

RKUtility::startup();

/*
 *	 A HTTP Request handler
 */
 
class RKRequest
{
	static public function argument($position = 0) {
		global $path;

		if (isset($path[0][$position])) {
			return $path[0][$position];
		}
		return null;
	}

	static public function isSubmitted()
	{
		if (RKRequest::Post('RKFormSubmit') === 'submitted') {
			return true;
		}
		return false;
	}

	static public function Get($var, $default=null)
	{
		if (isset($_GET[$var])) {
			return $_GET[$var];
		} else {
			return $default;
		}
	}

	static public function Post($var, $default=null)
	{
		if (isset($_POST[$var])) {
			return $_POST[$var];
		} else {
			return $default;
		}
	}

	static public function Server($var, $default=null)
	{
		if (isset($_SERVER[$var])) {
			return $_SERVER[$var];
		} else {
			return $default;
		}
	}

	static public function Session($var, $value=null)
	{
		if ($value !== null) {
			$_SESSION[$var] = $value;
		}
		if (isset($_SESSION[$var])) {
			return $_SESSION[$var];
		}
		return null;
	}

	static public function isAjax()
	{
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			return true;
		}
		return false;
	}
}

/*
 *	 A HTTP Response handler
 */
 
class RKResponse {
	public $doctype;
	public $title;
	public $meta;
	public $css;
	public $js;
	
	function __construct() {}

	function beginForm($upload=false)
	{
		if ($upload) {
			$enctype = 'application/x-www-form-urlencoded';
		} else {
			$enctype = 'multipart/form-data';
		}
		
		echo '<form method="post" enctype="'.$enctype.'" id="page_form">';
		echo '<input type="submit" name="RKDefaultButton" style="display:none;" value="submitted"/>';
		echo '<input type="hidden" name="RKFormSubmit" value="submitted"/>';
	}

	function choiceField($name, $options, $values=null, $default='')
	{
		$value = RKRequest::Post($name, $default);
		$form = '<select name="'.$name.'" id="'.$name.'" class="form-control">';
		for ($i=0; $i<count($options); $i++) {
			if (is_array($values)) {
				$form .= '<option value="'.$values[$i].'"';
				if ($values[$i] == $value) {
					$form .= ' selected';
				}
			} else {
				$form .= '<option value="'.$options[$i].'"';
				if ($options[$i] == $value) {
					$form .= ' selected';
				}
			}
			$form .= '>'.$options[$i].'</option>';
		}
		$form .= '</select>';
		return $form;
	}
	
	function nocache() {
		header("Expires: 0");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("cache-control: no-store, no-cache, must-revalidate");
		header("Pragma: no-cache");
	}

	function setTitle($title)
	{
		$this->title = $title;
	}

	function addMeta($meta)
	{
		$this->meta[] = $meta;
	}
	
	function addCSS($css)
	{
		$this->css[] = $css;
	}
	
	function addJS($js)
	{
		$this->js[] = $js;
	}
	
	function header()
	{
		$h  = $this->doctype;
		$h .= '<html lang="en">';
		$h .= '<head>';
		$h .= "<title>{$this->title}</title>";

		if (count($this->meta))
			foreach ($this->meta as $meta) :
				$h .= "<meta $meta>";
			endforeach;
		if (count($this->css))
			foreach ($this->css as $css) :
				$h .= '<link href="'.$css.'" rel="stylesheet">';
			endforeach;
		$h .= '</head>';

		return $h;
	}
	
	function content($content)
	{
		$c = '<body>';
		$c .= $content;
		
		return $c;
	}
	
	function footer()
	{
		$f = $this->footerJS();
		$f .= '</body>';
		$f .= '</html>';
		return $f;
	}

	function footerJS()
	{
		$f = '';
		if (count($this->js))
			foreach ($this->js as $js) :
				$f .= '<script src="'.$js.'" rel="stylesheet"></script>';
			endforeach;
		return $f;
	}
	
	function render()
	{
		echo $this->header();
		echo $this->content();
		echo $this->footer();
	}
}

class RKHTML extends RKResponse
{
	function __construct()
	{
		parent::__construct();
		$this->doctype = "<!DOCTYPE html>";
	}
}

class RKBootstrapPage extends RKHTML
{
	protected $errors;
	
	function __construct()
	{
		parent::__construct();
		$this->errors = array();
		$this->addCSS('/css/bootstrap.min.css');
		$this->addCSS('/css/simple-sidebar.css');
		$this->addCSS('//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');
		$this->addJS('/js/jquery.js');
		$this->addJS('/js/bootstrap.min.js');
		$this->addMeta('charset="utf-8"');
		$this->addMeta('http-equiv="X-UA-Compatible" content="IE=edge"');
		$this->addMeta('name="viewport" content="width=device-width, initial-scale=1"');
		$this->addMeta('name="description" content=""');
		$this->addMeta('name="author" content=""');
	}

	function addError($errorText)
	{
		$this->errors[] = $errorText;
	}
	
	function renderError()
	{
		if ($this->isError()) {
			$error = '<div class="alert alert-warning" role="alert">';
			$error .= '<a class="close" href="#">&times;</a>';
			$error .= '<p><strong>'.implode($this->errors, '<br/>').'</strong></p>';
			$error .= '</div>';

			return $error;
		}
		return '';
	}
	
	function noErrors()
	{
		return count($this->errors) === 0;
	}
	
	function isError()
	{
		return count($this->errors) > 0;
	}
}

class RKFoundationPage extends RKHTML
{
	function __construct()
	{
		parent::__construct();
		die('RKFoundationPage is under development');
	}
}

class memory extends RKBootstrapPage
{
	function __construct()
	{
		parent::__construct();
		$this->title = 'Memory of a Fish';
	}
	
	function content($c=null)
	{
		return parent::content(<<<EOD
	<div id="wrapper">

        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <ul class="sidebar-nav">
                <li class="sidebar-brand">
					<a href="#" class="menu-toggle">
                        <i class="fa fa-cog"></i> Settings
                    </a>
                </li>
				<p><a href="/?rklogout=true">Logout</a></p>
                <li>
					<ul class="list-group">
						<li class="list-group-item">
							<a href="/"><i class="fa fa-home"></i> Home</a>
						</li>
						<li class="list-group-item">
							<a href="/newtask.php"><i class="fa fa-add"></i> Create Task</a>
						</li>
					  <li class="list-group-item">
						<span class="badge">14</span>
						Cras justo odio
					  </li>
					  <li class="list-group-item">
						<span class="badge">14</span>
						Cras justo odio
					  </li>
					  <li class="list-group-item">
						<span class="badge">14</span>
						Cras justo odio
					  </li>
					</ul>
                </li>
            </ul>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <div class="container-fluid">			
				{$this->renderError()}
				{$c}
            </div>
        </div>
        <!-- /#page-content-wrapper -->

    </div>
    <!-- /#wrapper -->
	<!-- Menu Toggle Script -->
    <script>
		$(".menu-toggle").click(function(e) {
			e.preventDefault();
			$("#wrapper").toggleClass("toggled");
		});
    </script>
EOD
		);
	}
}

class loginPage extends RKBootstrapPage
{
	function __construct()
	{
		parent::__construct();
		$this->title = 'Memory of a Fish';
	}
	
	function content($c=null)
	{
		return parent::content(<<<EOD
        <div id="page-content-wrapper">
            <div class="container-fluid">			
				{$c}
            </div>
        </div>
EOD
		);
	}
}

/*
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
*/
