<?php

/* TwigBundle:Exception:error.json.twig */
class __TwigTemplate_e8a9cfe67bd02507aee8c80f230cd924ceff8cf16d128572660888dac6b41d73 extends Twig_Template
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
        $__internal_9a74f32edd3db37e9a373353aba5cb387147d8e67f2e166dc1c4de7a77305c38 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_9a74f32edd3db37e9a373353aba5cb387147d8e67f2e166dc1c4de7a77305c38->enter($__internal_9a74f32edd3db37e9a373353aba5cb387147d8e67f2e166dc1c4de7a77305c38_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:error.json.twig"));

        $__internal_7b2224e47c684c707014c31f839d95bf744da9b0aa339500098c235697952c40 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_7b2224e47c684c707014c31f839d95bf744da9b0aa339500098c235697952c40->enter($__internal_7b2224e47c684c707014c31f839d95bf744da9b0aa339500098c235697952c40_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "TwigBundle:Exception:error.json.twig"));

        // line 1
        echo twig_jsonencode_filter(array("error" => array("code" => (isset($context["status_code"]) ? $context["status_code"] : $this->getContext($context, "status_code")), "message" => (isset($context["status_text"]) ? $context["status_text"] : $this->getContext($context, "status_text")))));
        echo "
";
        
        $__internal_9a74f32edd3db37e9a373353aba5cb387147d8e67f2e166dc1c4de7a77305c38->leave($__internal_9a74f32edd3db37e9a373353aba5cb387147d8e67f2e166dc1c4de7a77305c38_prof);

        
        $__internal_7b2224e47c684c707014c31f839d95bf744da9b0aa339500098c235697952c40->leave($__internal_7b2224e47c684c707014c31f839d95bf744da9b0aa339500098c235697952c40_prof);

    }

    public function getTemplateName()
    {
        return "TwigBundle:Exception:error.json.twig";
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
        return new Twig_Source("{{ { 'error': { 'code': status_code, 'message': status_text } }|json_encode|raw }}
", "TwigBundle:Exception:error.json.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/TwigBundle/Resources/views/Exception/error.json.twig");
    }
}
