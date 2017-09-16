<?php

/* TwigBundle:Exception:exception.js.twig */
class __TwigTemplate_22a999904cd32a1d89f426ccaab34a42554c32649573535ce9bffd11eed96b39 extends Twig_Template
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
        $__internal_d439fdae81dffc5a1b13ecd9ac33d9a346496203eab38d2637d41a229859255a = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_d439fdae81dffc5a1b13ecd9ac33d9a346496203eab38d2637d41a229859255a->enter($__internal_d439fdae81dffc5a1b13ecd9ac33d9a346496203eab38d2637d41a229859255a_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:exception.js.twig"));

        $__internal_a61cdee8b94381b0d7f9c8dded6fa0466ca23acbaa6f7f2300f91e77320cb806 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_a61cdee8b94381b0d7f9c8dded6fa0466ca23acbaa6f7f2300f91e77320cb806->enter($__internal_a61cdee8b94381b0d7f9c8dded6fa0466ca23acbaa6f7f2300f91e77320cb806_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:exception.js.twig"));

        // line 1
        echo "/*
";
        // line 2
        echo twig_include($this->env, $context, "@Twig/Exception/exception.txt.twig", array("exception" => (isset($context["exception"]) ? $context["exception"] : $this->getContext($context, "exception"))));
        echo "
*/
";
        
        $__internal_d439fdae81dffc5a1b13ecd9ac33d9a346496203eab38d2637d41a229859255a->leave($__internal_d439fdae81dffc5a1b13ecd9ac33d9a346496203eab38d2637d41a229859255a_prof);

        
        $__internal_a61cdee8b94381b0d7f9c8dded6fa0466ca23acbaa6f7f2300f91e77320cb806->leave($__internal_a61cdee8b94381b0d7f9c8dded6fa0466ca23acbaa6f7f2300f91e77320cb806_prof);

    }

    public function getTemplateName()
    {
        return "TwigBundle:Exception:exception.js.twig";
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
{{ include('@Twig/Exception/exception.txt.twig', { exception: exception }) }}
*/
", "TwigBundle:Exception:exception.js.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle/Resources/views/Exception/exception.js.twig");
    }
}
