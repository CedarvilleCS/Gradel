<?php

/* @Framework/Form/choice_widget.html.php */
class __TwigTemplate_e981d61c7be43ce78c91e916c3ba6aea0fa384ebc6a602fc718cb2c37ebe93ef extends Twig_Template
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
        $__internal_466abf0c0fd41d4a320b6bf40c4da50ca8fc1584aeb4b1f6d7ec26035230a0a0 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_466abf0c0fd41d4a320b6bf40c4da50ca8fc1584aeb4b1f6d7ec26035230a0a0->enter($__internal_466abf0c0fd41d4a320b6bf40c4da50ca8fc1584aeb4b1f6d7ec26035230a0a0_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/choice_widget.html.php"));

        $__internal_5d18fa73b6aa02d0c3c2afff734894121c53265f6469a6ae6dd0a8bac4fff1bd = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_5d18fa73b6aa02d0c3c2afff734894121c53265f6469a6ae6dd0a8bac4fff1bd->enter($__internal_5d18fa73b6aa02d0c3c2afff734894121c53265f6469a6ae6dd0a8bac4fff1bd_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/choice_widget.html.php"));

        // line 1
        echo "<?php if (\$expanded): ?>
<?php echo \$view['form']->block(\$form, 'choice_widget_expanded') ?>
<?php else: ?>
<?php echo \$view['form']->block(\$form, 'choice_widget_collapsed') ?>
<?php endif ?>
";
        
        $__internal_466abf0c0fd41d4a320b6bf40c4da50ca8fc1584aeb4b1f6d7ec26035230a0a0->leave($__internal_466abf0c0fd41d4a320b6bf40c4da50ca8fc1584aeb4b1f6d7ec26035230a0a0_prof);

        
        $__internal_5d18fa73b6aa02d0c3c2afff734894121c53265f6469a6ae6dd0a8bac4fff1bd->leave($__internal_5d18fa73b6aa02d0c3c2afff734894121c53265f6469a6ae6dd0a8bac4fff1bd_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/choice_widget.html.php";
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
        return new Twig_Source("<?php if (\$expanded): ?>
<?php echo \$view['form']->block(\$form, 'choice_widget_expanded') ?>
<?php else: ?>
<?php echo \$view['form']->block(\$form, 'choice_widget_collapsed') ?>
<?php endif ?>
", "@Framework/Form/choice_widget.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/choice_widget.html.php");
    }
}
