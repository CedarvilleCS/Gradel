<?php

/* @Framework/Form/form_widget.html.php */
class __TwigTemplate_51546d6e615c10d79403b4ad2b027f6c4aad6c53332e991c62f936b3055b6be1 extends Twig_Template
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
        $__internal_c95b8a6c7960c576ac80ae669953bb1a1ecfda4df3631dfe399427dcac4cac7b = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_c95b8a6c7960c576ac80ae669953bb1a1ecfda4df3631dfe399427dcac4cac7b->enter($__internal_c95b8a6c7960c576ac80ae669953bb1a1ecfda4df3631dfe399427dcac4cac7b_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/form_widget.html.php"));

        $__internal_f2531652bc75398cdd4b60b93471878d9ee040095b852bc6998a4f3e0c6b2377 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_f2531652bc75398cdd4b60b93471878d9ee040095b852bc6998a4f3e0c6b2377->enter($__internal_f2531652bc75398cdd4b60b93471878d9ee040095b852bc6998a4f3e0c6b2377_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/form_widget.html.php"));

        // line 1
        echo "<?php if (\$compound): ?>
<?php echo \$view['form']->block(\$form, 'form_widget_compound')?>
<?php else: ?>
<?php echo \$view['form']->block(\$form, 'form_widget_simple')?>
<?php endif ?>
";
        
        $__internal_c95b8a6c7960c576ac80ae669953bb1a1ecfda4df3631dfe399427dcac4cac7b->leave($__internal_c95b8a6c7960c576ac80ae669953bb1a1ecfda4df3631dfe399427dcac4cac7b_prof);

        
        $__internal_f2531652bc75398cdd4b60b93471878d9ee040095b852bc6998a4f3e0c6b2377->leave($__internal_f2531652bc75398cdd4b60b93471878d9ee040095b852bc6998a4f3e0c6b2377_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/form_widget.html.php";
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
        return new Twig_Source("<?php if (\$compound): ?>
<?php echo \$view['form']->block(\$form, 'form_widget_compound')?>
<?php else: ?>
<?php echo \$view['form']->block(\$form, 'form_widget_simple')?>
<?php endif ?>
", "@Framework/Form/form_widget.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/form_widget.html.php");
    }
}
