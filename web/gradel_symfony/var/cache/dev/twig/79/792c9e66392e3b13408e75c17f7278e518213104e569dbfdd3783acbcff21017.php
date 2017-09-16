<?php

/* @Framework/Form/password_widget.html.php */
class __TwigTemplate_d94d4d48925093222286b4d01da406df1fe0e463f7f2d711bf9979914e58f507 extends Twig_Template
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
        $__internal_7c7f846ad333dd59817b6b951aac792a44470e75df892d50a5128c14eed8ccbc = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_7c7f846ad333dd59817b6b951aac792a44470e75df892d50a5128c14eed8ccbc->enter($__internal_7c7f846ad333dd59817b6b951aac792a44470e75df892d50a5128c14eed8ccbc_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/password_widget.html.php"));

        $__internal_3e36cb52224f08ab0c02e99604f76a8f9a7bf90c744f5689e8582d89aaf1e9a9 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_3e36cb52224f08ab0c02e99604f76a8f9a7bf90c744f5689e8582d89aaf1e9a9->enter($__internal_3e36cb52224f08ab0c02e99604f76a8f9a7bf90c744f5689e8582d89aaf1e9a9_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/password_widget.html.php"));

        // line 1
        echo "<?php echo \$view['form']->block(\$form, 'form_widget_simple', array('type' => isset(\$type) ? \$type : 'password')) ?>
";
        
        $__internal_7c7f846ad333dd59817b6b951aac792a44470e75df892d50a5128c14eed8ccbc->leave($__internal_7c7f846ad333dd59817b6b951aac792a44470e75df892d50a5128c14eed8ccbc_prof);

        
        $__internal_3e36cb52224f08ab0c02e99604f76a8f9a7bf90c744f5689e8582d89aaf1e9a9->leave($__internal_3e36cb52224f08ab0c02e99604f76a8f9a7bf90c744f5689e8582d89aaf1e9a9_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/password_widget.html.php";
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
        return new Twig_Source("<?php echo \$view['form']->block(\$form, 'form_widget_simple', array('type' => isset(\$type) ? \$type : 'password')) ?>
", "@Framework/Form/password_widget.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/password_widget.html.php");
    }
}
