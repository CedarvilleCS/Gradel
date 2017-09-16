<?php

/* WebProfilerBundle:Collector:ajax.html.twig */
class __TwigTemplate_fad78add6dda3affbf0e1a16f37b6737cea13ea248cb78b0b689a9f48f7c7d97 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("@WebProfiler/Profiler/layout.html.twig", "WebProfilerBundle:Collector:ajax.html.twig", 1);
        $this->blocks = array(
            'toolbar' => array($this, 'block_toolbar'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "@WebProfiler/Profiler/layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_632c5292929a0a6a4ccf3299368df0aa2d4212662593c047af1cf83a99514c85 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_632c5292929a0a6a4ccf3299368df0aa2d4212662593c047af1cf83a99514c85->enter($__internal_632c5292929a0a6a4ccf3299368df0aa2d4212662593c047af1cf83a99514c85_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "WebProfilerBundle:Collector:ajax.html.twig"));

        $__internal_63f8c530dcf8bd82d65608b5cd7afbe4a85383bb4b139430c85d59c061f597a3 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_63f8c530dcf8bd82d65608b5cd7afbe4a85383bb4b139430c85d59c061f597a3->enter($__internal_63f8c530dcf8bd82d65608b5cd7afbe4a85383bb4b139430c85d59c061f597a3_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "WebProfilerBundle:Collector:ajax.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_632c5292929a0a6a4ccf3299368df0aa2d4212662593c047af1cf83a99514c85->leave($__internal_632c5292929a0a6a4ccf3299368df0aa2d4212662593c047af1cf83a99514c85_prof);

        
        $__internal_63f8c530dcf8bd82d65608b5cd7afbe4a85383bb4b139430c85d59c061f597a3->leave($__internal_63f8c530dcf8bd82d65608b5cd7afbe4a85383bb4b139430c85d59c061f597a3_prof);

    }

    // line 3
    public function block_toolbar($context, array $blocks = array())
    {
        $__internal_f3696d5dd7aa96ae5f07b777aff4c21370ee6b205f214915be824308f52fba24 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_f3696d5dd7aa96ae5f07b777aff4c21370ee6b205f214915be824308f52fba24->enter($__internal_f3696d5dd7aa96ae5f07b777aff4c21370ee6b205f214915be824308f52fba24_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "toolbar"));

        $__internal_75a567a223ed8d1465d7a7a31957b538edb6fbe6974010ddb04719fde433a075 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_75a567a223ed8d1465d7a7a31957b538edb6fbe6974010ddb04719fde433a075->enter($__internal_75a567a223ed8d1465d7a7a31957b538edb6fbe6974010ddb04719fde433a075_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "toolbar"));

        // line 4
        echo "    ";
        ob_start();
        // line 5
        echo "        ";
        echo twig_include($this->env, $context, "@WebProfiler/Icon/ajax.svg");
        echo "
        <span class=\"sf-toolbar-value sf-toolbar-ajax-request-counter\">0</span>
    ";
        $context["icon"] = ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
        // line 8
        echo "
    ";
        // line 9
        $context["text"] = ('' === $tmp = "        <div class=\"sf-toolbar-info-piece\">
            <b class=\"sf-toolbar-ajax-info\"></b>
        </div>
        <div class=\"sf-toolbar-info-piece\">
            <table class=\"sf-toolbar-ajax-requests\">
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>URL</th>
                        <th>Time</th>
                        <th>Profile</th>
                    </tr>
                </thead>
                <tbody class=\"sf-toolbar-ajax-request-list\"></tbody>
            </table>
        </div>
    ") ? '' : new Twig_Markup($tmp, $this->env->getCharset());
        // line 29
        echo "
    ";
        // line 30
        echo twig_include($this->env, $context, "@WebProfiler/Profiler/toolbar_item.html.twig", array("link" => false));
        echo "
";
        
        $__internal_75a567a223ed8d1465d7a7a31957b538edb6fbe6974010ddb04719fde433a075->leave($__internal_75a567a223ed8d1465d7a7a31957b538edb6fbe6974010ddb04719fde433a075_prof);

        
        $__internal_f3696d5dd7aa96ae5f07b777aff4c21370ee6b205f214915be824308f52fba24->leave($__internal_f3696d5dd7aa96ae5f07b777aff4c21370ee6b205f214915be824308f52fba24_prof);

    }

    public function getTemplateName()
    {
        return "WebProfilerBundle:Collector:ajax.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  85 => 30,  82 => 29,  62 => 9,  59 => 8,  52 => 5,  49 => 4,  40 => 3,  11 => 1,);
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

{% block toolbar %}
    {% set icon %}
        {{ include('@WebProfiler/Icon/ajax.svg') }}
        <span class=\"sf-toolbar-value sf-toolbar-ajax-request-counter\">0</span>
    {% endset %}

    {% set text %}
        <div class=\"sf-toolbar-info-piece\">
            <b class=\"sf-toolbar-ajax-info\"></b>
        </div>
        <div class=\"sf-toolbar-info-piece\">
            <table class=\"sf-toolbar-ajax-requests\">
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>URL</th>
                        <th>Time</th>
                        <th>Profile</th>
                    </tr>
                </thead>
                <tbody class=\"sf-toolbar-ajax-request-list\"></tbody>
            </table>
        </div>
    {% endset %}

    {{ include('@WebProfiler/Profiler/toolbar_item.html.twig', { link: false }) }}
{% endblock %}
", "WebProfilerBundle:Collector:ajax.html.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/WebProfilerBundle/Resources/views/Collector/ajax.html.twig");
    }
}
