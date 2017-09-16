<?php

/* @Framework/Form/widget_attributes.html.php */
class __TwigTemplate_a857982497e136c62c915e05c5d572e857529933f775bb7140deb71c1c6cd3df extends Twig_Template
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
        $__internal_bd18e7df6692f5f06d9c0bdbe12a62d793cd5eb84d25d5eb7f1559b209f53071 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_bd18e7df6692f5f06d9c0bdbe12a62d793cd5eb84d25d5eb7f1559b209f53071->enter($__internal_bd18e7df6692f5f06d9c0bdbe12a62d793cd5eb84d25d5eb7f1559b209f53071_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/widget_attributes.html.php"));

        $__internal_cbe53aaf2878f17a419522fa32979786060cccc5a25e5e52ac7ff61bfd0704a6 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_cbe53aaf2878f17a419522fa32979786060cccc5a25e5e52ac7ff61bfd0704a6->enter($__internal_cbe53aaf2878f17a419522fa32979786060cccc5a25e5e52ac7ff61bfd0704a6_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/widget_attributes.html.php"));

        // line 1
        echo "id=\"<?php echo \$view->escape(\$id) ?>\" name=\"<?php echo \$view->escape(\$full_name) ?>\"<?php if (\$disabled): ?> disabled=\"disabled\"<?php endif ?>
<?php if (\$required): ?> required=\"required\"<?php endif ?>
<?php echo \$attr ? ' '.\$view['form']->block(\$form, 'attributes') : '' ?>
";
        
        $__internal_bd18e7df6692f5f06d9c0bdbe12a62d793cd5eb84d25d5eb7f1559b209f53071->leave($__internal_bd18e7df6692f5f06d9c0bdbe12a62d793cd5eb84d25d5eb7f1559b209f53071_prof);

        
        $__internal_cbe53aaf2878f17a419522fa32979786060cccc5a25e5e52ac7ff61bfd0704a6->leave($__internal_cbe53aaf2878f17a419522fa32979786060cccc5a25e5e52ac7ff61bfd0704a6_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/widget_attributes.html.php";
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
        return new Twig_Source("id=\"<?php echo \$view->escape(\$id) ?>\" name=\"<?php echo \$view->escape(\$full_name) ?>\"<?php if (\$disabled): ?> disabled=\"disabled\"<?php endif ?>
<?php if (\$required): ?> required=\"required\"<?php endif ?>
<?php echo \$attr ? ' '.\$view['form']->block(\$form, 'attributes') : '' ?>
", "@Framework/Form/widget_attributes.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/widget_attributes.html.php");
    }
}
