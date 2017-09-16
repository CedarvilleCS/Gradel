<?php

/* WebProfilerBundle:Collector:router.html.twig */
class __TwigTemplate_c8d21550850074782862265b813a9c2aea7c608253db98e24225c2ea859cc33f extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("@WebProfiler/Profiler/layout.html.twig", "WebProfilerBundle:Collector:router.html.twig", 1);
        $this->blocks = array(
            'toolbar' => array($this, 'block_toolbar'),
            'menu' => array($this, 'block_menu'),
            'panel' => array($this, 'block_panel'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "@WebProfiler/Profiler/layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_96ec0d9769d266022f36e6dffbe407f61eaa6ebbf9bc6ee724f4aac214a321eb = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_96ec0d9769d266022f36e6dffbe407f61eaa6ebbf9bc6ee724f4aac214a321eb->enter($__internal_96ec0d9769d266022f36e6dffbe407f61eaa6ebbf9bc6ee724f4aac214a321eb_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "WebProfilerBundle:Collector:router.html.twig"));

        $__internal_0a2185736f867b6d19af237af83e779a50428983428a57f78eb507f0fd1b4029 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_0a2185736f867b6d19af237af83e779a50428983428a57f78eb507f0fd1b4029->enter($__internal_0a2185736f867b6d19af237af83e779a50428983428a57f78eb507f0fd1b4029_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "WebProfilerBundle:Collector:router.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_96ec0d9769d266022f36e6dffbe407f61eaa6ebbf9bc6ee724f4aac214a321eb->leave($__internal_96ec0d9769d266022f36e6dffbe407f61eaa6ebbf9bc6ee724f4aac214a321eb_prof);

        
        $__internal_0a2185736f867b6d19af237af83e779a50428983428a57f78eb507f0fd1b4029->leave($__internal_0a2185736f867b6d19af237af83e779a50428983428a57f78eb507f0fd1b4029_prof);

    }

    // line 3
    public function block_toolbar($context, array $blocks = array())
    {
        $__internal_1f6a09b02f880ec0a737cc3065c90514c76107325055851c3e059032a12239da = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_1f6a09b02f880ec0a737cc3065c90514c76107325055851c3e059032a12239da->enter($__internal_1f6a09b02f880ec0a737cc3065c90514c76107325055851c3e059032a12239da_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "toolbar"));

        $__internal_d1029020e1e1bbaa3877bf92768151fc9aa85fd10b1d09979b94a06ec1228051 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_d1029020e1e1bbaa3877bf92768151fc9aa85fd10b1d09979b94a06ec1228051->enter($__internal_d1029020e1e1bbaa3877bf92768151fc9aa85fd10b1d09979b94a06ec1228051_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "toolbar"));

        
        $__internal_d1029020e1e1bbaa3877bf92768151fc9aa85fd10b1d09979b94a06ec1228051->leave($__internal_d1029020e1e1bbaa3877bf92768151fc9aa85fd10b1d09979b94a06ec1228051_prof);

        
        $__internal_1f6a09b02f880ec0a737cc3065c90514c76107325055851c3e059032a12239da->leave($__internal_1f6a09b02f880ec0a737cc3065c90514c76107325055851c3e059032a12239da_prof);

    }

    // line 5
    public function block_menu($context, array $blocks = array())
    {
        $__internal_50eafbeed5e74aedc2567a7b8e03e5a3a21489bef7d447fb39920d8ab0995ee3 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_50eafbeed5e74aedc2567a7b8e03e5a3a21489bef7d447fb39920d8ab0995ee3->enter($__internal_50eafbeed5e74aedc2567a7b8e03e5a3a21489bef7d447fb39920d8ab0995ee3_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "menu"));

        $__internal_3e3547f3654daf03325eac5d17bf989d56bea2667cca33e778fa1d8e0235be01 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_3e3547f3654daf03325eac5d17bf989d56bea2667cca33e778fa1d8e0235be01->enter($__internal_3e3547f3654daf03325eac5d17bf989d56bea2667cca33e778fa1d8e0235be01_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "menu"));

        // line 6
        echo "<span class=\"label\">
    <span class=\"icon\">";
        // line 7
        echo twig_include($this->env, $context, "@WebProfiler/Icon/router.svg");
        echo "</span>
    <strong>Routing</strong>
</span>
";
        
        $__internal_3e3547f3654daf03325eac5d17bf989d56bea2667cca33e778fa1d8e0235be01->leave($__internal_3e3547f3654daf03325eac5d17bf989d56bea2667cca33e778fa1d8e0235be01_prof);

        
        $__internal_50eafbeed5e74aedc2567a7b8e03e5a3a21489bef7d447fb39920d8ab0995ee3->leave($__internal_50eafbeed5e74aedc2567a7b8e03e5a3a21489bef7d447fb39920d8ab0995ee3_prof);

    }

    // line 12
    public function block_panel($context, array $blocks = array())
    {
        $__internal_b1ff630933542dba3a9a6fe9ab2267a6532ad8eed77f78cbee8d82efe6b656e5 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_b1ff630933542dba3a9a6fe9ab2267a6532ad8eed77f78cbee8d82efe6b656e5->enter($__internal_b1ff630933542dba3a9a6fe9ab2267a6532ad8eed77f78cbee8d82efe6b656e5_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "panel"));

        $__internal_46b36033e12978f8b91bfca4d872ecb48168d48d00e747855d4a3d8641686ea1 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_46b36033e12978f8b91bfca4d872ecb48168d48d00e747855d4a3d8641686ea1->enter($__internal_46b36033e12978f8b91bfca4d872ecb48168d48d00e747855d4a3d8641686ea1_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "panel"));

        // line 13
        echo "    ";
        echo $this->env->getRuntime('Symfony\Bridge\Twig\Extension\HttpKernelRuntime')->renderFragment($this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_profiler_router", array("token" => (isset($context["token"]) ? $context["token"] : $this->getContext($context, "token")))));
        echo "
";
        
        $__internal_46b36033e12978f8b91bfca4d872ecb48168d48d00e747855d4a3d8641686ea1->leave($__internal_46b36033e12978f8b91bfca4d872ecb48168d48d00e747855d4a3d8641686ea1_prof);

        
        $__internal_b1ff630933542dba3a9a6fe9ab2267a6532ad8eed77f78cbee8d82efe6b656e5->leave($__internal_b1ff630933542dba3a9a6fe9ab2267a6532ad8eed77f78cbee8d82efe6b656e5_prof);

    }

    public function getTemplateName()
    {
        return "WebProfilerBundle:Collector:router.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  94 => 13,  85 => 12,  71 => 7,  68 => 6,  59 => 5,  42 => 3,  11 => 1,);
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

{% block toolbar %}{% endblock %}

{% block menu %}
<span class=\"label\">
    <span class=\"icon\">{{ include('@WebProfiler/Icon/router.svg') }}</span>
    <strong>Routing</strong>
</span>
{% endblock %}

{% block panel %}
    {{ render(path('_profiler_router', { token: token })) }}
{% endblock %}
", "WebProfilerBundle:Collector:router.html.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/WebProfilerBundle/Resources/views/Collector/router.html.twig");
    }
}
