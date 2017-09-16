<?php

/* WebProfilerBundle:Profiler:info.html.twig */
class __TwigTemplate_432e15b31053ffcb4fc5b65ff2547e831e70cc06101146847aea688688d8de06 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("@WebProfiler/Profiler/layout.html.twig", "WebProfilerBundle:Profiler:info.html.twig", 1);
        $this->blocks = array(
            'summary' => array($this, 'block_summary'),
            'panel' => array($this, 'block_panel'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "@WebProfiler/Profiler/layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_742c97e517c5f4242acbe84d985ca66776079cb8a170e5516e324a5d01c4da6a = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_742c97e517c5f4242acbe84d985ca66776079cb8a170e5516e324a5d01c4da6a->enter($__internal_742c97e517c5f4242acbe84d985ca66776079cb8a170e5516e324a5d01c4da6a_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "WebProfilerBundle:Profiler:info.html.twig"));

        $__internal_cd4e009fcab6a6c5124531fc38097065df1e7d555cfe09d3a8cc8a078c3d45a1 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_cd4e009fcab6a6c5124531fc38097065df1e7d555cfe09d3a8cc8a078c3d45a1->enter($__internal_cd4e009fcab6a6c5124531fc38097065df1e7d555cfe09d3a8cc8a078c3d45a1_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "WebProfilerBundle:Profiler:info.html.twig"));

        // line 3
        $context["messages"] = array("no_token" => array("status" => "error", "title" => (((((        // line 6
array_key_exists("token", $context)) ? (_twig_default_filter((isset($context["token"]) ? $context["token"] : $this->getContext($context, "token")), "")) : ("")) == "latest")) ? ("There are no profiles") : ("Token not found")), "message" => (((((        // line 7
array_key_exists("token", $context)) ? (_twig_default_filter((isset($context["token"]) ? $context["token"] : $this->getContext($context, "token")), "")) : ("")) == "latest")) ? ("No profiles found in the database.") : ((("Token \"" . ((array_key_exists("token", $context)) ? (_twig_default_filter((isset($context["token"]) ? $context["token"] : $this->getContext($context, "token")), "")) : (""))) . "\" was not found in the database.")))));
        // line 1
        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_742c97e517c5f4242acbe84d985ca66776079cb8a170e5516e324a5d01c4da6a->leave($__internal_742c97e517c5f4242acbe84d985ca66776079cb8a170e5516e324a5d01c4da6a_prof);

        
        $__internal_cd4e009fcab6a6c5124531fc38097065df1e7d555cfe09d3a8cc8a078c3d45a1->leave($__internal_cd4e009fcab6a6c5124531fc38097065df1e7d555cfe09d3a8cc8a078c3d45a1_prof);

    }

    // line 11
    public function block_summary($context, array $blocks = array())
    {
        $__internal_b91eb3b99d200635c051c85f8d6bbc6cd49a00a3b1170cd3f9ff52e8e3751fd5 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_b91eb3b99d200635c051c85f8d6bbc6cd49a00a3b1170cd3f9ff52e8e3751fd5->enter($__internal_b91eb3b99d200635c051c85f8d6bbc6cd49a00a3b1170cd3f9ff52e8e3751fd5_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "summary"));

        $__internal_3be85d2ad07d2cc488f086c4d225185ebe5efdfdb8eafff745539d08c1218567 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_3be85d2ad07d2cc488f086c4d225185ebe5efdfdb8eafff745539d08c1218567->enter($__internal_3be85d2ad07d2cc488f086c4d225185ebe5efdfdb8eafff745539d08c1218567_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "summary"));

        // line 12
        echo "    <div class=\"status status-";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["messages"]) ? $context["messages"] : $this->getContext($context, "messages")), (isset($context["about"]) ? $context["about"] : $this->getContext($context, "about")), array(), "array"), "status", array()), "html", null, true);
        echo "\">
        <div class=\"container\">
            <h2>";
        // line 14
        echo twig_escape_filter($this->env, twig_title_string_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["messages"]) ? $context["messages"] : $this->getContext($context, "messages")), (isset($context["about"]) ? $context["about"] : $this->getContext($context, "about")), array(), "array"), "status", array())), "html", null, true);
        echo "</h2>
        </div>
    </div>
