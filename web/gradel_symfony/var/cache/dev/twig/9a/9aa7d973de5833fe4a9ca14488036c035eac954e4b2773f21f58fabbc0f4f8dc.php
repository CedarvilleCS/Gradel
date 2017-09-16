<?php

/* TwigBundle:Exception:exception.json.twig */
class __TwigTemplate_d3704eb0ebf4b97e39b97b5aabbc14addc6dfd675bc12c2079fbf543e8e9673d extends Twig_Template
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
        $__internal_728bd154fb8ec240d590e157345ff817b5b54c4296f977da3991da0a1768c3fe = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_728bd154fb8ec240d590e157345ff817b5b54c4296f977da3991da0a1768c3fe->enter($__internal_728bd154fb8ec240d590e157345ff817b5b54c4296f977da3991da0a1768c3fe_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:exception.json.twig"));

        $__internal_274dfd07d6c7c3708242a8de5c12402ba38672fa878f2c6e41035404809765db = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_274dfd07d6c7c3708242a8de5c12402ba38672fa878f2c6e41035404809765db->enter($__internal_274dfd07d6c7c3708242a8de5c12402ba38672fa878f2c6e41035404809765db_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:exception.json.twig"));

        // line 1
        echo twig_jsonencode_filter(array("error" => array("code" => (isset($context["status_code"]) ? $context["status_code"] : $this->getContext($context, "status_code")), "message" => (isset($context["status_text"]) ? $context["status_text"] : $this->getContext($context, "status_text")), "exception" => $this->getAttribute((isset($context["exception"]) ? $context["exception"] : $this->getContext($context, "exception")), "toarray", array()))));
        echo "
";
        
        $__internal_728bd154fb8ec240d590e157345ff817b5b54c4296f977da3991da0a1768c3fe->leave($__internal_728bd154fb8ec240d590e157345ff817b5b54c4296f977da3991da0a1768c3fe_prof);

        
        $__internal_274dfd07d6c7c3708242a8de5c12402ba38672fa878f2c6e41035404809765db->leave($__internal_274dfd07d6c7c3708242a8de5c12402ba38672fa878f2c6e41035404809765db_prof);

    }

    public function getTemplateName()
    {
        return "TwigBundle:Exception:exception.json.twig";
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
        return new Twig_Source("{{ { 'error': { 'code': status_code, 'message': status_text, 'exception': exception.toarray } }|json_encode|raw }}
", "TwigBundle:Exception:exception.json.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle/Resources/views/Exception/exception.json.twig");
    }
}
