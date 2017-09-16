<?php

/* WebProfilerBundle:Collector:exception.css.twig */
class __TwigTemplate_dc9a8dc7846e6a3a492c5da8438703bbf9f1fcb3e9743c96d27b7f33dbdfbdb6 extends Twig_Template
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
        $__internal_31573c30c9595bd36d64f1bdb70de9768f9e3d6b7d0b060657297206a421dcd3 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_31573c30c9595bd36d64f1bdb70de9768f9e3d6b7d0b060657297206a421dcd3->enter($__internal_31573c30c9595bd36d64f1bdb70de9768f9e3d6b7d0b060657297206a421dcd3_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "WebProfilerBundle:Collector:exception.css.twig"));

        $__internal_9c1a6f23c5096d6061c5365ca90c46aeaa3da2861c21f7cbb90b03b505bedfe4 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_9c1a6f23c5096d6061c5365ca90c46aeaa3da2861c21f7cbb90b03b505bedfe4->enter($__internal_9c1a6f23c5096d6061c5365ca90c46aeaa3da2861c21f7cbb90b03b505bedfe4_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "WebProfilerBundle:Collector:exception.css.twig"));

        // line 1
        echo twig_include($this->env, $context, "@Twig/exception.css.twig");
        echo "

.container {
    max-width: auto;
    margin: 0;
    padding: 0;
}
.container .container {
    padding: 0;
}

.exception-summary {
    background: #FFF;
    border: 1px solid #E0E0E0;
    box-shadow: 0 0 1px rgba(128, 128, 128, .2);
    margin: 1em 0;
    padding: 10px;
}
.exception-summary.exception-without-message {
    display: none;
}

.exception-message {
    color: #B0413E;
}

.exception-metadata,
.exception-illustration {
    display: none;
}

.exception-message-wrapper .container {
    min-height: auto;
}
";
        
        $__internal_31573c30c9595bd36d64f1bdb70de9768f9e3d6b7d0b060657297206a421dcd3->leave($__internal_31573c30c9595bd36d64f1bdb70de9768f9e3d6b7d0b060657297206a421dcd3_prof);

        
        $__internal_9c1a6f23c5096d6061c5365ca90c46aeaa3da2861c21f7cbb90b03b505bedfe4->leave($__internal_9c1a6f23c5096d6061c5365ca90c46aeaa3da2861c21f7cbb90b03b505bedfe4_prof);

    }

    public function getTemplateName()
    {
        return "WebProfilerBundle:Collector:exception.css.twig";
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
        return new Twig_Source("{{ include('@Twig/exception.css.twig') }}

.container {
    max-width: auto;
    margin: 0;
    padding: 0;
}
.container .container {
    padding: 0;
}

.exception-summary {
    background: #FFF;
    border: 1px solid #E0E0E0;
    box-shadow: 0 0 1px rgba(128, 128, 128, .2);
    margin: 1em 0;
    padding: 10px;
}
.exception-summary.exception-without-message {
    display: none;
}

.exception-message {
    color: #B0413E;
}

.exception-metadata,
.exception-illustration {
    display: none;
}

.exception-message-wrapper .container {
    min-height: auto;
}
", "WebProfilerBundle:Collector:exception.css.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/WebProfilerBundle/Resources/views/Collector/exception.css.twig");
    }
}
