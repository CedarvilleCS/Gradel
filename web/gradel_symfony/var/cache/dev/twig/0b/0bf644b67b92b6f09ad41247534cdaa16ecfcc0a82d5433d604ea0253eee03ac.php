<?php

/* TwigBundle:Exception:exception_full.html.twig */
class __TwigTemplate_c5a266016a1d0e31b6ee35f11dd88d3af10e1e671c98326d11002466de91cf50 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("@Twig/layout.html.twig", "TwigBundle:Exception:exception_full.html.twig", 1);
        $this->blocks = array(
            'head' => array($this, 'block_head'),
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
        $__internal_eacfb7e83ad57f11b1f711353a1e750b6051d4eba5aaf0ec0127765f7709df0f = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_eacfb7e83ad57f11b1f711353a1e750b6051d4eba5aaf0ec0127765f7709df0f->enter($__internal_eacfb7e83ad57f11b1f711353a1e750b6051d4eba5aaf0ec0127765f7709df0f_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:exception_full.html.twig"));

        $__internal_96ad56cff7ec834da6974a0f04a054a6ae21e7bb2e2a96e30227de6cc9808515 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_96ad56cff7ec834da6974a0f04a054a6ae21e7bb2e2a96e30227de6cc9808515->enter($__internal_96ad56cff7ec834da6974a0f04a054a6ae21e7bb2e2a96e30227de6cc9808515_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:exception_full.html.twig"));

        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_eacfb7e83ad57f11b1f711353a1e750b6051d4eba5aaf0ec0127765f7709df0f->leave($__internal_eacfb7e83ad57f11b1f711353a1e750b6051d4eba5aaf0ec0127765f7709df0f_prof);

        
        $__internal_96ad56cff7ec834da6974a0f04a054a6ae21e7bb2e2a96e30227de6cc9808515->leave($__internal_96ad56cff7ec834da6974a0f04a054a6ae21e7bb2e2a96e30227de6cc9808515_prof);

    }

    // line 3
    public function block_head($context, array $blocks = array())
    {
        $__internal_6f80b90d255a62a6d050f251cf9d7bff8d3f6b139a0dccf00ba6ce25733b069d = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_6f80b90d255a62a6d050f251cf9d7bff8d3f6b139a0dccf00ba6ce25733b069d->enter($__internal_6f80b90d255a62a6d050f251cf9d7bff8d3f6b139a0dccf00ba6ce25733b069d_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "head"));

        $__internal_815ebcd8278b915bbec0855b4a1211c37cc068428d26b23fb89f6e81bbec1374 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_815ebcd8278b915bbec0855b4a1211c37cc068428d26b23fb89f6e81bbec1374->enter($__internal_815ebcd8278b915bbec0855b4a1211c37cc068428d26b23fb89f6e81bbec1374_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "head"));

        // line 4
        echo "    <style>
        .sf-reset .traces {
            padding-bottom: 14px;
        }
        .sf-reset .traces li {
            font-size: 12px;
            color: #868686;
            padding: 5px 4px;
            list-style-type: decimal;
            margin-left: 20px;
        }
        .sf-reset #logs .traces li.error {
            font-style: normal;
            color: #AA3333;
            background: #f9ecec;
        }
        .sf-reset #logs .traces li.warning {
            font-style: normal;
            background: #ffcc00;
        }
        /* fix for Opera not liking empty <li> */
        .sf-reset .traces li:after {
            content: \"\\00A0\";
        }
        .sf-reset .trace {
            border: 1px solid #D3D3D3;
            padding: 10px;
            overflow: auto;
            margin: 10px 0 20px;
        }
        .sf-reset .block-exception {
            -moz-border-radius: 16px;
            -webkit-border-radius: 16px;
            border-radius: 16px;
            margin-bottom: 20px;
            background-color: #f6f6f6;
            border: 1px solid #dfdfdf;
            padding: 30px 28px;
            word-wrap: break-word;
            overflow: hidden;
        }
        .sf-reset .block-exception div {
            color: #313131;
            font-size: 10px;
        }
        .sf-reset .block-exception-detected .illustration-exception,
        .sf-reset .block-exception-detected .text-exception {
            float: left;
        }
        .sf-reset .block-exception-detected .illustration-exception {
            width: 152px;
        }
        .sf-reset .block-exception-detected .text-exception {
            width: 670px;
            padding: 30px 44px 24px 46px;
            position: relative;
        }
        .sf-reset .text-exception .open-quote,
        .sf-reset .text-exception .close-quote {
            font-family: Arial, Helvetica, sans-serif;
            position: absolute;
            color: #C9C9C9;
            font-size: 8em;
        }
        .sf-reset .open-quote {
            top: 0;
            left: 0;
        }
        .sf-reset .close-quote {
            bottom: -0.5em;
            right: 50px;
        }
        .sf-reset .block-exception p {
            font-family: Arial, Helvetica, sans-serif;
        }
        .sf-reset .block-exception p a,
        .sf-reset .block-exception p a:hover {
            color: #565656;
        }
        .sf-reset .logs h2 {
            float: left;
            width: 654px;
        }
        .sf-reset .error-count, .sf-reset .support {
            float: right;
            width: 170px;
            text-align: right;
        }
        .sf-reset .error-count span {
             display: inline-block;
             background-color: #aacd4e;
             -moz-border-radius: 6px;
             -webkit-border-radius: 6px;
             border-radius: 6px;
             padding: 4px;
             color: white;
             margin-right: 2px;
             font-size: 11px;
             font-weight: bold;
        }

        .sf-reset .support a {
            display: inline-block;
            -moz-border-radius: 6px;
            -webkit-border-radius: 6px;
            border-radius: 6px;
            padding: 4px;
            color: #000000;
            margin-right: 2px;
            font-size: 11px;
            font-weight: bold;
        }

        .sf-reset .toggle {
            vertical-align: middle;
        }
        .sf-reset .linked ul,
        .sf-reset .linked li {
            display: inline;
        }
        .sf-reset #output-content {
            color: #000;
            font-size: 12px;
        }
        .sf-reset #traces-text pre {
            white-space: pre;
            font-size: 12px;
            font-family: monospace;
        }
    </style>
