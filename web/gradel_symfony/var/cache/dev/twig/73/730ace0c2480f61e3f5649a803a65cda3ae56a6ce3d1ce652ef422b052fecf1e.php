<?php

/* TwigBundle:Exception:exception.css.twig */
class __TwigTemplate_11d1885057f6b52c78643c4042824dffc592883e725d995ad827f0c38335e2af extends Twig_Template
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
        $__internal_9c0ff21e4ff4615c1401c4fde9267d44c6ff11c6a08ecaeb585749340233adac = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_9c0ff21e4ff4615c1401c4fde9267d44c6ff11c6a08ecaeb585749340233adac->enter($__internal_9c0ff21e4ff4615c1401c4fde9267d44c6ff11c6a08ecaeb585749340233adac_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:exception.css.twig"));

        $__internal_c2de98d7255cdca854adba3ff4d5b5a193c1e99d8b9a192ac1e774b2a9b76d15 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_c2de98d7255cdca854adba3ff4d5b5a193c1e99d8b9a192ac1e774b2a9b76d15->enter($__internal_c2de98d7255cdca854adba3ff4d5b5a193c1e99d8b9a192ac1e774b2a9b76d15_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:exception.css.twig"));

        // line 1
        echo "/*
";
        // line 2
        echo twig_include($this->env, $context, "@Twig/Exception/exception.txt.twig", array("exception" => (isset($context["exception"]) ? $context["exception"] : $this->getContext($context, "exception"))));
        echo "
*/
";
        
        $__internal_9c0ff21e4ff4615c1401c4fde9267d44c6ff11c6a08ecaeb585749340233adac->leave($__internal_9c0ff21e4ff4615c1401c4fde9267d44c6ff11c6a08ecaeb585749340233adac_prof);

        
        $__internal_c2de98d7255cdca854adba3ff4d5b5a193c1e99d8b9a192ac1e774b2a9b76d15->leave($__internal_c2de98d7255cdca854adba3ff4d5b5a193c1e99d8b9a192ac1e774b2a9b76d15_prof);

    }

    public function getTemplateName()
    {
        return "TwigBundle:Exception:exception.css.twig";
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
", "TwigBundle:Exception:exception.css.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle/Resources/views/Exception/exception.css.twig");
    }
}
