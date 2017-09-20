<?php

/* :default:old.index.html.twig */
class __TwigTemplate_36d59754419c5936cb74e2539d00ca493939e62e51c2d396a24ff43635ff1e0b extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("base.html.twig", ":default:old.index.html.twig", 1);
        $this->blocks = array(
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
    public function block_body($context, array $blocks = array())
    {
        // line 4
        echo "    <div id=\"wrapper\">
        <div id=\"container\">
            <div id=\"welcome\">
                <h1> the Gradel Demo!</h1>
            </div>

            <div id=\"status\">
\t\t\t
\t\t\t\t<hr>
\t\t\t
\t\t\t\t<p>
\t\t\t\t<h2>Here are all the users:</h2>\t\t\t
\t\t\t\t<ul>
\t\t\t\t";
        // line 17
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["users"]) ? $context["users"] : null));
        foreach ($context['_seq'] as $context["_key"] => $context["user"]) {
            // line 18
            echo "\t\t\t\t\t<h4>";
            echo twig_escape_filter($this->env, $this->getAttribute($context["user"], "first_name", array()), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, $this->getAttribute($context["user"], "last_name", array()), "html", null, true);
            echo " - ";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["user"], "access_level", array()), "role_name", array()), "html", null, true);
            echo "</h4>\t\t\t\t
\t\t\t\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['user'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 19
        echo "\t\t\t\t
\t\t\t\t</ul>
\t\t\t\t</p>
\t\t\t\t
\t\t\t\t<hr>
\t\t\t\t
\t\t\t\t<p>
\t\t\t\t<h2>Here are all the courses:</h2>\t\t\t
\t\t\t\t";
        // line 27
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["courses"]) ? $context["courses"] : null));
        foreach ($context['_seq'] as $context["_key"] => $context["course"]) {
            // line 28
            echo "\t\t\t\t\t<h4>";
            echo twig_escape_filter($this->env, $this->getAttribute($context["course"], "code", array()), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, $this->getAttribute($context["course"], "name", array()), "html", null, true);
            echo "</h4>
\t\t\t\t\t<ul>
\t\t\t\t\t";
            // line 30
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute($context["course"], "sections", array()));
            foreach ($context['_seq'] as $context["_key"] => $context["section"]) {
                // line 31
                echo "\t\t\t\t\t\t<li> ";
                echo twig_escape_filter($this->env, $this->getAttribute($context["section"], "name", array()), "html", null, true);
                echo " (";
                if (($this->getAttribute($context["course"], "is_contest", array()) == false)) {
                    echo twig_escape_filter($this->env, $this->getAttribute($context["section"], "semester", array()), "html", null, true);
                    echo " ";
                }
                echo twig_escape_filter($this->env, $this->getAttribute($context["section"], "year", array()), "html", null, true);
                echo ") - <i>";
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["section"], "owner", array()), "first_name", array()), "html", null, true);
                echo " ";
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($context["section"], "owner", array()), "last_name", array()), "html", null, true);
                echo "</i></li>
\t\t\t\t\t";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['section'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 33
            echo "\t\t\t\t\t</ul>
\t\t\t\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['course'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 34
        echo "\t\t\t\t
\t\t\t\t</p>

\t\t\t\t<hr>
\t\t\t\t
            </div>

        </div>
    </div>
";
    }

    // line 45
    public function block_stylesheets($context, array $blocks = array())
    {
        // line 46
        echo "<style>
    body { background: #F5F5F5; font: 18px/1.5 sans-serif; }
    h1, h2 { line-height: 1.2; margin: 0 0 .5em; }
    h1 { font-size: 36px; }
    h2 { font-size: 21px; margin-bottom: 1em; }
    p { margin: 0 0 1em 0; }
    a { color: #0000F0; }
    a:hover { text-decoration: none; }
    code { background: #F5F5F5; max-width: 100px; padding: 2px 6px; word-wrap: break-word; }
    #wrapper { background: #FFF; margin: 1em auto; max-width: 800px; width: 95%; }
    #container { padding: 2em; 
    #welcome, #status { margin-bottom: 2em; }
    #welcome h1 span { display: block; font-size: 75%; }
    #icon-status, #icon-book { float: left; height: 64px; margin-right: 1em; margin-top: -4px; width: 64px; }
    #icon-book { display: none; }

    @media (min-width: 768px) {
        #wrapper { width: 80%; margin: 2em auto; }
        #icon-book { display: inline-block; }
        #status a, #next a { display: block; }

        @-webkit-keyframes fade-in { 0% { opacity: 0; } 100% { opacity: 1; } }
        @keyframes fade-in { 0% { opacity: 0; } 100% { opacity: 1; } }
        .sf-toolbar { opacity: 0; -webkit-animation: fade-in 1s .2s forwards; animation: fade-in 1s .2s forwards;}
    }
</style>
";
    }

    public function getTemplateName()
    {
        return ":default:old.index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  132 => 46,  129 => 45,  116 => 34,  109 => 33,  90 => 31,  86 => 30,  78 => 28,  74 => 27,  64 => 19,  51 => 18,  47 => 17,  32 => 4,  29 => 3,  11 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", ":default:old.index.html.twig", "/var/www/gradel_dev/brauns/Gradel/symfony_project/app/Resources/views/default/old.index.html.twig");
    }
}