";
        
        $__internal_815ebcd8278b915bbec0855b4a1211c37cc068428d26b23fb89f6e81bbec1374->leave($__internal_815ebcd8278b915bbec0855b4a1211c37cc068428d26b23fb89f6e81bbec1374_prof);

        
        $__internal_6f80b90d255a62a6d050f251cf9d7bff8d3f6b139a0dccf00ba6ce25733b069d->leave($__internal_6f80b90d255a62a6d050f251cf9d7bff8d3f6b139a0dccf00ba6ce25733b069d_prof);

    }

    // line 136
    public function block_title($context, array $blocks = array())
    {
        $__internal_814bd5d6ce5b369ddb3c4ec1f3011e4ff95b6fe2e1fa0e5ff3a53eb5438251ad = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_814bd5d6ce5b369ddb3c4ec1f3011e4ff95b6fe2e1fa0e5ff3a53eb5438251ad->enter($__internal_814bd5d6ce5b369ddb3c4ec1f3011e4ff95b6fe2e1fa0e5ff3a53eb5438251ad_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        $__internal_2bb253aa7e0a3c8deddb344aab315b1c900536475afbb322f6be6d9477b29e66 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_2bb253aa7e0a3c8deddb344aab315b1c900536475afbb322f6be6d9477b29e66->enter($__internal_2bb253aa7e0a3c8deddb344aab315b1c900536475afbb322f6be6d9477b29e66_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "title"));

        // line 137
        echo "    ";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["exception"]) ? $context["exception"] : $this->getContext($context, "exception")), "message", array()), "html", null, true);
        echo " (";
        echo twig_escape_filter($this->env, (isset($context["status_code"]) ? $context["status_code"] : $this->getContext($context, "status_code")), "html", null, true);
        echo " ";
        echo twig_escape_filter($this->env, (isset($context["status_text"]) ? $context["status_text"] : $this->getContext($context, "status_text")), "html", null, true);
        echo ")
