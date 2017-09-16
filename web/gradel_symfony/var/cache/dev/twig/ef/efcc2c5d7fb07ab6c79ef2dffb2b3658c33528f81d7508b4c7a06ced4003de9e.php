<?php

/* TwigBundle:Exception:exception.atom.twig */
class __TwigTemplate_b66f22fd44260057ad8a62d65396cda02d7fb901f9acd2668bf860bda94b2b82 extends Twig_Template
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
        $__internal_a1c6c056bbaada0232a44680b2b00ae74579733e00fb400b64b4d6430fb8e126 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_a1c6c056bbaada0232a44680b2b00ae74579733e00fb400b64b4d6430fb8e126->enter($__internal_a1c6c056bbaada0232a44680b2b00ae74579733e00fb400b64b4d6430fb8e126_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:exception.atom.twig"));

        $__internal_c2b9c693be4b61e2837b70af4c50eb79c9b57896fd833b8957f2137024549f2a = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_c2b9c693be4b61e2837b70af4c50eb79c9b57896fd833b8957f2137024549f2a->enter($__internal_c2b9c693be4b61e2837b70af4c50eb79c9b57896fd833b8957f2137024549f2a_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:exception.atom.twig"));

        // line 1
        echo twig_include($this->env, $context, "@Twig/Exception/exception.xml.twig", array("exception" => (isset($context["exception"]) ? $context["exception"] : $this->getContext($context, "exception"))));
        echo "
";
        
        $__internal_a1c6c056bbaada0232a44680b2b00ae74579733e00fb400b64b4d6430fb8e126->leave($__internal_a1c6c056bbaada0232a44680b2b00ae74579733e00fb400b64b4d6430fb8e126_prof);

        
        $__internal_c2b9c693be4b61e2837b70af4c50eb79c9b57896fd833b8957f2137024549f2a->leave($__internal_c2b9c693be4b61e2837b70af4c50eb79c9b57896fd833b8957f2137024549f2a_prof);

    }

    public function getTemplateName()
    {
        return "TwigBundle:Exception:exception.atom.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  25 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("{{ include('@Twig/Exception/exception.xml.twig', { exception: exception }) }}
", "TwigBundle:Exception:exception.atom.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle/Resources/views/Exception/exception.atom.twig");
    }
}
