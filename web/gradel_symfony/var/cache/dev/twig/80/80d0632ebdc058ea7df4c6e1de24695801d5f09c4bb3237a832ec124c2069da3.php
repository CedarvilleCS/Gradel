<?php

/* TwigBundle:Exception:error.css.twig */
class __TwigTemplate_587128cadfe3e3a54ffb7413d194c7b1ce455e0d80e46391938dc4d8769f8ea0 extends Twig_Template
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
        $__internal_d4d23a6de3b555cc230a712df5085851e13a7e6112c8a4a61feb5bb0b0e0d9c4 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_d4d23a6de3b555cc230a712df5085851e13a7e6112c8a4a61feb5bb0b0e0d9c4->enter($__internal_d4d23a6de3b555cc230a712df5085851e13a7e6112c8a4a61feb5bb0b0e0d9c4_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:error.css.twig"));

        $__internal_9d4bc64007281061ac691c2ab40385b59e0dd73cc4fc4142a48c64d64577e5b4 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_9d4bc64007281061ac691c2ab40385b59e0dd73cc4fc4142a48c64d64577e5b4->enter($__internal_9d4bc64007281061ac691c2ab40385b59e0dd73cc4fc4142a48c64d64577e5b4_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:error.css.twig"));

        // line 1
        echo "/*
";
        // line 2
        echo twig_escape_filter($this->env, (isset($context["status_code"]) ? $context["status_code"] : $this->getContext($context, "status_code")), "css", null, true);
        echo " ";
        echo twig_escape_filter($this->env, (isset($context["status_text"]) ? $context["status_text"] : $this->getContext($context, "status_text")), "css", null, true);
        echo "

*/
";
        
        $__internal_d4d23a6de3b555cc230a712df5085851e13a7e6112c8a4a61feb5bb0b0e0d9c4->leave($__internal_d4d23a6de3b555cc230a712df5085851e13a7e6112c8a4a61feb5bb0b0e0d9c4_prof);

        
        $__internal_9d4bc64007281061ac691c2ab40385b59e0dd73cc4fc4142a48c64d64577e5b4->leave($__internal_9d4bc64007281061ac691c2ab40385b59e0dd73cc4fc4142a48c64d64577e5b4_prof);

    }

    public function getTemplateName()
    {
        return "TwigBundle:Exception:error.css.twig";
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
", "TwigBundle:Exception:error.css.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle/Resources/views/Exception/error.css.twig");
    }
}
