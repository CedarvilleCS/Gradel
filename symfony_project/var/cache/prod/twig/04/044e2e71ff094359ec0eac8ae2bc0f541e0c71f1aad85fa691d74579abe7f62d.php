<?php

/* :default:index.html.twig */
class __TwigTemplate_5da4dd3a4201d8b26389485bafe4ad341040066b4b28157931b9616cfd2cc256 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("base.html.twig", ":default:index.html.twig", 1);
        $this->blocks = array(
            'javascripts' => array($this, 'block_javascripts'),
            'body' => array($this, 'block_body'),
            'stylesheets' => array($this, 'block_stylesheets'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_javascripts($context, array $blocks = array())
    {
        // line 4
        echo "\t";
        $this->displayParentBlock("javascripts", $context, $blocks);
        echo "
\t<script src=\"";
        // line 5
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\AssetExtension')->getAssetUrl("\tjs/login.js"), "html", null, true);
        echo "\"></script>
";
    }

    // line 8
    public function block_body($context, array $blocks = array())
    {
<<<<<<< HEAD
        echo " 
\t<div id = \"wrapper\">
\t\t<div id=\"container\">
\t\t\t<div id=\"welcome\">
\t\t\t\t<form>
\t\t\t\t\t<h1>Hello! Welcome to Gradel1!</h1>
\t\t\t\t\t<input style=\"text\">username</input>
\t\t\t\t\t<input style=\"password\">password</input>
\t\t\t\t\t<button>login</button>
\t\t\t\t</form>
\t\t\t</div>
\t\t</div>
\t</div>
=======
        // line 9
        echo "
<div class=\"login-page\">
  <div class=\"form\">
    <form class=\"register-form\">
      <input type=\"text\" placeholder=\"name\"/>
      <input type=\"password\" placeholder=\"password\"/>
      <input type=\"text\" placeholder=\"email address\"/>
      <button href=\"/account\">create</button>
      <p class=\"message\">Already registered? <a href=\"#\">Sign In</a></p>
    </form>
    <form class=\"login-form\">
      <input type=\"text\" placeholder=\"username\"/>
      <input type=\"password\" placeholder=\"password\"/>
      <button>login</button>
      <p class=\"message\">Not registered? <a href=\"#\">Create an account</a></p>
    </form>
  </div>
</div>
>>>>>>> 55788633d11bf2a3758bcedca33354d3d4c6d9d9
";
    }

    // line 29
    public function block_stylesheets($context, array $blocks = array())
    {
        // line 30
        echo "<style>
  @import url(https://fonts.googleapis.com/css?family=Roboto:300);

  .login-page {
  width: 360px;
  padding: 8% 0 0;
  margin: auto;
  }
  .form {
  position: relative;
  z-index: 1;
  background: #FFFFFF;
  max-width: 360px;
  margin: 0 auto 100px;
  padding: 45px;
  text-align: center;
  box-shadow: 0 0 20px 0 rgba(0, 0, 0, 0.2), 0 5px 5px 0 rgba(0, 0, 0, 0.24);
  }
  .form input {
  font-family: \"Roboto\", sans-serif;
  outline: 0;
  background: #f2f2f2;
  width: 100%;
  border: 0;
  margin: 0 0 15px;
  padding: 15px;
  box-sizing: border-box;
  font-size: 14px;
  }
  .form button {
  font-family: \"Roboto\", sans-serif;
  text-transform: uppercase;
  outline: 0;
  background: #4CAF50;
  width: 100%;
  border: 0;
  padding: 15px;
  color: #FFFFFF;
  font-size: 14px;
  -webkit-transition: all 0.3 ease;
  transition: all 0.3 ease;
  cursor: pointer;
  }
  .form button:hover,.form button:active,.form button:focus {
  background: #43A047;
  }
  .form .message {
  margin: 15px 0 0;
  color: #b3b3b3;
  font-size: 12px;
  }
  .form .message a {
  color: #4CAF50;
  text-decoration: none;
  }
  .form .register-form {
  display: none;
  }
  .container {
  position: relative;
  z-index: 1;
  max-width: 300px;
  margin: 0 auto;
  }
  .container:before, .container:after {
  content: \"\";
  display: block;
  clear: both;
  }
  .container .info {
  margin: 50px auto;
  text-align: center;
  }
  .container .info h1 {
  margin: 0 0 15px;
  padding: 0;
  font-size: 36px;
  font-weight: 300;
  color: #1a1a1a;
  }
  .container .info span {
  color: #4d4d4d;
  font-size: 12px;
  }
  .container .info span a {
  color: #000000;
  text-decoration: none;
  }
  .container .info span .fa {
  color: #EF3B3A;
  }
  body {
  background: #76b852; /* fallback for old browsers */
  background: -webkit-linear-gradient(right, #76b852, #8DC26F);
  background: -moz-linear-gradient(right, #76b852, #8DC26F);
  background: -o-linear-gradient(right, #76b852, #8DC26F);
  background: linear-gradient(to left, #76b852, #8DC26F);
  font-family: \"Roboto\", sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  }
</style>
";
    }

    public function getTemplateName()
    {
        return ":default:index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  72 => 30,  69 => 29,  47 => 9,  44 => 8,  38 => 5,  33 => 4,  30 => 3,  11 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", ":default:index.html.twig", "/var/www/gradel_dev/wolf/gradel/symfony_project/app/Resources/views/default/index.html.twig");
    }
}
