<?php

/* @Framework/Form/form_widget_compound.html.php */
class __TwigTemplate_5bf1591ed6ec2cc122188630a87f284e478792dc2fbd04534fce90ae9a440bc9 extends Twig_Template
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
        $__internal_6414336b50bc87dc1b8e78c4baa7383a0671afd181d0795e4ebdc01ed1f21401 = $this->env->getExtension("Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension");
        $__internal_6414336b50bc87dc1b8e78c4baa7383a0671afd181d0795e4ebdc01ed1f21401->enter($__internal_6414336b50bc87dc1b8e78c4baa7383a0671afd181d0795e4ebdc01ed1f21401_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/form_widget_compound.html.php"));

        $__internal_62ee104d94669e96740d575c4b2bd9677969b456fcc928d2dc939192b0ee2ff0 = $this->env->getExtension("Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension");
        $__internal_62ee104d94669e96740d575c4b2bd9677969b456fcc928d2dc939192b0ee2ff0->enter($__internal_62ee104d94669e96740d575c4b2bd9677969b456fcc928d2dc939192b0ee2ff0_prof = new Twig_Profiler_Profile($this->getTemplateName(), "template", "@Framework/Form/form_widget_compound.html.php"));

        // line 1
        echo "<div <?php echo \$view['form']->block(\$form, 'widget_container_attributes') ?>>
    <?php if (!\$form->parent && \$errors): ?>
    <?php echo \$view['form']->errors(\$form) ?>
    <?php endif ?>
    <?php echo \$view['form']->block(\$form, 'form_rows') ?>
    <?php echo \$view['form']->rest(\$form) ?>
</div>
";
        
        $__internal_6414336b50bc87dc1b8e78c4baa7383a0671afd181d0795e4ebdc01ed1f21401->leave($__internal_6414336b50bc87dc1b8e78c4baa7383a0671afd181d0795e4ebdc01ed1f21401_prof);

        
        $__internal_62ee104d94669e96740d575c4b2bd9677969b456fcc928d2dc939192b0ee2ff0->leave($__internal_62ee104d94669e96740d575c4b2bd9677969b456fcc928d2dc939192b0ee2ff0_prof);

    }

    public function getTemplateName()
    {
        return "@Framework/Form/form_widget_compound.html.php";
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
        return new Twig_Source("<div <?php echo \$view['form']->block(\$form, 'widget_container_attributes') ?>>
    <?php if (!\$form->parent && \$errors): ?>
    <?php echo \$view['form']->errors(\$form) ?>
    <?php endif ?>
    <?php echo \$view['form']->block(\$form, 'form_rows') ?>
    <?php echo \$view['form']->rest(\$form) ?>
</div>
", "@Framework/Form/form_widget_compound.html.php", "/var/www/gradel_dev/tgsmith/test/vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/views/Form/form_widget_compound.html.php");
    }
}
