<?php

/* @Framework/Form/container_attributes.html.php */
class __TwigTemplate_0acd4d31854cc5772f2e91cc62633c0851c73cd33a4efe121e8e1a491a9929cf extends Twig_Template
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
        $__internal_c9e5393ca7602c44f6ba8fe25170160505ff06e1db2bf12d72a66723d347aef5 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_c9e5393ca7602c44f6ba8fe25170160505ff06e1db2bf12d72a66723d347aef5->enter($__internal_c9e5393ca7602c44f6ba8fe25170160505ff06e1db2bf12d72a66723d347aef5_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/container_attributes.html.php"));

        $__internal_67ac9bb0f71b5c269ca496a8f0a03eb16bb6c8bdb686a56c0d05c723ece0decf = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_67ac9bb0f71b5c269ca496a8f0a03eb16bb6c8bdb686a56c0d05c723ece0decf->enter($__internal_67ac9bb0f71b5c269ca496a8f0a03eb16bb6c8bdb686a56c0d05c723ece0decf_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/container_attributes.html.php"));

        // line 1
        echo "<?php echo \$view['form']->block(\$form, 'widget_container_attributes') ?>
";
        
        $__internal_c9e5393ca7602c44f6ba8fe25170160505ff06e1db2bf12d72a66723d347aef5->leave($__internal_c9e5393ca7602c44f6ba8fe25170160505ff06e1db2bf12d72a66723d347aef5_prof);

        
        $__internal_67ac9bb0f71b5c269ca496a8f0a03eb16bb6c8bdb686a56c0d05c723ece0decf->leave($__internal_67ac9bb0f71b5c269ca496a8f0a03eb16bb6c8bdb686a56c0d05c723ece0decf_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/container_attributes.html.php";
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
        return new Twig_Source("<?php echo \$view['form']->block(\$form, 'widget_container_attributes') ?>
", "@Framework/Form/container_attributes.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/container_attributes.html.php");
    }
}
