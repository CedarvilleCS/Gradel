<?php

/* WebProfilerBundle:Profiler:open.html.twig */
class __TwigTemplate_8d4cd47b336a0ecd6224f0e28801bfc602431d68e2a1b9aa80a0b5f2c2fcd858 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("@WebProfiler/Profiler/base.html.twig", "WebProfilerBundle:Profiler:open.html.twig", 1);
        $this->blocks = array(
            'head' => array($this, 'block_head'),
            'body' => array($this, 'block_body'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "@WebProfiler/Profiler/base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_7c249ba840e8186fc697a51a7d0d6c33adac57c1e282b77fc72dc3f1d88630fc = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_7c249ba840e8186fc697a51a7d0d6c33adac57c1e282b77fc72dc3f1d88630fc->enter($__internal_7c249ba840e8186fc697a51a7d0d6c33adac57c1e282b77fc72dc3f1d88630fc_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "WebProfilerBundle:Profiler:open.html.twig"));

        $__internal_3045a01018020046424aa6d19bf74ed220ad8798497704676728389e31b11536 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_3045a01018020046424aa6d19bf74ed220ad8798497704676728389e31b11536->enter($__internal_3045a01018020046424aa6d19bf74ed220ad8798497704676728389e31b11536_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "WebProfilerBundle:Profiler:open.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_7c249ba840e8186fc697a51a7d0d6c33adac57c1e282b77fc72dc3f1d88630fc->leave($__internal_7c249ba840e8186fc697a51a7d0d6c33adac57c1e282b77fc72dc3f1d88630fc_prof);

        
        $__internal_3045a01018020046424aa6d19bf74ed220ad8798497704676728389e31b11536->leave($__internal_3045a01018020046424aa6d19bf74ed220ad8798497704676728389e31b11536_prof);

    }

    // line 3
    public function block_head($context, array $blocks = array())
    {
        $__internal_5c60e4479deb6aad706266cbc0641ef017ae70e5fba17a5f98a8b66febad2c50 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_5c60e4479deb6aad706266cbc0641ef017ae70e5fba17a5f98a8b66febad2c50->enter($__internal_5c60e4479deb6aad706266cbc0641ef017ae70e5fba17a5f98a8b66febad2c50_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "head"));

        $__internal_26e75dbb0fa7cf90acbb880dd3529b380a805803add5e628f4f8da1bbd3c4d5c = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_26e75dbb0fa7cf90acbb880dd3529b380a805803add5e628f4f8da1bbd3c4d5c->enter($__internal_26e75dbb0fa7cf90acbb880dd3529b380a805803add5e628f4f8da1bbd3c4d5c_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "head"));

        // line 4
        echo "    <style>
        ";
        // line 5
        echo twig_include($this->env, $context, "@WebProfiler/Profiler/open.css.twig");
        echo "
    </style>
";
        
        $__internal_26e75dbb0fa7cf90acbb880dd3529b380a805803add5e628f4f8da1bbd3c4d5c->leave($__internal_26e75dbb0fa7cf90acbb880dd3529b380a805803add5e628f4f8da1bbd3c4d5c_prof);

        
        $__internal_5c60e4479deb6aad706266cbc0641ef017ae70e5fba17a5f98a8b66febad2c50->leave($__internal_5c60e4479deb6aad706266cbc0641ef017ae70e5fba17a5f98a8b66febad2c50_prof);

    }

    // line 9
    public function block_body($context, array $blocks = array())
    {
        $__internal_6edf59ef9740a8448b1b36db98bbd3ea6fd552b615f78c8f2791256b7bbb3739 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_6edf59ef9740a8448b1b36db98bbd3ea6fd552b615f78c8f2791256b7bbb3739->enter($__internal_6edf59ef9740a8448b1b36db98bbd3ea6fd552b615f78c8f2791256b7bbb3739_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        $__internal_fd06678f914ef29c76d9c6645746085fcce168bc2854c3c22ec2e4a17a4c6ca8 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_fd06678f914ef29c76d9c6645746085fcce168bc2854c3c22ec2e4a17a4c6ca8->enter($__internal_fd06678f914ef29c76d9c6645746085fcce168bc2854c3c22ec2e4a17a4c6ca8_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        // line 10
        echo "<div class=\"header\">
    <h1>";
        // line 11
        echo twig_escape_filter($this->env, (isset($context["file"]) ? $context["file"] : $this->getContext($context, "file")), "html", null, true);
        echo " <small>line ";
        echo twig_escape_filter($this->env, (isset($context["line"]) ? $context["line"] : $this->getContext($context, "line")), "html", null, true);
        echo "</small></h1>
    <a class=\"doc\" href=\"https://symfony.com/doc/";
        // line 12
        echo twig_escape_filter($this->env, twig_constant("Symfony\\Component\\HttpKernel\\Kernel::VERSION"), "html", null, true);
        echo "/reference/configuration/framework.html#ide\" rel=\"help\">Open in your IDE?</a>
</div>
<div class=\"source\">
    ";
        // line 15
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\CodeExtension')->fileExcerpt((isset($context["filename"]) ? $context["filename"] : $this->getContext($context, "filename")), (isset($context["line"]) ? $context["line"] : $this->getContext($context, "line")),  -1);
        echo "
</div>
";
        
        $__internal_fd06678f914ef29c76d9c6645746085fcce168bc2854c3c22ec2e4a17a4c6ca8->leave($__internal_fd06678f914ef29c76d9c6645746085fcce168bc2854c3c22ec2e4a17a4c6ca8_prof);

        
        $__internal_6edf59ef9740a8448b1b36db98bbd3ea6fd552b615f78c8f2791256b7bbb3739->leave($__internal_6edf59ef9740a8448b1b36db98bbd3ea6fd552b615f78c8f2791256b7bbb3739_prof);

    }

    public function getTemplateName()
    {
        return "WebProfilerBundle:Profiler:open.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  90 => 15,  84 => 12,  78 => 11,  75 => 10,  66 => 9,  53 => 5,  50 => 4,  41 => 3,  11 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("{% extends '@WebProfiler/Profiler/base.html.twig' %}

{% block head %}
    <style>
        {{ include('@WebProfiler/Profiler/open.css.twig') }}
    </style>
{% endblock %}

{% block body %}
<div class=\"header\">
    <h1>{{ file }} <small>line {{ line }}</small></h1>
    <a class=\"doc\" href=\"https://symfony.com/doc/{{ constant('Symfony\\\\Component\\\\HttpKernel\\\\Kernel::VERSION') }}/reference/configuration/framework.html#ide\" rel=\"help\">Open in your IDE?</a>
</div>
<div class=\"source\">
    {{ filename|file_excerpt(line, -1) }}
</div>
{% endblock %}
", "WebProfilerBundle:Profiler:open.html.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/WebProfilerBundle/Resources/views/Profiler/open.html.twig");
    }
}