";
        
        $__internal_3be85d2ad07d2cc488f086c4d225185ebe5efdfdb8eafff745539d08c1218567->leave($__internal_3be85d2ad07d2cc488f086c4d225185ebe5efdfdb8eafff745539d08c1218567_prof);

        
        $__internal_b91eb3b99d200635c051c85f8d6bbc6cd49a00a3b1170cd3f9ff52e8e3751fd5->leave($__internal_b91eb3b99d200635c051c85f8d6bbc6cd49a00a3b1170cd3f9ff52e8e3751fd5_prof);

    }

    // line 19
    public function block_panel($context, array $blocks = array())
    {
        $__internal_7149f5692153be89292fe30edaf061a0913b09757be0e4c024c7f55f6cf783d6 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_7149f5692153be89292fe30edaf061a0913b09757be0e4c024c7f55f6cf783d6->enter($__internal_7149f5692153be89292fe30edaf061a0913b09757be0e4c024c7f55f6cf783d6_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "panel"));

        $__internal_bad05df4fde3d1273705f2cb04eb0d8728da0440d5213e1ce83acae65729730f = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_bad05df4fde3d1273705f2cb04eb0d8728da0440d5213e1ce83acae65729730f->enter($__internal_bad05df4fde3d1273705f2cb04eb0d8728da0440d5213e1ce83acae65729730f_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "panel"));

        // line 20
        echo "    <h2>";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["messages"]) ? $context["messages"] : $this->getContext($context, "messages")), (isset($context["about"]) ? $context["about"] : $this->getContext($context, "about")), array(), "array"), "title", array()), "html", null, true);
        echo "</h2>
    <p>";
        // line 21
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["messages"]) ? $context["messages"] : $this->getContext($context, "messages")), (isset($context["about"]) ? $context["about"] : $this->getContext($context, "about")), array(), "array"), "message", array()), "html", null, true);
        echo "</p>
";
        
        $__internal_bad05df4fde3d1273705f2cb04eb0d8728da0440d5213e1ce83acae65729730f->leave($__internal_bad05df4fde3d1273705f2cb04eb0d8728da0440d5213e1ce83acae65729730f_prof);

        
        $__internal_7149f5692153be89292fe30edaf061a0913b09757be0e4c024c7f55f6cf783d6->leave($__internal_7149f5692153be89292fe30edaf061a0913b09757be0e4c024c7f55f6cf783d6_prof);

    }

    public function getTemplateName()
    {
        return "WebProfilerBundle:Profiler:info.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  89 => 21,  84 => 20,  75 => 19,  61 => 14,  55 => 12,  46 => 11,  36 => 1,  34 => 7,  33 => 6,  32 => 3,  11 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("{% extends '@WebProfiler/Profiler/layout.html.twig' %}

{% set messages = {
    'no_token' : {
        status:  'error',
        title:   (token|default('') == 'latest') ? 'There are no profiles' : 'Token not found',
        message: (token|default('') == 'latest') ? 'No profiles found in the database.' : 'Token \"' ~ token|default('') ~ '\" was not found in the database.'
    }
} %}

{% block summary %}
    <div class=\"status status-{{ messages[about].status }}\">
        <div class=\"container\">
            <h2>{{ messages[about].status|title }}</h2>
        </div>
    </div>
{% endblock %}

{% block panel %}
    <h2>{{ messages[about].title }}</h2>
    <p>{{ messages[about].message }}</p>
{% endblock %}
", "WebProfilerBundle:Profiler:info.html.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/WebProfilerBundle/Resources/views/Profiler/info.html.twig");
    }
}
