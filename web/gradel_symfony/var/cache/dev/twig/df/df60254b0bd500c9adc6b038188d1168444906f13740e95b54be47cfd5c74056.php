<?php

/* @Framework/Form/form_enctype.html.php */
class __TwigTemplate_6a33d2204497760203374f3e3ceb2f4bb7e7014be1f46a22cd4a364cc1bfd96d extends Twig_Template
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
        $__internal_ffd31747a684833d9f7f4ede0471ae6cecacb2336a68a13425f2aa1fd0030ddd = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_ffd31747a684833d9f7f4ede0471ae6cecacb2336a68a13425f2aa1fd0030ddd->enter($__internal_ffd31747a684833d9f7f4ede0471ae6cecacb2336a68a13425f2aa1fd0030ddd_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/form_enctype.html.php"));

        $__internal_672f94bae8def389c40e6c246ddacfc7b258827703915911f64fbcc994e5aff4 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_672f94bae8def389c40e6c246ddacfc7b258827703915911f64fbcc994e5aff4->enter($__internal_672f94bae8def389c40e6c246ddacfc7b258827703915911f64fbcc994e5aff4_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/form_enctype.html.php"));

        // line 1
        echo "<?php if (\$form->vars['multipart']): ?>enctype=\"multipart/form-data\"<?php endif ?>
";
        
        $__internal_ffd31747a684833d9f7f4ede0471ae6cecacb2336a68a13425f2aa1fd0030ddd->leave($__internal_ffd31747a684833d9f7f4ede0471ae6cecacb2336a68a13425f2aa1fd0030ddd_prof);

        
        $__internal_672f94bae8def389c40e6c246ddacfc7b258827703915911f64fbcc994e5aff4->leave($__internal_672f94bae8def389c40e6c246ddacfc7b258827703915911f64fbcc994e5aff4_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/form_enctype.html.php";
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
        return new Twig_Source("<?php if (\$form->vars['multipart']): ?>enctype=\"multipart/form-data\"<?php endif ?>
", "@Framework/Form/form_enctype.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/form_enctype.html.php");
    }
}