";
        
        $__internal_2bb253aa7e0a3c8deddb344aab315b1c900536475afbb322f6be6d9477b29e66->leave($__internal_2bb253aa7e0a3c8deddb344aab315b1c900536475afbb322f6be6d9477b29e66_prof);

        
        $__internal_814bd5d6ce5b369ddb3c4ec1f3011e4ff95b6fe2e1fa0e5ff3a53eb5438251ad->leave($__internal_814bd5d6ce5b369ddb3c4ec1f3011e4ff95b6fe2e1fa0e5ff3a53eb5438251ad_prof);

    }

    // line 140
    public function block_body($context, array $blocks = array())
    {
        $__internal_c344e3cc0efa17a071fb4678f1fdca2a398c4243f4d6c7f97a2e1c7d510be8ee = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_c344e3cc0efa17a071fb4678f1fdca2a398c4243f4d6c7f97a2e1c7d510be8ee->enter($__internal_c344e3cc0efa17a071fb4678f1fdca2a398c4243f4d6c7f97a2e1c7d510be8ee_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        $__internal_047833566b0a3962785598a2df57ebc49d313c8291f9f570830ce39820089e06 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_047833566b0a3962785598a2df57ebc49d313c8291f9f570830ce39820089e06->enter($__internal_047833566b0a3962785598a2df57ebc49d313c8291f9f570830ce39820089e06_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "body"));

        // line 141
        echo "    ";
        $this->loadTemplate("@Twig/Exception/exception.html.twig", "TwigBundle:Exception:exception_full.html.twig", 141)->display($context);
        
        $__internal_047833566b0a3962785598a2df57ebc49d313c8291f9f570830ce39820089e06->leave($__internal_047833566b0a3962785598a2df57ebc49d313c8291f9f570830ce39820089e06_prof);

        
        $__internal_c344e3cc0efa17a071fb4678f1fdca2a398c4243f4d6c7f97a2e1c7d510be8ee->leave($__internal_c344e3cc0efa17a071fb4678f1fdca2a398c4243f4d6c7f97a2e1c7d510be8ee_prof);

    }

    public function getTemplateName()
    {
        return "TwigBundle:Exception:exception_full.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  226 => 141,  217 => 140,  200 => 137,  191 => 136,  51 => 4,  42 => 3,  11 => 1,);
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

{% block head %}
    <style>
        .sf-reset .traces {
            padding-bottom: 14px;
        }
        .sf-reset .traces li {
            font-size: 12px;
            color: #868686;
            padding: 5px 4px;
            list-style-type: decimal;
            margin-left: 20px;
        }
        .sf-reset #logs .traces li.error {
            font-style: normal;
            color: #AA3333;
            background: #f9ecec;
        }
        .sf-reset #logs .traces li.warning {
            font-style: normal;
            background: #ffcc00;
        }
        /* fix for Opera not liking empty <li> */
        .sf-reset .traces li:after {
            content: \"\\00A0\";
        }
        .sf-reset .trace {
            border: 1px solid #D3D3D3;
            padding: 10px;
            overflow: auto;
            margin: 10px 0 20px;
        }
        .sf-reset .block-exception {
            -moz-border-radius: 16px;
            -webkit-border-radius: 16px;
            border-radius: 16px;
            margin-bottom: 20px;
            background-color: #f6f6f6;
            border: 1px solid #dfdfdf;
            padding: 30px 28px;
            word-wrap: break-word;
            overflow: hidden;
        }
        .sf-reset .block-exception div {
            color: #313131;
            font-size: 10px;
        }
        .sf-reset .block-exception-detected .illustration-exception,
        .sf-reset .block-exception-detected .text-exception {
            float: left;
        }
        .sf-reset .block-exception-detected .illustration-exception {
            width: 152px;
        }
        .sf-reset .block-exception-detected .text-exception {
            width: 670px;
            padding: 30px 44px 24px 46px;
            position: relative;
        }
        .sf-reset .text-exception .open-quote,
        .sf-reset .text-exception .close-quote {
            font-family: Arial, Helvetica, sans-serif;
            position: absolute;
            color: #C9C9C9;
            font-size: 8em;
        }
        .sf-reset .open-quote {
            top: 0;
            left: 0;
        }
        .sf-reset .close-quote {
            bottom: -0.5em;
            right: 50px;
        }
        .sf-reset .block-exception p {
            font-family: Arial, Helvetica, sans-serif;
        }
        .sf-reset .block-exception p a,
        .sf-reset .block-exception p a:hover {
            color: #565656;
        }
        .sf-reset .logs h2 {
            float: left;
            width: 654px;
        }
        .sf-reset .error-count, .sf-reset .support {
            float: right;
            width: 170px;
            text-align: right;
        }
        .sf-reset .error-count span {
             display: inline-block;
             background-color: #aacd4e;
             -moz-border-radius: 6px;
             -webkit-border-radius: 6px;
             border-radius: 6px;
             padding: 4px;
             color: white;
             margin-right: 2px;
             font-size: 11px;
             font-weight: bold;
        }

        .sf-reset .support a {
            display: inline-block;
            -moz-border-radius: 6px;
            -webkit-border-radius: 6px;
            border-radius: 6px;
            padding: 4px;
            color: #000000;
            margin-right: 2px;
            font-size: 11px;
            font-weight: bold;
        }

        .sf-reset .toggle {
            vertical-align: middle;
        }
        .sf-reset .linked ul,
        .sf-reset .linked li {
            display: inline;
        }
        .sf-reset #output-content {
            color: #000;
            font-size: 12px;
        }
        .sf-reset #traces-text pre {
            white-space: pre;
            font-size: 12px;
            font-family: monospace;
        }
    </style>
{% endblock %}

{% block title %}
    {{ exception.message }} ({{ status_code }} {{ status_text }})
{% endblock %}

{% block body %}
    {% include '@Twig/Exception/exception.html.twig' %}
{% endblock %}
", "TwigBundle:Exception:exception_full.html.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle/Resources/views/Exception/exception_full.html.twig");
    }
}
