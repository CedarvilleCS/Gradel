<?php

/* WebProfilerBundle:Profiler:ajax_layout.html.twig */
class __TwigTemplate_ac11290e59d19f4e4685744d9601a8b8c6ca0799da926f1efe665399bc939cd1 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'panel' => array($this, 'block_panel'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $__internal_d6b67c0382781979ebec4280c2e06dd0cc7a5eeb46301ca1af552e68a4fb9f56 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_d6b67c0382781979ebec4280c2e06dd0cc7a5eeb46301ca1af552e68a4fb9f56->enter($__internal_d6b67c0382781979ebec4280c2e06dd0cc7a5eeb46301ca1af552e68a4fb9f56_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "WebProfilerBundle:Profiler:ajax_layout.html.twig"));

        $__internal_e1c62fa3f7752aef81f2523dc193d5814e49c099aa728c13adbfbbc9036c05b1 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_e1c62fa3f7752aef81f2523dc193d5814e49c099aa728c13adbfbbc9036c05b1->enter($__internal_e1c62fa3f7752aef81f2523dc193d5814e49c099aa728c13adbfbbc9036c05b1_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "WebProfilerBundle:Profiler:ajax_layout.html.twig"));

        // line 1
        $this->displayBlock('panel', $context, $blocks);
        
        $__internal_d6b67c0382781979ebec4280c2e06dd0cc7a5eeb46301ca1af552e68a4fb9f56->leave($__internal_d6b67c0382781979ebec4280c2e06dd0cc7a5eeb46301ca1af552e68a4fb9f56_prof);

        
        $__internal_e1c62fa3f7752aef81f2523dc193d5814e49c099aa728c13adbfbbc9036c05b1->leave($__internal_e1c62fa3f7752aef81f2523dc193d5814e49c099aa728c13adbfbbc9036c05b1_prof);

    }

    public function block_panel($context, array $blocks = array())
    {
        $__internal_07b1864583025be28c8fa2646523a2584b674e07af4801168bc99927efa96fc2 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_07b1864583025be28c8fa2646523a2584b674e07af4801168bc99927efa96fc2->enter($__internal_07b1864583025be28c8fa2646523a2584b674e07af4801168bc99927efa96fc2_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "panel"));

        $__internal_700a3e68c48ab79dc3fd25b840ee6d724c17cb91a1ed7063848084609ed51bf9 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_700a3e68c48ab79dc3fd25b840ee6d724c17cb91a1ed7063848084609ed51bf9->enter($__internal_700a3e68c48ab79dc3fd25b840ee6d724c17cb91a1ed7063848084609ed51bf9_prof = new Twig_Profiler_Profile($this->getTemplateName(), "block", "panel"));

        echo "";
        
        $__internal_700a3e68c48ab79dc3fd25b840ee6d724c17cb91a1ed7063848084609ed51bf9->leave($__internal_700a3e68c48ab79dc3fd25b840ee6d724c17cb91a1ed7063848084609ed51bf9_prof);

        
        $__internal_07b1864583025be28c8fa2646523a2584b674e07af4801168bc99927efa96fc2->leave($__internal_07b1864583025be28c8fa2646523a2584b674e07af4801168bc99927efa96fc2_prof);

    }

    public function getTemplateName()
    {
        return "WebProfilerBundle:Profiler:ajax_layout.html.twig";
    }

    public function getDebugInfo()
    {
        return array (  26 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("{% block panel '' %}
", "WebProfilerBundle:Profiler:ajax_layout.html.twig", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/WebProfilerBundle/Resources/views/Profiler/ajax_layout.html.twig");
    }
}
