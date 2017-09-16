<?php

/* @Framework/Form/form_rows.html.php */
class __TwigTemplate_2739c8f4042ad26812381406662874d9be1fa82c762311eddc736483cdbddc64 extends Twig_Template
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
        $__internal_6ab30ea2cf8d8b0943fe99250c56d1bfb09c6c4acd1b46ff374f669c0d89c2d1 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_6ab30ea2cf8d8b0943fe99250c56d1bfb09c6c4acd1b46ff374f669c0d89c2d1->enter($__internal_6ab30ea2cf8d8b0943fe99250c56d1bfb09c6c4acd1b46ff374f669c0d89c2d1_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/form_rows.html.php"));

        $__internal_c974d6e7f2ed25148540403639664eea1ec66660e4635d8c39e64a34d73a9915 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_c974d6e7f2ed25148540403639664eea1ec66660e4635d8c39e64a34d73a9915->enter($__internal_c974d6e7f2ed25148540403639664eea1ec66660e4635d8c39e64a34d73a9915_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/form_rows.html.php"));

        // line 1
        echo "<?php foreach (\$form as \$child) : ?>
    <?php echo \$view['form']->row(\$child) ?>
<?php endforeach; ?>
";
        
        $__internal_6ab30ea2cf8d8b0943fe99250c56d1bfb09c6c4acd1b46ff374f669c0d89c2d1->leave($__internal_6ab30ea2cf8d8b0943fe99250c56d1bfb09c6c4acd1b46ff374f669c0d89c2d1_prof);

        
        $__internal_c974d6e7f2ed25148540403639664eea1ec66660e4635d8c39e64a34d73a9915->leave($__internal_c974d6e7f2ed25148540403639664eea1ec66660e4635d8c39e64a34d73a9915_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/form_rows.html.php";
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
        return new Twig_Source("<?php foreach (\$form as \$child) : ?>
    <?php echo \$view['form']->row(\$child) ?>
<?php endforeach; ?>
", "@Framework/Form/form_rows.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/form_rows.html.php");
    }
}
