<?php

/* :lucky:number.html.twig */
class __TwigTemplate_5f2fb3989d30cc1bb703cb59f0dd94f4f3307dc8bce5f67d6325184450fdca2f extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("base.html.twig", ":lucky:number.html.twig", 1);
        $this->blocks = array(
            'body' => array($this, 'block_body'),
            'stylesheets' => array($this, 'block_stylesheets'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_c088b048503a826b4941488797f4bbdc20b29af633d7e7999dc3738919379d20 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_c088b048503a826b4941488797f4bbdc20b29af633d7e7999dc3738919379d20->enter($__internal_c088b048503a826b4941488797f4bbdc20b29af633d7e7999dc3738919379d20_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", ":lucky:number.html.twig"));

        $__internal_fcbc0df545d63c3c70cc8cfbb509fa2c59d8e66fd66cf650ab1d5a92edcaa1e2 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_fcbc0df545d63c3c70cc8cfbb509fa2c59d8e66fd66cf650ab1d5a92edcaa1e2->enter($__internal_fcbc0df545d63c3c70cc8cfbb509fa2c59d8e66fd66cf650ab1d5a92edcaa1e2_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", ":lucky:number.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_c088b048503a826b4941488797f4bbdc20b29af633d7e7999dc3738919379d20->leave($__internal_c088b048503a826b4941488797f4bbdc20b29af633d7e7999dc3738919379d20_prof);

        
        $__internal_fcbc0df545d63c3c70cc8cfbb509fa2c59d8e66fd66cf650ab1d5a92edcaa1e2->leave($__internal_fcbc0df545d63c3c70cc8cfbb509fa2c59d8e66fd66cf650ab1d5a92edcaa1e2_prof);

    }

    // line 3
    public function block_body($context, array $blocks = array())
    {
        $__internal_f56b6bafaf34595a02fadf2659dbcb8644bb0859ce8ee11e0af73bf9a257fb87 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_f56b6bafaf34595a02fadf2659dbcb8644bb0859ce8ee11e0af73bf9a257fb87->enter($__internal_f56b6bafaf34595a02fadf2659dbcb8644bb0859ce8ee11e0af73bf9a257fb87_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        $__internal_ce2d9b1a53d9a5084e7416b4539865e928d0dc887e22fb5baeb214e5a4c3c856 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_ce2d9b1a53d9a5084e7416b4539865e928d0dc887e22fb5baeb214e5a4c3c856->enter($__internal_ce2d9b1a53d9a5084e7416b4539865e928d0dc887e22fb5baeb214e5a4c3c856_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        // line 4
        echo "\t<h1>Your lucky number is ";
        echo twig_escape_filter($this->env, (isset($context["number"]) ? $context["number"] : $this->getContext($context, "number")), "html", null, true);
        echo "</h1>
";
        
        $__internal_ce2d9b1a53d9a5084e7416b4539865e928d0dc887e22fb5baeb214e5a4c3c856->leave($__internal_ce2d9b1a53d9a5084e7416b4539865e928d0dc887e22fb5baeb214e5a4c3c856_prof);

        
        $__internal_f56b6bafaf34595a02fadf2659dbcb8644bb0859ce8ee11e0af73bf9a257fb87->leave($__internal_f56b6bafaf34595a02fadf2659dbcb8644bb0859ce8ee11e0af73bf9a257fb87_prof);

    }

    // line 7
    public function block_stylesheets($context, array $blocks = array())
    {
        $__internal_35e69bb352ecef5fd7be340483a505345f48e75e3e95dd17786a0811b766e783 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_35e69bb352ecef5fd7be340483a505345f48e75e3e95dd17786a0811b766e783->enter($__internal_35e69bb352ecef5fd7be340483a505345f48e75e3e95dd17786a0811b766e783_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "stylesheets"));

        $__internal_2a8910b8edc7611eb1b8a80f9ed7576a5f811aea87ca569b6eb7be38babbd64a = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_2a8910b8edc7611eb1b8a80f9ed7576a5f811aea87ca569b6eb7be38babbd64a->enter($__internal_2a8910b8edc7611eb1b8a80f9ed7576a5f811aea87ca569b6eb7be38babbd64a_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "stylesheets"));

        
        $__internal_2a8910b8edc7611eb1b8a80f9ed7576a5f811aea87ca569b6eb7be38babbd64a->leave($__internal_2a8910b8edc7611eb1b8a80f9ed7576a5f811aea87ca569b6eb7be38babbd64a_prof);

        
        $__internal_35e69bb352ecef5fd7be340483a505345f48e75e3e95dd17786a0811b766e783->leave($__internal_35e69bb352ecef5fd7be340483a505345f48e75e3e95dd17786a0811b766e783_prof);

    }

    public function getTemplateName()
    {
        return ":lucky:number.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  63 => 7,  50 => 4,  41 => 3,  11 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("{% extends 'base.html.twig' %}

{% block body %}
\t<h1>Your lucky number is {{ number}}</h1>
{% endblock %}

{% block stylesheets %}{% endblock %}
", ":lucky:number.html.twig", "/var/www/gradel_dev/tgsmith/test/app/Resources/views/lucky/number.html.twig");
    }
}
