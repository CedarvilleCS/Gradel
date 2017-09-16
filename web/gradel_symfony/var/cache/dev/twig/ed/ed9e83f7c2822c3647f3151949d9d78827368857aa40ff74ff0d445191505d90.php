<?php

/* TwigBundle:Exception:error.js.twig */
class __TwigTemplate_5381916ee7abedc64c82ee11e0815981c0b1830acc9d2d3bd6d3fb539489798a extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_73a1773c7a98ee9eec47b6476dc352a09bc686252cdd0e87a5c509e14a8dbf4f = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_73a1773c7a98ee9eec47b6476dc352a09bc686252cdd0e87a5c509e14a8dbf4f->enter($__internal_73a1773c7a98ee9eec47b6476dc352a09bc686252cdd0e87a5c509e14a8dbf4f_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:error.js.twig"));

        $__internal_1ad75166ac871a12dd48d02ba8945d3e828f74f0d7e61dbc9d7d887f2aa0e1bd = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_1ad75166ac871a12dd48d02ba8945d3e828f74f0d7e61dbc9d7d887f2aa0e1bd->enter($__internal_1ad75166ac871a12dd48d02ba8945d3e828f74f0d7e61dbc9d7d887f2aa0e1bd_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:error.js.twig"));

        // line 1
        echo "/*
";
        // line 2
        echo twig_escape_filter($this->env, (isset($context["status_code"]) ? $context["status_code"] : $this->getContext($context, "status_code")), "js", null, true);
        echo " ";
        echo twig_escape_filter($this->env, (isset($context["status_text"]) ? $context["status_text"] : $this->getContext($context, "status_text")), "js", null, true);
        echo "

*/
";
        
        $__internal_73a1773c7a98ee9eec47b6476dc352a09bc686252cdd0e87a5c509e14a8dbf4f->leave($__internal_73a1773c7a98ee9eec47b6476dc352a09bc686252cdd0e87a5c509e14a8dbf4f_prof);

        
        $__internal_1ad75166ac871a12dd48d02ba8945d3e828f74f0d7e61dbc9d7d887f2aa0e1bd->leave($__internal_1ad75166ac871a12dd48d02ba8945d3e828f74f0d7e61dbc9d7d887f2aa0e1bd_prof);

    }

    public function getTemplateName()
    {
        return "TwigBundle:Exception:error.js.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  28 => 2,  25 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("/*
{{ status_code }} {{ status_text }}

*/
", "TwigBundle:Exception:error.js.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle/Resources/views/Exception/error.js.twig");
    }
}
