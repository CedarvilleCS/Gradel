<?php

/* WebProfilerBundle:Profiler:toolbar_redirect.html.twig */
class __TwigTemplate_35062b352eb103734c01ebe25a1c295e6c61d654f8854e8c017eec3583602ec5 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("@Twig/layout.html.twig", "WebProfilerBundle:Profiler:toolbar_redirect.html.twig", 1);
        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'body' => array($this, 'block_body'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "@Twig/layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_dd9304d78856a34cdb6ea353041ced7acce381e0bde5ad550dd4b4d4fd1e3610 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_dd9304d78856a34cdb6ea353041ced7acce381e0bde5ad550dd4b4d4fd1e3610->enter($__internal_dd9304d78856a34cdb6ea353041ced7acce381e0bde5ad550dd4b4d4fd1e3610_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "WebProfilerBundle:Profiler:toolbar_redirect.html.twig"));

        $__internal_9c84dd95bee23846d6bc695030671a8a0c495dd98eb6b3599366e83fc3119139 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_9c84dd95bee23846d6bc695030671a8a0c495dd98eb6b3599366e83fc3119139->enter($__internal_9c84dd95bee23846d6bc695030671a8a0c495dd98eb6b3599366e83fc3119139_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "WebProfilerBundle:Profiler:toolbar_redirect.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_dd9304d78856a34cdb6ea353041ced7acce381e0bde5ad550dd4b4d4fd1e3610->leave($__internal_dd9304d78856a34cdb6ea353041ced7acce381e0bde5ad550dd4b4d4fd1e3610_prof);

        
        $__internal_9c84dd95bee23846d6bc695030671a8a0c495dd98eb6b3599366e83fc3119139->leave($__internal_9c84dd95bee23846d6bc695030671a8a0c495dd98eb6b3599366e83fc3119139_prof);

    }

    // line 3
    public function block_title($context, array $blocks = array())
    {
        $__internal_ac3f421492a5b169ded94c36148649d4010bf4dc8c129ef339b82aa97a08275b = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_ac3f421492a5b169ded94c36148649d4010bf4dc8c129ef339b82aa97a08275b->enter($__internal_ac3f421492a5b169ded94c36148649d4010bf4dc8c129ef339b82aa97a08275b_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        $__internal_b147224140b69763b81fd804051eb70878ad660ba01077ee9671bb6d3993bded = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_b147224140b69763b81fd804051eb70878ad660ba01077ee9671bb6d3993bded->enter($__internal_b147224140b69763b81fd804051eb70878ad660ba01077ee9671bb6d3993bded_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        echo "Redirection Intercepted";
        
        $__internal_b147224140b69763b81fd804051eb70878ad660ba01077ee9671bb6d3993bded->leave($__internal_b147224140b69763b81fd804051eb70878ad660ba01077ee9671bb6d3993bded_prof);

        
        $__internal_ac3f421492a5b169ded94c36148649d4010bf4dc8c129ef339b82aa97a08275b->leave($__internal_ac3f421492a5b169ded94c36148649d4010bf4dc8c129ef339b82aa97a08275b_prof);

    }

    // line 5
    public function block_body($context, array $blocks = array())
    {
        $__internal_f3e102cef0a8b62100cc492fb6b4f9eeb4caed5f3be79f56dcf64fc521c05ada = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_f3e102cef0a8b62100cc492fb6b4f9eeb4caed5f3be79f56dcf64fc521c05ada->enter($__internal_f3e102cef0a8b62100cc492fb6b4f9eeb4caed5f3be79f56dcf64fc521c05ada_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        $__internal_1a078233c72d3c7e4758090f71a72ef3629bf8904ce42e8f95305ecf25174c35 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_1a078233c72d3c7e4758090f71a72ef3629bf8904ce42e8f95305ecf25174c35->enter($__internal_1a078233c72d3c7e4758090f71a72ef3629bf8904ce42e8f95305ecf25174c35_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        // line 6
        echo "    <div class=\"sf-reset\">
        <div class=\"block-exception\">
            <h1>This request redirects to <a href=\"";
        // line 8
        echo twig_escape_filter($this->env, (isset($context["location"]) ? $context["location"] : $this->getContext($context, "location")), "html", null, true);
        echo "\">";
        echo twig_escape_filter($this->env, (isset($context["location"]) ? $context["location"] : $this->getContext($context, "location")), "html", null, true);
        echo "</a>.</h1>

            <p>
                <small>
                    The redirect was intercepted by the web debug toolbar to help debugging.
                    For more information, see the \"intercept-redirects\" option of the Profiler.
                </small>
            </p>
        </div>
    </div>
";
        
        $__internal_1a078233c72d3c7e4758090f71a72ef3629bf8904ce42e8f95305ecf25174c35->leave($__internal_1a078233c72d3c7e4758090f71a72ef3629bf8904ce42e8f95305ecf25174c35_prof);

        
        $__internal_f3e102cef0a8b62100cc492fb6b4f9eeb4caed5f3be79f56dcf64fc521c05ada->leave($__internal_f3e102cef0a8b62100cc492fb6b4f9eeb4caed5f3be79f56dcf64fc521c05ada_prof);

    }

    public function getTemplateName()
    {
        return "WebProfilerBundle:Profiler:toolbar_redirect.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  72 => 8,  68 => 6,  59 => 5,  41 => 3,  11 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("{% extends '@Twig/layout.html.twig' %}

{% block title 'Redirection Intercepted' %}

{% block body %}
    <div class=\"sf-reset\">
        <div class=\"block-exception\">
            <h1>This request redirects to <a href=\"{{ location }}\">{{ location }}</a>.</h1>

            <p>
                <small>
                    The redirect was intercepted by the web debug toolbar to help debugging.
                    For more information, see the \"intercept-redirects\" option of the Profiler.
                </small>
            </p>
        </div>
    </div>
{% endblock %}
", "WebProfilerBundle:Profiler:toolbar_redirect.html.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/WebProfilerBundle/Resources/views/Profiler/toolbar_redirect.html.twig");
    }
}
