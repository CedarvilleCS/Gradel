<?php

/* TwigBundle:Exception:error.rdf.twig */
class __TwigTemplate_7fa4700dc56bac756a81bca3b5c33187a8549bd75d0bef0c6f0dae81abb6991a extends Twig_Template
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
        $__internal_d3efdaa127c17f408f87c59496109bd4dbc4464a8dd9ce7fb21dda5518cfaa9f = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_d3efdaa127c17f408f87c59496109bd4dbc4464a8dd9ce7fb21dda5518cfaa9f->enter($__internal_d3efdaa127c17f408f87c59496109bd4dbc4464a8dd9ce7fb21dda5518cfaa9f_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:error.rdf.twig"));

        $__internal_bba649f958b3dff8a8ac525bb7ccffcab1623fd43048501ec6056173987bf92e = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_bba649f958b3dff8a8ac525bb7ccffcab1623fd43048501ec6056173987bf92e->enter($__internal_bba649f958b3dff8a8ac525bb7ccffcab1623fd43048501ec6056173987bf92e_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:error.rdf.twig"));

        // line 1
        echo twig_include($this->env, $context, "@Twig/Exception/error.xml.twig");
        echo "
";
        
        $__internal_d3efdaa127c17f408f87c59496109bd4dbc4464a8dd9ce7fb21dda5518cfaa9f->leave($__internal_d3efdaa127c17f408f87c59496109bd4dbc4464a8dd9ce7fb21dda5518cfaa9f_prof);

        
        $__internal_bba649f958b3dff8a8ac525bb7ccffcab1623fd43048501ec6056173987bf92e->leave($__internal_bba649f958b3dff8a8ac525bb7ccffcab1623fd43048501ec6056173987bf92e_prof);

    }

    public function getTemplateName()
    {
        return "TwigBundle:Exception:error.rdf.twig";
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
        return new Twig_Source("{{ include('@Twig/Exception/error.xml.twig') }}
", "TwigBundle:Exception:error.rdf.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle/Resources/views/Exception/error.rdf.twig");
    }
}
