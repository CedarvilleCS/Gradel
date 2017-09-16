<?php

/* TwigBundle:Exception:exception.rdf.twig */
class __TwigTemplate_0774ae5e69511382dced60f906716cabd4efad5932c1aef2c6a680b813a735bc extends Twig_Template
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
        $__internal_a8733ec7a3c38135fedb526789abb8c23098693fb4a515dcf71612ae6884d713 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_a8733ec7a3c38135fedb526789abb8c23098693fb4a515dcf71612ae6884d713->enter($__internal_a8733ec7a3c38135fedb526789abb8c23098693fb4a515dcf71612ae6884d713_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:exception.rdf.twig"));

        $__internal_a1ae661f524d14c4870fe330d5546d46db2d8cae4b9a0556f59b8e5bbe673e95 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_a1ae661f524d14c4870fe330d5546d46db2d8cae4b9a0556f59b8e5bbe673e95->enter($__internal_a1ae661f524d14c4870fe330d5546d46db2d8cae4b9a0556f59b8e5bbe673e95_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:exception.rdf.twig"));

        // line 1
        echo twig_include($this->env, $context, "@Twig/Exception/exception.xml.twig", array("exception" => (isset($context["exception"]) ? $context["exception"] : $this->getContext($context, "exception"))));
        echo "
";
        
        $__internal_a8733ec7a3c38135fedb526789abb8c23098693fb4a515dcf71612ae6884d713->leave($__internal_a8733ec7a3c38135fedb526789abb8c23098693fb4a515dcf71612ae6884d713_prof);

        
        $__internal_a1ae661f524d14c4870fe330d5546d46db2d8cae4b9a0556f59b8e5bbe673e95->leave($__internal_a1ae661f524d14c4870fe330d5546d46db2d8cae4b9a0556f59b8e5bbe673e95_prof);

    }

    public function getTemplateName()
    {
        return "TwigBundle:Exception:exception.rdf.twig";
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
", "TwigBundle:Exception:exception.rdf.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle/Resources/views/Exception/exception.rdf.twig");
    }
}
